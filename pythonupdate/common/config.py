#Borg class - works like singletons in other languages but rather than instancing an identity it instances a state
import logging

class  Config (object):
	instance = None
	settings = dict()
	debug_level = logging.INFO

	def __get__(self, instance, owner):
		if instance is None:
			return None
		else:
			return instance

	def __set__(self, instance, value):
		instance = value
		

	def __new__(cls, *args, **kargs): 
		if cls.instance is None:
			cls.instance = object.__new__(cls, *args, **kargs)
		return cls.instance		