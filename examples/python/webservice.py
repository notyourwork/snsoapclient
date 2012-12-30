from SOAPpy import SOAPProxy
# sets the price for a given request item
# using RequestItem's setprice soap service  
#

#dynamic elements of SOAP endpoint construction 
username, password, instance, gliderecord = 'user', 'pass', 'instance', 'WebServiceEndPoint'
proxy = "https://%s:%s@%s.service-now.com/%s.do?SOAP" % (
    username, 
    password, 
    instance,
    gliderecord, 
)

namespace = 'http://www.glidesoft.com/'

server = SOAPProxy(proxy,namespace)

response = server.setprice(number='RITM45680', price='1.99')

print response 

