# On-My.tv 

## Why 
Hi.

So for a long time I've not maintained the calendar or kept up with new shows adequately, and to be honest I find no interest or enjoyment from this project anymore. The machine that the calendar was hosted on needs to be repurposed, so I'm stepping back. That said, I spent a lot of time running this over the years so I'm open sourcing the code. Use it for inspiration, take all of it, none of it, use it for toilet paper, or submit patches if you want. If people are serious about cleaning it up and improving it then I'm happy to accept patches to this repo.

The code is mostly shit, designed specifically to run on a tiny server at high speed and thus sacrifices readability for speed in lots of situations. The core of it though, the calendar and db libraries are relatively clean and work OK.

Useful files (db data, lighttpd config and magnet scripts for compressing the JS / css)  are in supplementary/ (this is not a web facing directory) 

This needs mysql + pdo, php, memcached and such to run. There's no other documentation aside from the few and far between code comments. 

The likelihood of anyone finding this code useful is low, but if anyone wants to pick up the flame and run with it the domain is still registered and if anyone shows the commitment to run it I'll be happy to point the domain wherever. 


## Notes
* There's no special way to add a show. To add one requires editing the shows table in the database to add a new row. This will, with triggers, automatically add a row to the shows_search table which is used for fulltext searching for subdomains and such.
* The index file is handle_subdomain_forwarding.php which deals with sending users to the correct place based on the subdomain selected.
* None of the user account information has been exported with this, including show selections and watches and the like. I have backups of the db files but whether these still work or not is an interesting question. I will not be providing this user information to anyone since I have no guarantee people won't use it for spam or whatever the hell they feel like.
* Feel free to edit this readme and the wiki with information pertaining to how to set up the code.


## Contact
Please continue to use contact@on-my.tv to get in contact. Of course the likelihood of getting a reply is not guaranteed, but I'll try to respond to any questions.
