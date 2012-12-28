import urllib2, sys, MySQLdb
from BeautifulSoup import *
from SOAPpy import SOAPProxy

reload(sys)
sys.setdefaultencoding('utf-8')

# FORMAT THE INPUT AND PARSE WITH BEAUTIFULSOUP
# args: 1) instance of service now url 2) table name
if len(sys.argv) < 2:
	print "ERROR: Program needs instance of service now to run. Example: 'python copytable.py instance table'"
url = "https://"+sys.argv[1]+".service-now.com/"+sys.argv[2]+".do?displayvalue=true&wsdl"
table_name = sys.argv[2]
instance = sys.argv[1]
try:
	wsdl = urllib2.urlopen(url)
except IOError:
	print 'ERROR: Problem reading url: '+url
input = ""
# Replace "xsd:element" for all the element tags that contain column names and types for the database with "elementTag"
for line in wsdl:
	input += line.replace("xsd:element max", "elementTag max")
soup = BeautifulStoneSoup(input)

# FIND GET_RECORDS AND GENERATE SQL STATEMENTS
key_list = [] # List of all the keys being put in the table
sql_command = " ("
#Parse through soup to find the elements under getRecords
for elem in soup.find(attrs={"name" : "getRecords"}).contents[0].contents[0].contents:
#Add the type based on the type of the element
	if elem['name'][0:2] != "__": # Make sure it's actually a column name and not an extended parameter
		sql_command += "`" + elem['name'] + "` "
		key_list.append(elem['name'])
		if elem['name'] == "sys_id":
			sql_command +="VARCHAR(255) NOT NULL UNIQUE"
		elif elem['type'] == "xsd:boolean":
			sql_command += "INT"
		elif elem['name'] == "description" or elem['name'] == "text" or elem['name'] == "close_notes":
			sql_command += "LONGTEXT"
		elif elem['type'] == "xsd:string":
			sql_command += "TEXT"
		else:
			sql_command += "INT"
		sql_command += ", "
commandLength = len(sql_command) -  2
sql_command = sql_command[0:commandLength] + ")"

# OPEN DATABASE AND CREATE TABLE
con = MySQLdb.connect(host='localhost', user='root', passwd='niK6efiS', db = 'sndump', charset = 'utf8')
cur = con.cursor()
cur.execute("CREATE TABLE "+table_name+sql_command)

# DUMP DATA INTO TABLE
# Open url with SOAP call - dynamic elements of SOAP endpoint construction 
username, password, gliderecord = 'admin', 'explodev1234', table_name
proxy = "https://%s:%s@%s.service-now.com/%s.do?displayvalue=all&SOAP" % (username, password, instance, gliderecord, )
namespace = 'http://www.glidesoft.com/'
server = SOAPProxy(proxy,namespace)
# Cycle through every 250 until you get nothing
start = 0
end = 249
response = server.getRecords(__first_row=start, __last_row=end, __order_by='number')
continue_loop = 1
while continue_loop==1:
	if len(response)==0:
		continue_loop = 0
	for r in response: # Each r is a dict containing keys/values which are column/data for the table
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
		cur.execute(sql_insert)
	print "Processed %s records" % (end)
	start += 250
	end += 250
	response = server.getRecords(__first_row=start, __last_row=end, __order_by='number')
cur.close()
con.close()
