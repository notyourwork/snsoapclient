This script needs urllib2, MySQLdb, BeautifulSoup, and SOAPpy to run.

The following scripts are available for usage: 

- copytable.py - copy a single table (copys all records) 
    The copytable.py file copies a single table fresh into the database. 
    Be sure to delete or rename a table if it has the same name. 
    Otherwise, the script will throw an error.

- updatetable.py - update a single table (run after initial copy) 
    updates a table by adding/updating fields edited or added to service 
    now in the past 8 days.

- copymult.py - same as copy but for multiple tables 
- updatemult.py - same as update but for multiple tables 
    The files ending in 'mult.py' do the same as the single files but 
    for a prewritten list of tables. These are easily editable.



For the single table scripts, the first argument is the instance of service now such as 'osuitsm' or 'osuitsmtest'. The second argument should be the name of the table. For multiple table scripts, just include the service now instance.
