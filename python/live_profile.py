from SOAPpy import SOAPProxy

#dynamic elements of SOAP endpoint construction 
username, password, instance, gliderecord = 'admin', 'explodev1234', 'osuitsmdev', 'GetLiveProfileId'
proxy = "https://%s:%s@%s.service-now.com/%s.do?SOAP" % (username, password, instance, gliderecord, )
namespace = 'http://www.glidesoft.com/'

server = SOAPProxy(proxy,namespace)
#limit record set? 
start, end = 0, 2

response = server.get(username='sherman.1206' )
print response 


