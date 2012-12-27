from SOAPpy import SOAPProxy

username, password, instance, gliderecord = '', '', '', ''
proxy = "https://%s:%s@%s.service-now.com/%s.do?SOAP" % (username, password, instance, gliderecord, )
namespace = 'http://www.glidesoft.com/'

server = SOAPProxy(proxy,namespace)
#limit record set? 
start, end = 0, 2

print server.insert(
    u_username='',
    u_text="this is my kiosk contact data", 
    u_osu_id='32345235235',
    u_short_description = 'my custom short description' 
)


