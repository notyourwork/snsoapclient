<?php
interface ServiceNowSoapRecord {
	
}

Class Record implements ServiceNowSoapRecord {
	
	public $soapRecord = NULL;
	public $tableType = NULL;
	
	/**
	 * 
	 * Create the record with the corresponding table type.
	 * 
	 * @param Array $SoapRecord
	 * @param String $TableType
	 */
	function __construct($SoapRecord, $TableType){
		$this->soapRecord = $SoapRecord;
		$this->tableType = $TableType;
	}
}
?>
