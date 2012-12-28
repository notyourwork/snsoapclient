import sys, datetime

now = datetime.datetime.now()
now_stamp = now.strftime("%Y-%m-%d %H:%M:%S")
print "Copy script started at: " + now_stamp

#Pass in instance name only.

print "Processing change_task table"
sys.argv.append("change_task")
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sysapproval_approver table"
sys.argv[2] = "sysapproval_approver"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sc_task table"
sys.argv[2] = "sc_task"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing kb_knowledge table"
sys.argv[2] = "kb_knowledge"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sc_req_item table"
sys.argv[2] = "sc_req_item"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sc_request table"
sys.argv[2] = "sc_request"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing problem table"
sys.argv[2] = "problem"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing change_request table"
sys.argv[2] = "change_request"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sys_db_object table"
sys.argv[2] = "sys_db_object"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing incident table"
sys.argv[2] = "incident"
execfile("/var/www/html/servicenow-script/copytable.py")

print "Processing sys_user table"
sys.argv[2] = "sys_user"
execfile("/var/www/html/servicenow-script/copytable.py")

now = datetime.datetime.now()
now_stamp = now.strftime("%Y-%m-%d %H:%M:%S")
print "Script finished at: " + now_stamp