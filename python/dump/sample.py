import urllib2, sys, firebirdsql
from BeautifulSoup import *
import simplejson as json

# FORMAT THE INPUT AND PARSE WITH BEAUTIFULSOUP
if len(sys.argv) < 2:
	#default is uat site with incidents table
	url = "https://osuituat.service-now.com/incident.do?wsdl"
	table_name = "change_task"
	instance = "osuituat.service-now.com"
else:
	# args: 1) instance of service now url 2) table name
	url = "https://"+sys.argv[1]+"/"+sys.argv[2]+".do?wsdl"
	table_name = sys.argv[2]
	instance = sys.argv[1]

# DUMP DATA INTO TABLE
# Open url with username password
password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
jsonurl = "https://" + instance + "/" + table_name + ".do?JSON"
#
password_mgr.add_password(None, jsonurl, "admin", "explodev1234") # Replace user/password with admin
handler = urllib2.HTTPBasicAuthHandler(password_mgr)
try:
	opener = urllib2.build_opener(handler)
	opener.open(jsonurl)
	urllib2.install_opener(opener)
	getRecs = json.load( urllib2.urlopen(jsonurl) )
except IOError:
	print 'ERROR: Problem opening json data'
# Put page data into a string, jsonData
#jsonData = ""
#for line in getRecs:
#	jsonData += line
# Use simplejson to decode
#items = json.loads(jsonData)
print len(getRecs['records'])

counter = 0 
for record in getRecs['records']:
    counter = counter +1
    print counter, record['number'] 
