from SOAPpy import SOAPProxy
#dynamic elements of SOAP endpoint construction 
username, password, instance, gliderecord = 'admin', 'explodev1234', 'osuituat', 'change_task'
proxy = "https://%s:%s@%s.service-now.com/%s.do?SOAP" % (username, password, instance, gliderecord, )
namespace = 'http://www.glidesoft.com/'

server = SOAPProxy(proxy,namespace)

start = 0
end = 2
response = server.getRecords(__first_row=start, __last_row=end, __order_by='number')
for r in response:
    for field, value in r.__dict__.iteritems():
        print field, value

    
print "size", len(response) 
