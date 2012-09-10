from elixir import *
from sqlalchemy import Table, Column, ForeignKey, ForeignKeyConstraint

from common.config import Config
import simplejson

class TVShow(Entity):
	using_options(shortnames=True,autoload=True,tablename=Config.settings['DATABASE']['show_table'],autosetup=True,order_by='sh_name')

	
	def _get_metadata(self):
		return simplejson.loads(self.sh_metadata)

	def _set_metadata(self,value):
		self.sh_metadata = simplejson.dumps(value)

	metadata = property(_get_metadata,_set_metadata)


	

	episodes = OneToMany('TVEpisode',primaryjoin=lambda: TVEpisode.sh_id == TVShow.sh_id, foreign_keys=lambda: [TVEpisode.sh_id])

class TVEpisode(Entity):
	using_options(shortnames=True,autoload=True,tablename=Config.settings['DATABASE']['episode_table'],autosetup=True,order_by='-ep_date')

	show = ManyToOne('TVShow',primaryjoin=lambda: TVEpisode.sh_id == TVShow.sh_id, foreign_keys=lambda: [TVEpisode.sh_id])
