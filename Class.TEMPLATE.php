<?php 

class TEMPLATE{
	public $client; 	

	public function __construct()
	{ 
		$this->client = new SoapClient( get_class( $this ) ); 
	} 

	
}

?>


