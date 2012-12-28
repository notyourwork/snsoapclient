<?php
	
	header('Content-Type: text/plain');
	require_once('Class.SoapClient.php');
	require_once('class.Record.php');
	
	$clientOptions = array(	'login' => 'admin', 
							'password' => 'admin', 
							'instance' => 'demo008',
							'debug' => true, 
							'tableName' => "incident");
	
	$client = new SNSoapClient($clientOptions);
	
	
	$record = $client->getRecords(array('sys_id'=>'e8e875b0c0a80164009dc852b4d677d5'));
	
	var_dump($record);
	
?>