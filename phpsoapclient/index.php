<?php
	
	header('Content-Type: text/plain');
	require_once('Class.SoapClient.php');
	require_once('class.Record.php');
	
	$clientOptions = array(	'login' => 'admin', 
							'password' => 'admin', 
							'instance' => 'demo008',
							'debug' => true, 
							'tableName' => "incident.do");
	
	$client = new SNSoapClient($clientOptions);
	
	
	$record = $client->getRecords(array('sys_id'=>'e8e875b0c0a80164009dc852b4d677d5'));
	
// 	echo "User ID: " . $record[0]->soapRecord->assigned_to . "\n\n";
	
// 	var_dump($record[0]);
	
// 	$clientOptions['tableName'] = "user";
	
// 	$client = new SNSoapClient($clientOptions);
	
// 	$user = $client->getRecords(array('sys_id'=>$record[0]->soapRecord->assigned_to));
	
// 	var_dump($user);
print_r($record);
	
?>