from SOAPpy import SOAPProxy

#dynamic elements of SOAP endpoint construction 
username, password, instance, gliderecord = 'user', 'pass', 'instance', 'endpoint'
proxy = "https://%s:%s@%s.service-now.com/%s.do?SOAP" % (
    username, 
    password, 
    instance, 
    gliderecord, 
)

namespace = 'http://www.glidesoft.com/'

server = SOAPProxy(proxy,namespace)
#limit record set? 
start, end = 0, 1

response = server.getRecords(
    _order_by='number', 
    __first_row=start, 
    __last_row=end
)

for r in response:
    for field, value in r.__dict__.iteritems():
        print field, value    
print "size", len(response)

