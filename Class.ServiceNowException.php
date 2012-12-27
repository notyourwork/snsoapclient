<?php 

class ServiceNowException extends Exception{

	public function __construct($message, $code = 0, Exception $previous = null){
		parent::__construct($message, $code, $previous); 
	}

	//we can use this to return a user friendly string 
	//to inform user of error 
	public function __toString(){
		return __CLASS__.": [{$this->code}]: {$this->message}\n";
	}

	public function handler(){
		
	}
	
}


?>
