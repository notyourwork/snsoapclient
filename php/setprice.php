<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL|E_STRICT); 
date_default_timezone_set('EST'); 
define("CLI", (php_sapi_name() == 'cli'));

$username = "user"; 
$password = "pass"; 
$WSDL = "https://instance.service-now.com/RequestItem.do?WSDL"; 
$AUTH = array(
    'login'=>$username,
    'password'=>$password,
    'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 
    'cache_wsdl'=>WSDL_CACHE_NONE,
    'trace' => 1
);

$client = new SoapClient( $WSDL, $AUTH );

$data = array(
    'number' => 'RITM10164',
    'price' => '11.99'
);

$function = "setprice"; 
$response = $client->$function( $data ); 

if( CLI ){
    echo "---Message---------------------------------------\n"; 
    print_r( $data); 
    echo "---Response---------------------------------------\n"; 
    print_r($response);
    echo "---REQUEST HEADERS:---------------------------------------\n"; 
    echo $client->__getLastRequestHeaders();
    echo "---REQUEST XML:--------------------------------------------\n";
    echo $client->__getLastRequest()."\n"; 
    echo "---RESPONSE HEADERS:---------------------------------------\n"; 
    echo $client->__getLastResponseHeaders();
    echo "--RESPONSE XML:---------------------------------------------\n";
    echo $client->__getLastResponse()."\n"; 
}else{
    echo "<hr/>"."<strong>REQUEST HEADERS:</strong>".$client->__getLastRequestHeaders(); 
    echo "<hr/><strong>REQUEST</strong>".htmlentities($client->__getLastRequest()); 
    echo "<hr/>"."<strong>RESPONSE HEADERS:</strong>".$client->__getLastResponseHeaders(); 
    echo "<hr/><strong>RESPONSE</strong>".htmlentities($client->__getLastResponse()); 
}

?>
