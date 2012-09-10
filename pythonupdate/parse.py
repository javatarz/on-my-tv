#!/usr/bin/env python
"""
        $URL$
        $Rev$
        $Author$
        $Date$
        $Id$
"""
from __future__ import with_statement 

__version__ = 0.01
__author__ = 'B. Agricola <maz@lawlr.us>'

import sys, os, logging, ConfigParser, Queue, threading, urllib, datetime, pytz, re, traceback,cStringIO,simplejson,time
import xml.etree.cElementTree as ET

from common.config import Config

import MySQLdb, MySQLdb.cursors
MySQLdb.paramstyle='pyformat'

#import hotshot, hotshot.stats

_config_file_path = './calendar.conf'

Config.run = True

import memcache

def main(argv): 
    #Switch into current script dir
    if os.path.exists(sys.path[0]):
        os.chdir(sys.path[0])

    if not os.path.exists(_config_file_path) or not os.access(_config_file_path, os.R_OK):
            
            print 'MAIN',"FATAL: Config file %s not found or unreadable" % _config_file_path
            sys.exit(1)

    #Setup logging to console / log file
    format_string = '%(asctime)s %(name)s %(levelname)s %(message)s'
    log_format = logging.Formatter(format_string)
    logging.basicConfig(level=Config.debug_level, format=format_string, filename='./log/calendar_parse.log',filemode='a')

    console_log = logging.StreamHandler()
    console_log.setLevel(Config.debug_level)
    console_log.setFormatter(log_format)

    logging.getLogger('').addHandler(console_log)

    #Load config file
    try:
        logging.getLogger('MAIN').info('Loading config from %s' % _config_file_path)

        _cfile = open(_config_file_path,'r')
        _config_file = ConfigParser.SafeConfigParser()
        _config_file.readfp(_cfile)

        for section, contents in _config_file.__dict__['_sections'].items():
            Config.settings[section] = contents
        _cfile.close()

        logging.getLogger('MAIN').info('Config loaded from %s' % _config_file_path)

    except (ConfigParser.NoSectionError,ConfigParser.ParsingError,ConfigParser.NoOptionError), errmsg:

        logging.getLogger('MAIN').info('Config file parse error: %s' %errmsg)
        sys.exit(1)	

    
    #Test single show list
    #_show_list = TVShow.selectBy(shIscancelled=0,shName='The Amazing Race')

    #Get a list of all uncancelled TV Shows
    try:
        _mthread_database = MySQLdb.connect(host=Config.settings['DATABASE']['host'],user=Config.settings['DATABASE']['username'],passwd=Config.settings['DATABASE']['password'],db=Config.settings['DATABASE']['database'],cursorclass=MySQLdb.cursors.DictCursor,connect_timeout=5)
        _mthread_cursor = _mthread_database.cursor()

    except MySQLdb.MySQLError, message:
        logging.getLogger('MAIN').error('Error connecting to database %s - %s' %(Config.settings['DATABASE']['database'],message))
        sys.exit(1)

    #_mthread_cursor.execute("""SELECT * FROM `%s` WHERE sh_iscancelled = 0 AND sh_name = 'Kings';"""% (Config.settings['DATABASE']['show_table']))
    #_mthread_cursor.execute("""SELECT * FROM `%s` WHERE sh_iscancelled = 0;"""% (Config.settings['DATABASE']['show_table']))
    _mthread_cursor.execute("""SELECT * FROM `%s`"""% (Config.settings['DATABASE']['show_table']))
    _show_list = _mthread_cursor.fetchall()
    
    queue = Queue.Queue()


    #Start a specific number of worker threads
    for i in xrange(int(Config.settings['PERFORMANCE']['threads'])):
         t = t = ParseShowData(queue,i)
         t.setDaemon(True)
         t.start()

    for _show in _show_list:
        queue.put(_show)
    
    #queue.put(_show_list[0])

    

    try:
        queue.join() 

        try:
            _mc = memcache.Client(['%(server)s:%(port)s' % Config.settings['MEMCACHE']],debug=True)
            _mc.flush_all()
        except:
            logging.getLogger('MAIN').error('Could not flush memcache.')

        with open('%s' % (Config.settings['LAST_UPDATE']['file']),'w') as _luf:
            _luf.write(datetime.datetime.utcnow().strftime('%a, %d %b %Y %H:%M:%S'))
        
    except KeyboardInterrupt:
        logging.getLogger('MAIN').info('Caught Interrupt, exiting...')


