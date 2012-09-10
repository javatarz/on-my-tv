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

import sys, os, logging, ConfigParser, Queue, threading, urllib, pycurl
from common.config import Config

import MySQLdb, MySQLdb.cursors
MySQLdb.paramstyle='pyformat'

_config_file_path = './calendar.conf'

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
    logging.basicConfig(level=Config.debug_level, format=format_string, filename='./log/calendar_download.log',filemode='a')

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

        logging.getLogger('MAIN').error('Config file parse error: %s' %errmsg)
        sys.exit(1)	

    if not os.path.isdir(Config.settings['CACHE']['directory']):
        logging.getLogger('MAIN').error('Cache directory setting %s is not a directory' % Config.settings['CACHE']['directory'])
        sys.exit(1)	



    #Get a list of all uncancelled TV Shows
    
    try:
        _mthread_database = MySQLdb.connect(host=Config.settings['DATABASE']['host'],user=Config.settings['DATABASE']['username'],passwd=Config.settings['DATABASE']['password'],db=Config.settings['DATABASE']['database'],cursorclass=MySQLdb.cursors.DictCursor,connect_timeout=5)
        _mthread_cursor = _mthread_database.cursor()

    except MySQLdb.MySQLError, message:
        logging.getLogger('MAIN').error('Error connecting to database %s - %s' %(Config.settings['DATABASE']['database'],message))
        sys.exit(1)

    #_mthread_cursor.execute("""SELECT * FROM `%s` WHERE sh_iscancelled = 0;"""% (Config.settings['DATABASE']['show_table']))
    _mthread_cursor.execute("""SELECT * FROM `%s`"""% (Config.settings['DATABASE']['show_table']))
    _show_list = _mthread_cursor.fetchall()

    queue = Queue.Queue()

    #Start a specific number of worker threads
    for i in xrange(int(Config.settings['PERFORMANCE']['threads'])):
         t = t = DownloadURLSToFileThread(queue,i)
         t.setDaemon(True)
         t.start()

    for _show in _show_list:
        if _show['sh_stringid'] == '':
            _show['sh_stringid'] = str(_show['sh_tvrage_rid'])
            
        queue.put(('http://services.tvrage.com/feeds/full_show_info.php?sid=%s' % _show['sh_tvrage_rid'],'%s/%s.tvr' % (Config.settings['CACHE']['directory'],_show['sh_stringid'].lower().replace(' ', '_'))))
        #http://www.tvrage.com/shows/id-18685/printable?nogs=1&nocrew=1
        queue.put(('http://www.tvrage.com/shows/id-%s/printable?nogs=1&nocrew=1' % _show['sh_tvrage_rid'],'%s/%s.tvrd' % (Config.settings['CACHE']['directory'],_show['sh_stringid'].lower().replace(' ', '_'))))

    try:
        queue.join() 
    except KeyboardInterrupt:
        logging.getLogger('MAIN').info('Caught Interrupt, exiting...')


class DownloadURLSToFileThread(threading.Thread):

    def __init__(self, queue,tid):
        threading.Thread.__init__(self)
        self.queue = queue
        self.id = tid
        self.curl = pycurl.Curl()
	self.curl.setopt(pycurl.USERAGENT,'Mozilla 5.0 on-my.tv Web Service Request Crawler')
    def run(self):
        while True:
            #Get queue item
            _url,_file = self.queue.get()
            with open(_file, "wb") as self.curl.fp:
                self.curl.setopt(pycurl.URL, _url)
                self.curl.setopt(pycurl.FOLLOWLOCATION, 1)
                self.curl.setopt(pycurl.MAXREDIRS, 5)
                self.curl.setopt(pycurl.NOPROGRESS, 1)
                self.curl.setopt(pycurl.CONNECTTIMEOUT, 10)
                self.curl.setopt(pycurl.AUTOREFERER,1)
                self.curl.setopt(pycurl.TIMEOUT, 20)
                self.curl.setopt(pycurl.NOSIGNAL, 1)
                self.curl.setopt(pycurl.HEADER, 0)
                self.curl.setopt(pycurl.NOBODY, 0)
                self.curl.setopt(pycurl.WRITEDATA,self.curl.fp)
                #Perform work
                try:
                    
                    self.curl.perform()
                    _header_code = self.curl.getinfo(pycurl.RESPONSE_CODE)
                    if _header_code != 200:
                        logging.getLogger('DOWNLOAD_URL:THREAD_%s' % self.id).info('Encountered non-200 status code: %s at %s' % (_header_code,_url))
                    else:
                        logging.getLogger('DOWNLOAD_URL:THREAD_%s' % self.id).info('Downloaded: %s > %s' % (_url,_file))

                except pycurl.error, msg:
                    logging.getLogger('DOWNLOAD_URL:THREAD_%s' % self.id).error('Download Error: %s' % msg)

                #Replace queue item
            self.queue.task_done()

""" USES URLLIB2 TO GRAB PAGES POSSIBLY COMPRESSED TO THE HDD USING COMPRESSION
def download_url_custom(_url,_dest):
    try:
        _conn = urllib2.Request(_url)
        _conn.add_header('Accept-encoding', 'gzip')
        _conn.ad_header('Connection','Keep-alive')
        _opn = urllib2.build_opener()

        _if = _opn.open(_conn)
        
        with open(_dest, 'w') as _of:
            _of.write(_if.read())

        _if.close()

    except Exception, msg:
        logging.getLogger('DOWNLOAD_URL').error('Download Error: %s' % msg)
        return None
"""

if __name__ == "__main__":
    try:
        import psyco
        psyco.full()
    except ImportError:
        pass

    main(sys.argv[1:])
