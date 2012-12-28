import urllib2, sys, MySQLdb
from BeautifulSoup import *
from SOAPpy import SOAPProxy
from datetime import datetime, timedelta

reload(sys)
sys.setdefaultencoding('utf-8')

# FORMAT THE INPUT AND PARSE WITH BEAUTIFULSOUP
# args: 1) instance of service now url 2) table name
if len(sys.argv) < 2:
	print "ERROR: Program needs instance of service now to run. Example: 'python copytable.py instance_name table_name'"

url = "https://"+sys.argv[1]+".service-now.com/"+sys.argv[2]+".do?displayvalue=true&wsdl"
table_name = sys.argv[2]
instance = sys.argv[1]

try:
	wsdl = urllib2.urlopen(url)
except IOError:
	print 'ERROR: Problem reading url: '+url

input = ""

# Replace "xsd:element" for all the element tags that contain column names 
#and types for the database with "elementTag"
for line in wsdl:
	input += line.replace("xsd:element max", "elementTag max")
soup = BeautifulStoneSoup(input)

# FIND GET_RECORDS AND GENERATE SQL STATEMENTS
key_list = [] # List of all the keys being put in the table
#Parse through soup to find the elements under getRecords
for elem in soup.find(attrs={"name" : "getRecords"}).contents[0].contents[0].contents:
	if elem['name'][0:2] != "__": # Make sure it's actually a column name and not an extended parameter
		key_list.append(elem['name'])

# DUMP DATA INTO TABLE
# Open url with SOAP call - dynamic elements of SOAP endpoint construction 
username, password, gliderecord = 'admin', 'explodev1234', table_name
proxy = "https://%s:%s@%s.service-now.com/%s.do?displayvalue=all&SOAP" % (
	username, 
	password, 
	instance, 
	gliderecord, 
)
namespace = 'http://www.glidesoft.com/'
server = SOAPProxy(proxy,namespace)

# Cycle through every 250 until you get nothing
con = MySQLdb.connect(
	host='localhost', 
	user='servicenow', 
	passwd='servicenow', 
	db = 'sndump', 
	charset = 'utf8'
)

cur = con.cursor()
start = 0
end = 249
response = server.getRecords(
	__first_row=start, 
	__last_row=end, 
	__order_by_desc='sys_updated_on'
)
date_n_ago = "%s" % (datetime.now() - timedelta(days=15))
date_n_ago = date_n_ago[0:10]
continue_loop = 1
while continue_loop==1:
	for r in response: # Each r is a dict containing keys/values which are column/data for the table
		print r.sys_updated_on[0:10]>= date_n_ago 
		if continue_loop==1 and r.sys_updated_on[0:10]>=date_n_ago:
			insertinto = "INSERT INTO " + table_name + " ("
			values = "VALUES ("
			for field, value in vars(r).items():
				if key_list.count(field) > 0:
					insertinto += "`%s`, " % (field)
					temp = con.escape_string(value)
					values += "\'%s', " % (temp)
			insertlen = len(insertinto) - 2
			valuelen = len(values) - 2
			insertinto = insertinto[0:insertlen] + ")"
			values = values[0:valuelen] + ")"
			sql_insert = insertinto + " " + values
			sql_insert += " ON DUPLICATE KEY UPDATE "
			for item in key_list:
				sql_insert += "`" +item + "` = VALUES(`" + item + "`), "
			insertlen = len(sql_insert) - 2
			sql_insert = sql_insert[0:insertlen]
			cur.execute(sql_insert)
		else:
			continue_loop = 0
	print "Processed %s records" % (end)
	start += 250
	end += 250
	response = server.getRecords(__first_row=start, __last_row=end, __order_by_desc='sys_updated_on')
cur.close()
con.close()
print "Update finished."