class ParseShowData(threading.Thread):

    def __init__(self, queue,tid):
        threading.Thread.__init__(self)
        self.queue = queue
        self.id = tid
        self.finished_counter = 0
        self.tvrage_url_epid_search = 'episodes/'
        self.tvrage_summary_start = '<p>'
        self.tvrage_summary_end = '</p>'
     

        #Open a database connection for each thread because MySQLdb is not properly threadsafe
        try:
            self.database = MySQLdb.connect(host=Config.settings['DATABASE']['host'],user=Config.settings['DATABASE']['username'],passwd=Config.settings['DATABASE']['password'],db=Config.settings['DATABASE']['database'],cursorclass=MySQLdb.cursors.DictCursor,connect_timeout=5,init_command='SET NAMES utf8;')
            self.database.set_character_set('utf8')

            self.database.cursor().execute('SET CHARACTER SET utf8;')
            self.database.cursor().execute('SET character_set_connection=utf8;')

        except MySQLdb.MySQLError, message:
            logging.getLogger('PARSE_SHOWDATA:THREAD_%s').error('Error connecting to database %s - %s' %(Config.settings['DATABASE']['database'],message))


    def run(self):
        
        while Config.run:
            #Get queue item
            _show = self.queue.get()

            #Perform work
            
            try:
                if _show['sh_stringid'] == '':
                    _show['sh_stringid'] = str(_show['sh_tvrage_rid'])
                    
                self.parse_tvrage_cache(_show)
                self.finished_counter+=1
                self.database.commit()
                
                logging.getLogger('PARSE_SHOWDATA:THREAD_%s' % self.id).info('[%s] Updated: %s' % (self.finished_counter,_show['sh_name']))
            except Exception, msg:
                self.finished_counter+=1
                logging.getLogger('PARSE_SHOWDATA:THREAD_%s' % self.id).error('[%s] Update Error: %s (%s)' % (self.finished_counter,_show['sh_name'],msg))

            #Replace queue item
            self.queue.task_done()
            
    def parse_tvrage_cache(self,_show):
        
        #Parse cache file as XML
        try:
    
            with open('%s/%s.tvr' % (Config.settings['CACHE']['directory'],_show['sh_stringid'].lower().replace(' ', '_')), 'r') as _fi:
                _strd = cStringIO.StringIO(re.sub(r'(?i)&(?!amp;|quot;|nbsp;|gt;|lt;|laquo;|raquo;|copy;|reg;|bul;|rsquo;)', '&amp;', _fi.read()))
                _if = ET.parse(_strd)
            
                _root = _if.getroot()

                """ PARSE SHOW DATA START """
                _show['sh_status'] = _root.findtext('status')
                _show['sh_country'] = _root.findtext('origin_country')
                _show['sh_network'] = _root.findtext('network')
                _show['sh_airtime'] = _root.findtext('airtime')
                _show['sh_timezone'] = _root.findtext('timezone')
		_rlength = _root.findtext('runtime')
		if _rlength:
                	_show['sh_length'] = int(_rlength) * 60
                
                _metadata = {}
                _metadata['seasons'] = _root.findtext('seasons')
                _metadata['started'] = _root.findtext('started')
                _metadata['ended'] = _root.findtext('ended')
                _metadata['classification'] = _root.findtext('classification')

                if _root.find('genres') is not None:
                    _metadata['genres'] = [gen.text for gen in _root.find('genres')]

                if _root.find('akas') is not None:
                    _metadata['akas'] = [(aka.text,aka.items()) for aka in _root.find('akas')]
                _metadata['tvrage_link'] = _root.findtext('showlink')
                _metadata['airday'] = _root.findtext('airday')

                _show['sh_metadata'] = simplejson.dumps(_metadata)

                if _root.findtext('status') == 'Canceled/Ended':
                    _show['sh_iscancelled'] = 1
            
               
                _input = self.database.cursor()
                self.run_query_handle_deadlock(_input,"""
                UPDATE `""" + Config.settings['DATABASE']['show_table'] + """` 
                SET
                    sh_status = %(sh_status)s, 
                    sh_country = %(sh_country)s, 
                    sh_network = %(sh_network)s,
                    sh_airtime = %(sh_airtime)s,
                    sh_timezone = %(sh_timezone)s,
                    sh_length = %(sh_length)s,
                    sh_metadata = %(sh_metadata)s,
                    sh_iscancelled = %(sh_iscancelled)s
                WHERE
                    sh_id = %(sh_id)s
                LIMIT 1
                """,enc(_show))
                _input.close()

                """ PARSE SHOW DATA END """

                """ PARSE EPISODE DATA START """
                _seasons = _root.find('Episodelist')
                

                _episode_entries = []

                _summary_data = self.get_episode_summary_data(_show)
                
                for _season in _seasons.findall('Season'):
                    _season_number = int(_season.get('no'))
                    for _ep in _season:
                        _src = self.database.cursor()
                        _src.execute("""SELECT * FROM `%s` WHERE sh_id = %s AND ep_season = %s AND ep_number = %s LIMIT 1;"""% (Config.settings['DATABASE']['episode_table'],_show['sh_id'],int(_season_number),int(_ep.findtext('seasonnum'))))
                        _epr = _src.fetchone()
                        _src.close()
                        
                        _input_timezones = {}
                        _input_timezones['United Kingdom'] = 'Europe/London'
                        _input_timezones['UK'] = 'Europe/London'
                        _input_timezones['America'] = 'US/Eastern';
                        _input_timezones['USA'] = 'US/Eastern';
                        _input_timezones['US'] = 'US/Eastern';
                        _input_timezones['Canada'] = 'US/Eastern';
                        _input_timezones['CA'] = 'US/Eastern'

                        
                        if _show['sh_country'] in _input_timezones:
                            _import_timezone = pytz.timezone(_input_timezones[_show['sh_country']])
                            has_dst = False
                        else:
                            if _show['sh_timezone'].find(' ') != -1:
                                _rtz,_dst = _show['sh_timezone'].split(' ')
                                has_dst = True
                            else:
                                _rtz = _show['sh_timezone']
                                has_dst = False

                            if _rtz[3:4] == '+':
                                _rtz = _rtz.replace('+','-',1)
                            else:
                                _rtz = _rtz.replace('-','+',1)

                            _import_timezone = pytz.timezone('Etc/%s' %_rtz)
                    
                        try:
        
                            _timezone_fixed_date = _import_timezone.localize(datetime.datetime.strptime('%s %s' % (_ep.findtext('airdate'),_show['sh_airtime']),'%Y-%m-%d %H:%M'),is_dst=has_dst)
                            _utc = pytz.utc

                            _timezone_fixed_date = _timezone_fixed_date.astimezone(_utc)

                        except ValueError, e:
                            _timezone_fixed_date = None
                            logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error: Encountered bad date %s for show %s' % ('%s %s' % (_ep.findtext('airdate'),_show['sh_airtime']),_show['sh_name']))
                        
                        try:
                            _url = _ep.findtext('link')
                            _erf = _url.rfind(self.tvrage_url_epid_search)
                            
                            if _erf != -1:
                                _epid = _url[_erf+len(self.tvrage_url_epid_search):]
                            else:
                                _epid = -1
                                
                            
                            _vars = {
                                'sh_id': _show['sh_id'],
                                'ep_season': _season_number,
                                'ep_number': int(_ep.findtext('seasonnum')),
                                'ep_title': _ep.findtext('title'),
                                'ep_tvrage_url': _url,
                                'ep_tvrage_id': _epid,
                                'ep_summary': self.find_episode_summary(_show,_epid,_summary_data),
                                'ep_prod_number': _ep.findtext('prodnum'),
                                'ep_screen_cap': _ep.findtext('screencap'),
                                'sh_length': _show['sh_length'],
                                'ep_date': _timezone_fixed_date
                            }

                            if _vars['ep_prod_number'] is None or _vars['ep_prod_number'] is '':
                                _vars['ep_prod_number'] = _vars['ep_number']

                            if _vars['ep_screen_cap'] is None:
                                _vars['ep_screen_cap'] = ''
                            
                            if _vars['ep_tvrage_url'] is None:
                                _vars['ep_tvrage_url'] = ''

                            
                            _episode_entries.append(enc(_vars))
                            
                        except TypeError, e:
                            tb = traceback.format_exc()
                            logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error: Invalid Type for %s - %s' % (_show.shName,_vars))
                            logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error(tb)
                           
                """ RUN SQL """
                
                _input = self.database.cursor()
                _dep = self.database.cursor()

		logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).info('Show %s has %s entries' % (_show['sh_name'],len(_episode_entries)))
		if _show['sh_name'] == 'Continuum':
                	logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).info('%s entries: %s (%s)' % (_show['sh_name'],_episode_entries,_show))

                for i in _episode_entries:
                
                    if i['ep_date']is not None:
                        _dep.execute("SELECT ep_date FROM `%s` WHERE sh_id = %s AND ep_season = %s AND DATE(ep_date) = DATE('%s') AND ep_number < %s ORDER BY ep_number DESC LIMIT 1;" 
                        % (Config.settings['DATABASE']['episode_table'],i['sh_id'],i['ep_season'],i['ep_date'].strftime('%Y-%m-%d %H:%M:%S'),i['ep_number']))
                        _eplast = _dep.fetchone()
                                
                        if _eplast is not None:
                            _delta = datetime.timedelta(seconds=i['sh_length'])
                            i['ep_date'] = _eplast['ep_date'] + _delta
                            logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).info('Adjusting %s %sx%s by %s' % (_show['sh_name'],i['ep_season'],i['ep_number'],_delta))

                    self.run_query_handle_deadlock(_input,"INSERT INTO `" + Config.settings['DATABASE']['episode_table'] + "` (sh_id,ep_season,ep_number,ep_title,ep_tvrage_url,ep_prod_number,ep_screen_cap,ep_date,ep_summary,ep_tvrage_id) VALUES (%(sh_id)s,%(ep_season)s,%(ep_number)s,%(ep_title)s,%(ep_tvrage_url)s,%(ep_prod_number)s,%(ep_screen_cap)s,%(ep_date)s,%(ep_summary)s,%(ep_tvrage_id)s) ON DUPLICATE KEY UPDATE ep_title = VALUES(ep_title), ep_tvrage_url = VALUES(ep_tvrage_url), ep_prod_number = VALUES(ep_prod_number), ep_screen_cap = VALUES(ep_screen_cap), ep_date = VALUES(ep_date), ep_summary = VALUES(ep_summary), ep_tvrage_id = VALUES(ep_tvrage_id);",i)
                        
                _dep.close()
                _input.close()
            
        
            """ PARSE EPISODE DATA END """

        except Exception, msg:
            tb = traceback.format_exc()
            logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error for show %s (%d): %s (%s)' % (_show['sh_stringid'],_show['sh_id'],msg,tb))

    def find_episode_summary(self,_show,_epid,_data):
        _ep_tentative_start = _data.find(self.tvrage_url_epid_search + _epid)
        
        if _ep_tentative_start == -1:
            return ''
        
        _ep_end = _data.find(self.tvrage_summary_end,_ep_tentative_start)
        
        if _ep_end == -1:
            return ''
        
        _ep_start = _data.rfind(self.tvrage_summary_start,_ep_tentative_start,_ep_end)
        
        if _ep_start == -1:
            return ''
        
        return _data[_ep_start+len(self.tvrage_summary_start):_ep_end]
        
    
    def get_episode_summary_data(self,_show):
        try:
            with open('%s/%s.tvrd' % (Config.settings['CACHE']['directory'],_show['sh_stringid'].lower().replace(' ', '_'))) as _if:
                _data = _if.read()
                return _data
            return ''
        except Exception, msg:
            tb = traceback.format_exc()
            logging.getLogger('PARSE_EPGUIDES_CACHE:THREAD_%s' % self.id).error('Parse Error: %s (%s)' % (msg,tb))
    
    
    def run_query_handle_deadlock(self,cursor,query,data,i=1):
        try:
            #print query
            #return 
            cursor.execute(query,data)
        except MySQLdb.OperationalError, message:
            #return
            #Handle deadlocks
            if message[0]  == 1213:
                if i <= 10:
                    logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Deadlock %s encountered for query %s' % (i,query))
                    time.sleep(0.1)
                    i += 1
                    self.run_query_handle_deadlock(cursor,query,data,i)
                else:
                    logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Deadlock could not be avoided for query %s' % (query))
                
            

def enc(dict):
    """
    for key,cur in dict.items():
        if isinstance(cur, basestring):
            try:
                dict[key] = unicode(cur).encode('utf-8')
            except Exception, msg:
                try:
                    dict[key] = cur.decode('iso-8859-1').encode('utf-8')
                except Exception,msg:
                    logging.getLogger('ENC').error('Could not encode [%s] %s to utf-8 (%s)' % (key,cur,msg))
    """    
    return dict

if __name__ == "__main__":
    try:
        import psyco
        psyco.full()
    except ImportError:
        pass
        
    #prof = hotshot.Profile("parse.prof")
    #prof.runcall(main,sys.argv[1:])

    main(sys.argv[1:])
