import urllib2, sys, firebirdsql
from BeautifulSoup import *
import simplejson as json

# FORMAT THE INPUT AND PARSE WITH BEAUTIFULSOUP
# args: 1) instance of service now url 2) table name
table_name = sys.argv[2]
instance = sys.argv[1]
url = "https://"+instance+"/"+table_name+".do?wsdl"
try:
	wsdl = urllib2.urlopen(url)
except IOError:
	print 'ERROR: Problem reading url: '+url
input = ""
# Replace "xsd:element" for all the element tags that contain column names and
# types for the database with "elementTag"
for line in wsdl:
	input += line.replace("xsd:element max", "elementTag max")
soup = BeautifulStoneSoup(input)

# FIND GET_RECORDS AND GENERATE SQL STATEMENTS
sql_command = "CREATE TABLE "+table_name+" ("
#Parse through soup to find the elements under getRecords
for elem in soup.find(attrs={"name" : "getRecords"}).contents[0].contents[0].contents:
#Add the type based on the type of the element
	if elem['name'][0:2] != "__" : # Make sure it's actually a column name and not an extended parameter
		sql_command += "\"" + elem['name']+"\" "
		if elem['type'] == "xsd:boolean":
			sql_command += "varchar(5)"
		elif elem['type'] == "xsd:string":
			sql_command += "varchar(255)"
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
# Open url with username password
password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
jsonurl = "https://" + instance + "/" + table_name + ".do?JSON"
password_mgr.add_password(None, jsonurl, "", "")
handler = urllib2.HTTPBasicAuthHandler(password_mgr)
try:
	opener = urllib2.build_opener(handler)
	opener.open(jsonurl)
	urllib2.install_opener(opener)
	getRecs = urllib2.urlopen(jsonurl)
except IOError:
	print 'ERROR: Problem opening json data'
# Put page data into a string, jsonData
jsonData = ""
for line in getRecs:
	jsonData += line
# Use simplejson to decode
items = json.loads(jsonData)
records = items["records"] # records is now a list of items to be added to the table
for i in records: # Each i is a dict containing keys/values which are column/data for the table
	insertinto = "INSERT INTO " + table_name + " ("
	values = "VALUES ("
	for key in i:
		insertinto += "\"" + key + "\", "
		temp = i[key].replace("'", "''")
		values += "\'" + temp + "', "
	insertlen = len(insertinto) - 2
	valuelen = len(values) - 2
	insertinto = insertinto[0:insertlen] + ")"
	values = values[0:valuelen] + ")"
	sql_insert = insertinto + " " + values
	con = firebirdsql.connect(host='localhost', database='/var/lib/firebird/2.1/data/sndump.fdb', user='SYSDBA', password='z1x2c3d')
	cur = con.cursor()
	cur.execute(sql_insert)
	con.commit()
