import urllib2, sys, firebirdsql
from BeautifulSoup import *
from SOAPpy import SOAPProxy

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
sql_command = "CREATE TABLE "+table_name+" ("
#Parse through soup to find the elements under getRecords
for elem in soup.find(attrs={"name" : "getRecords"}).contents[0].contents[0].contents:
#Add the type based on the type of the element
	if elem['name'][0:2] != "__" : # Make sure it's actually a column name and not an extended parameter
		sql_command += "\"" + elem['name']+"\" "
		key_list.append(elem['name'])
		if elem['type'] == "xsd:boolean":
			sql_command += "varchar(5)"
		elif elem['type'] == "xsd:string":
			sql_command += "blob sub_type text"
		else:
			sql_command += "int"
		sql_command += ","
commandLength = len(sql_command) -  1
sql_command = sql_command[0:commandLength]
sql_command += ")"

# OPEN DATABASE AND CREATE TABLE
con = firebirdsql.connect(host='localhost', database='/var/lib/firebird/2.1/data/sndump.fdb', user='SYSDBA', password='z1x2c3d')
cur = con.cursor()
cur.execute(sql_command)
con.commit()

# DUMP DATA INTO TABLE
# Open url with SOAP call - dynamic elements of SOAP endpoint construction 
username, password, gliderecord = 'admin', 'explodev1234', table_name
proxy = "https://%s:%s@%s.service-now.com/%s.do?displayvalue=all&SOAP" % (username, password, instance, gliderecord, )
namespace = 'http://www.glidesoft.com/'
server = SOAPProxy(proxy,namespace)
start = 749
end = 999
response = server.getRecords(__first_row=start, __last_row=end, __order_by='number')
continue_loop = 1
while continue_loop==1: # Cycle through every 250 until you get nothing
	if len(response)==0:
		continue_loop = 0
	for r in response: # Each r is a dict containing keys/values which are column/data for the table
		insertinto = "INSERT INTO " + table_name + " ("
		values = "VALUES ("
		for field, value in vars(r).items():
			if key_list.count(field) > 0:
				insertinto += "\"%s\", " % (field)
				temp = "%s" % (value)
				temp = temp.replace("'", "''")
				#temp = temp.replace("\n", " ")
				values += "\'" + temp + "', "
		insertlen = len(insertinto) - 2
		valuelen = len(values) - 2
		insertinto = insertinto[0:insertlen] + ")"
		values = values[0:valuelen] + ")"
		sql_insert = insertinto + " " + values
		con = firebirdsql.connect(host='localhost', database='/var/lib/firebird/2.1/data/sndump.fdb', user='SYSDBA', password='z1x2c3d')
		cur = con.cursor()
		if sys.argv[2]=="kb_knowledge":
			if r.number == "KB02955":
				print sql_insert[650:750]
		cur.execute(sql_insert)
		con.commit()
	print "Processed %s records" % (end)
	start += 250
	end += 250
	response = server.getRecords(__first_row=start, __last_row=end, __order_by='number')
