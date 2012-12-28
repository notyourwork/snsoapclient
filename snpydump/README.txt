This script needs urllib2, MySQLdb, BeautifulSoup, and SOAPpy to run.

The only scripts you will want to use is one of the following:

copytable.py
updatetable.py
copymult.py
updatemult.py

The rest of the files are used for development or logging purposes.

The copytable.py file copies a single table fresh into the database. Be sure to delete or rename a table if it has the same name. Otherwise, the script will throw an error.

udpatetable.py updates a table by adding/updating fields edited or added to service now in the past 8 days.

The files ending in 'mult.py' do the same as the single files but for a prewritten list of tables. These are easily editable.

For the single table scripts, the first argument is the instance of service now such as 'osuitsm' or 'osuitsmtest'. The second argument should be the name of the table. For multiple table scripts, just include the service now instance.
