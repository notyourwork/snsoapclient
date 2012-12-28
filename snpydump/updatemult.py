import datetime
import os
import sys 
 
now = datetime.datetime.now()
now_stamp = now.strftime("%Y-%m-%d %H:%M:%S")
print "Update script started at: " + now_stamp

#Pass in instance name only.
base_path = os.path.dirname( os.path.realpath(__file__) )
script = os.path.join( base_path, "updatetable.py") 

try:
	print "[info] Processing change_task table"
	sys.argv.append("change_task")
	execfile( script ) 
except:
	print "[error]  processing change_task table"

try:
	print "[info]Processing sysapproval_approver table"
	sys.argv[2] = "sysapproval_approver"
	execfile( script ) 
except:
	print "[error]  processing sysapproval_approver table"

try:
	print "[info] Processing sc_task table"
	sys.argv[2] = "sc_task"
	execfile( script ) 
except:
	print "[error]  processing sc_task table"

try:
	print "[info] Processing kb_knowledge table"
	sys.argv[2] = "kb_knowledge"
	execfile( script ) 
except:
	print "[error]  processing kb_knowledge table"

try:
	print "[info] Processing sc_req_item table"
	sys.argv[2] = "sc_req_item"
	execfile( script ) 
except:
	print "[error]  processing sc_req_item table"

try:
	print "[info] Processing sc_request table"
	sys.argv[2] = "sc_request"
	execfile( script ) 
except:
	print "[error]  processing sc_request table"


try:
	print "[info] Processing problem table"
	sys.argv[2] = "problem"
	execfile( script ) 
except:
	print "[error]  processing problem table"


try:
	print "[info] Processing change_request table"
	sys.argv[2] = "change_request"
	execfile( script ) 
except:
	print "[error]  processing change_request table"


try:
	print "[info] Processing sys_db_object table"
	sys.argv[2] = "sys_db_object"
	execfile( script ) 
except:
	print "[error]  processing sys_db_object table"


try:
	print "[info] Processing sys_user table"
	sys.argv[2] = "sys_user"
	execfile( script ) 
except:
	print "[error]  processing sys_user table"


try:
	print "[info] Processing incident table"
	sys.argv[2] = "incident"
	execfile( script ) 
except:
	print "[error]  processing incident table"

now = datetime.datetime.now()
now_stamp = now.strftime("%Y-%m-%d %H:%M:%S")
print "[info] Script finished at: " + now_stamp
