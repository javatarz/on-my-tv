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

import sys, os, logging, ConfigParser, Queue, threading, urllib, datetime, pytz, re, traceback,cStringIO,simplejson
import xml.etree.cElementTree as ET

from elixir import *
from sqlalchemy import and_,or_

from common.config import Config
#from dataobjects import TVShow

_config_file_path = './calendar.conf'

import hotshot

Config.run = True



def main(argv): 

	
	#Test single show list
	#_show_list = TVShow.selectBy(shIscancelled=0,shName='The Amazing Race')

	#Get a list of all uncancelled TV Shows
	
	try:
		
		metadata.bind = '%(scheme)s://%(username)s:%(password)s@%(host)s/%(database)s' % Config.settings['DATABASE']
		#metadata.bind.echo = True
		
		
		_show_list = TVShow.query.filter(TVShow.sh_iscancelled==0).all()
		
	except Exception, message:
		logging.getLogger('MAIN').error('Error connecting to database %s - %s' %(Config.settings['DATABASE']['database'],message))
		sys.exit(1)

	
	queue = Queue.Queue()

	#Start a specific number of worker threads
	for i in xrange(int(Config.settings['PERFORMANCE']['threads'])):
	     t = t = ParseShowData(queue,i)
	     t.setDaemon(True)
	     t.start()

	for _show in _show_list[0:10]:
		session.expunge(_show)
		queue.put(_show)
	

	try:
		
		queue.join() 
		
	except KeyboardInterrupt:
		logging.getLogger('MAIN').info('Caught Interrupt, exiting...')


class ParseShowData(threading.Thread):

	def __init__(self, queue,tid):
		threading.Thread.__init__(self)
		self.queue = queue
		self.id = tid
		self.finished_counter = 0

	def run(self):
		
		while Config.run:
			#Get queue item
			_show = self.queue.get()

			#Perform work
			
			try:
				self.parse_tvrage_cache(_show)
				self.parse_epguides_cache(_show)
				self.finished_counter+=1
				session.commit()	
				session.flush()
				logging.getLogger('PARSE_SHOWDATA:THREAD_%s' % self.id).info('[%s] Updated: %s' % (self.finished_counter,_show.sh_name))
			except Exception, msg:
				self.finished_counter+=1
				logging.getLogger('PARSE_SHOWDATA:THREAD_%s' % self.id).error('[%s] Update Error: %s (%s)' % (self.finished_counter,_show.sh_name,msg))

			#Replace queue item
			self.queue.task_done()
			


	def parse_epguides_cache(self,_show):
		try:
			with open('%s/%s.epg' % (Config.settings['CACHE']['directory'],_show.sh_stringid.lower().replace(' ', '_'))) as _if:
							
				_mstr = re.compile('^ *((?P<ep_id>[0-9]+)\.)? +(?P<season>[0-9P]+)- *(?P<episode>[0-9]+) +(?P<prod_no>[-0-9a-z/#\.]+)? +(?P<day>[0-9]+) (?P<month>[a-z]+) (?P<year>[0-9]+) +<a .*href="(?P<url>[^"]+)">(?P<title>[^<]+)</a>$',re.I)

				eplist = []
				
				for _line in _if.readlines():
					m = _mstr.match(_line)
					if m is None:
						continue

					match = m.groupdict()
				
					eplist.append(match)
					
				
			with open('%s/%s.tvc' % (Config.settings['CACHE']['directory'],_show.sh_stringid.lower().replace(' ', '_'))) as _if:
				_data = _if.read()
				for _epr in eplist:
					if _epr['url'] is None:
						continue

					_pos_fst = _epr['url'].find('/episode/')
					_urls = _epr['url'][_pos_fst:]
					_pos_url = _data.find(_urls)
		
					if _pos_url == -1:
						_epr['summary'] = ''
						continue

					_pos_left = _data.find('<p>',_pos_url)+3

					_pos_right = _data.find('</p>',_pos_left)

					if _pos_left != -1 and _pos_right != -1:

						_epr['summary'] = re.sub('(<br( )?(/)?>)+','<br />','<br />'.join(_data[_pos_left:_pos_right].strip().splitlines()))
						
					else:
						_epr['summary'] = ''

			for _ep in eplist:
			
				try:
					_epx = TVEpisode.query.filter_by(show=_show,ep_season=_ep['season'],ep_number=_ep['episode']).one()
					#[setattr(_epx,key,value) for key,value in _ep.items()]
					if _epx.ep_prod_number is None:
						_epx.ep_prod_number = _ep['prod_no']
					
					_epx.ep_summary_url = _ep['url']
					_epx.ep_summary = _ep['summary']
				except Exception,e:
					logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Could not find existing episode (%s)' % (e))
				
		except Exception, msg:
			tb = traceback.format_exc()
			logging.getLogger('PARSE_EPGUIDES_CACHE:THREAD_%s' % self.id).error('Parse Error: %s (%s)' % (msg,tb))


	def parse_tvrage_cache(self,_show):
		
		#Parse cache file as XML
		try:
		
			with open('%s/%s.tvr' % (Config.settings['CACHE']['directory'],_show.sh_stringid.lower().replace(' ', '_')), 'r') as _fi:
				_strd = cStringIO.StringIO(re.sub(r'(?i)&(?!amp;|quot;|nbsp;|gt;|lt;|laquo;|raquo;|copy;|reg;|bul;|rsquo;)', '&amp;', _fi.read()))
				_if = ET.parse(_strd)
			
				_root = _if.getroot()

				""" PARSE SHOW DATA START """
				_show.sh_status = _root.findtext('status')
				_show.sh_country = _root.findtext('origin_country')
				_show.sh_network = _root.findtext('network')
				_show.sh_airtime = _root.findtext('airtime')
				_show.sh_timezone = _root.findtext('timezone')
				_show.sh_length = int(_root.findtext('runtime')) * 60
				
				_metadata = {}
				_metadata['seasons'] = _root.findtext('seasons')
				_metadata['started'] = _root.findtext('started')
				_metadata['startdate'] = _root.findtext('startdate')
				_metadata['ended'] = _root.findtext('ended')
				_metadata['classification'] = _root.findtext('classification')

				if _root.find('genres') is not None:
					_metadata['genres'] = [(gen.text,gen.items()) for gen in _root.find('genres')]

				if _root.find('akas') is not None:
					_metadata['akas'] = [(aka.text,aka.items()) for aka in _root.find('akas')]
				_metadata['tvrage_link'] = _root.findtext('showlink')
				_metadata['airday'] = _root.findtext('airday')

				_show.metadata = _metadata

				if _root.findtext('status') == 'Canceled/Ended':
					_show.sh_iscancelled = 1

				session.add(_show)
				""" PARSE SHOW DATA END """

				""" PARSE EPISODE DATA START """
				_seasons = _root.find('Episodelist')
				

				_episode_entries = []

				for _season in _seasons.findall('Season'):
					_season_number = int(_season.get('no'))
					for _ep in _season:
						
						try:
							_epr = TVEpisode.query.filter_by(show=_show,ep_season=_season_number,ep_number=int(_ep.findtext('seasonnum'))).one()
						except Exception,e:
							#logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Could not find existing episode (%s)' % (e))
							_epr = TVEpisode()

						if _show.sh_timezone.find(' ') != -1:
							_rtz,_dst = _show.sh_timezone.split(' ')
							has_dst = True
						else:
							_rtz = _show.sh_timezone
							has_dst = False

						if _rtz[3:4] == '+':
							_rtz = _rtz.replace('+','-',1)
						else:
							_rtz = _rtz.replace('-','+',1)

						_import_timezone = pytz.timezone('Etc/%s' %_rtz)
					
						try:
							try:
								_timezone_fixed_date = _import_timezone.localize(datetime.datetime.strptime('%s %s' % (_ep.findtext('airdate'),_show.sh_airtime),'%Y-%m-%d %H:%M'),is_dst=has_dst)
								_utc = pytz.utc
							except:
								continue

							_timezone_fixed_date = _timezone_fixed_date.astimezone(_utc)

							#Convert to timezone-naive so it can be compared by elixir with the timestamp from the DB
							_timezone_fixed_date = _timezone_fixed_date.replace(tzinfo = None)

							if _timezone_fixed_date is not None:
							
								_epr.show		= _show
								_epr.ep_season		= _season_number
								_epr.ep_number		= int(_ep.findtext('seasonnum'))
								_epr.ep_title		= _ep.findtext('title')
								_epr.ep_tvrage_url	= _ep.findtext('link')
								_epr.ep_prod_number	= _ep.findtext('prodnum')
								_epr.ep_screen_cap	= _ep.findtext('screencap')
								_epr.ep_date		= _timezone_fixed_date
								
							else:
								logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error: Could not produce valid date for %s' % ('%s %s' % (_ep.findtext('airdate'),_show.sh_airtime)))

						except ValueError, e:
							logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error: Encountered bad date %s for show %s' % ('%s %s' % (_ep.findtext('airdate'),_show.sh_airtime),_show.sh_name))
				
			""" PARSE EPISODE DATA END """

		except Exception, msg:
			tb = traceback.format_exc()
			logging.getLogger('PARSE_TVRAGE_CACHE:THREAD_%s' % self.id).error('Parse Error for show %s: %s (%s)' % (_show.sh_stringid,msg,tb))


def enc(dict):
	for key,cur in dict.items():
		if isinstance(cur, basestring):
			try:
				dict[key] = unicode(cur).encode('utf-8')
			except Exception, msg:
				try:
					dict[key] = cur.decode('iso-8859-1').encode('utf-8')
				except Exception,msg:
					logging.getLogger('ENC').error('Could not encode [%s] %s to utf-8 (%s)' % (key,cur,msg))
		
	return dict

if __name__ == "__main__":
	try:
		pass
        	import psyco
        	psyco.full()
    	except ImportError:
        	pass

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
	
	from common.dataobjects import TVShow,TVEpisode

	prof = hotshot.Profile("parse_elixir.prof")

	prof.runcall(main,sys.argv[1:])

	prof.close()

