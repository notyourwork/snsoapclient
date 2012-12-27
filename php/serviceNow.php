<?php

	interface ServiceNowSoapClientInterface
	{
		/**
		*	function insert($query) 
		*	inserts a record into service now specific to the given WSDL.
		*	Function does not assume you have provided the required fields
		*	this should be done elsewhere.  
		*	
		*	@require the query array at least includes required fields specific
		*	to the WSDL.
		*	@param string end point WSDL
		*	@param associative_array field -> value pairs
		*	@return boolean true on success, false for failures 
		*
		*/
		public function insert($query); 

		/**
		*	Function: get($sys_id)
		*	Gets a specific record by its system id.  Specific to a given 
		*	WSDL.  
		*
		*	@param: string the system id 
		*	@return a record specific to provided WSDL
		*/
		public function get( $sys_id ); 

		/**
		*	Function: get($sys_id)
		*	Gets a specific record by its system id.  Specific to a given 
		*	WSDL.  
		*
		*	@param: string the system id 
		*	@return a record specific to provided WSDL
		*/
		
		public function getRecord($query); 
		
		/**
		*	Implements the ServiceNow Soap getRecords method.
		*
		*	param: 	array, the query for soap web service 
		*	return: an array containing the record objects or an empty array for no results 
		*/
	
		public function getRecords( $query=array() ); 
	
		/**
		*	Updates a specific record in service now, the record is sepcific
		* 	to provided WSDL location 
		*
		*	@param array - associative array of field -> value pairs 
		*	@requires adhering to WSDL of specific location with at least 
		*	the min requirements.
		*/	 
		public function update($query); 
		public function deleteRecord($id); 

		/**
		*	for any query which is an insert or update we want to replace "" (empty strings)
		*	with the string "NULL" per requirement of service now soap client. 
		*	@param array $query an associative array of key/value pairs for soap insert/update 
		*	@return array $query an associative array of key/value pairs where for any key
		*	pointing to a value = "" (empty string) is now replaced with value = "NULL" 
		*	 
		*/
		function processEmptyValues($query); 
	}

	class ServiceNowClient implements ServiceNowSoapClientInterface
	{
		public $tableName;
		public $WSDL = "https://instance.service-now.com/";
	
		const LOGIN = '';
		const PASSWORD = '';
	
		public $client;
	
		public function __construct( $tableName )
		{
			$this -> WSDL .= $tableName . ".do?displayvalue=all&WSDL";
			$this -> tableName = $tableName;
			
			$this -> client = new SoapClient( $this -> WSDL , self :: getOptions() );
		}
	
		/**  
		*	Used when soap client or needs to perform interaction. 
		*	Creates the array to authenticate with the web service. 
		*
		*	@return associative_array, this.array('login' -> $this->login, 
		*	'password'->$this->password') 
		*/
	
		private static function getOptions()
		{
			return array( 'login'=> self::LOGIN, 'password' => self::PASSWORD, 
				'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'cache_wsdl' => WSDL_CACHE_NONE ); 
		}
	
		private function caughtException($E)
		{
			dump( $E );
			echo $E -> getMessage();
			return false;
		}
	
		public function insert( $query )
		{
			try
			{
				$query = $this -> processEmptyValues( $query );
				
				return $this -> client -> insert( $query );
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
		
		private function fixDefaults( $args )
		{
			if( !is_array( $args ) )
				$objs = array( $args );
			else
				$objs = $args;
				
			$defauls = array(
				"assignment_group",
				"dv_assignment_group",
				"assigned_to",
				"dv_assigned_to",
				"hint",
				"u_stage_type" );
					
			foreach( $objs as $obj )
			{
				foreach( $defauls as $def )
				{
					if( !isset( $obj -> $def ) )
						$obj -> $def = "";
				}
			}
			
			if( !is_array( $args ) )
				return $objs[0];
				
			return $objs;
		}
		private function fixTime( $strTime )
		{
			$fromZone = 'UTC'; 
			$toZone = 'America/New_York';
			
			if( strlen($strTime) > 0 )
			{				
				$date = new DateTime($strTime, new DateTimeZone($fromZone));
					
				$date -> setTimezone(new DateTimeZone($toZone));
				
				$new_date = $date->format('m/d/Y h:ia');

				return $new_date;
			}
			else
				return '';
		}
		
		public function fixDates( $result )
		{			
			$ret = $result;
			
			if( !is_array( $ret ) )
			{
				$ret = $this -> fixDates( array( $ret ) );
				$ret = $ret[0];
			}
					
			foreach( $ret as &$obj )
			{
				if( isset( $obj -> sys_created_on ) )
				{
					$obj -> sys_created_on = $this -> fixTime( $obj -> sys_created_on );
					$obj -> dv_sys_created_on = $obj -> sys_created_on;
				}
				if( isset( $obj -> closed_at ) )
				{
					$obj -> closed_at = $this -> fixTime( $obj -> closed_at );
					$obj -> dv_closed_at = $obj -> closed_at;
				}
			}
			
			return $ret;
		}
	
		public function get( $sys_id )
		{
			try
			{
				$res = $this -> client -> get( array( 'sys_id' => $sys_id ) );

				$res = $this -> fixDefaults( $res );
				$res = $this -> fixDates( $res );
				
				return $res;
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
		
		public function getRecord( $query )
		{
			try
			{
				if( is_array( $query ) )
					$query['__limit'] = 1;
				else
					$query .= "^__limit=1";
					
				$res = $this -> getRecords( $query );
				
				if( empty( $res ) || !$res )
					return false;
									
				return $res[0];
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
		
		public function getRecords( $query = array() )
		{
			try
			{
				$ret = array();
			
				if( !is_array( $query ) )
					$query = $this -> toSoapQuery( $query );
					
				$result = $this -> client -> getRecords( $query );
				
				if( !isset( $result -> getRecordsResult ) )
					return $ret;
		
				$result = $result -> getRecordsResult;
	
				if( !is_array($result) )
					$ret[] = $result;
				else
					$ret = $result;
		
				$ret = $this -> fixDefaults( $ret );
				$ret = $this -> fixDates( $ret );
				
				return $ret;
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
	
		public function update( $query )
		{
			try
			{
				$query = $this -> processEmptyValues( $query );
				
				return $this -> client -> update( $query );
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
	
		public function deleteRecord( $sys_id )
		{
			try
			{
				return $this -> client -> deleteRecord( array( 'sys_id' => $sys_id ) );
			}
			catch( Exception $E )
			{
				return $this -> caughtException($E);
			}
		}
	
		public function processEmptyValues( $query )
		{
			/*foreach( $query as $field => $value )
			{
				if( empty($value) )
					$query[ $field ] = "NULL";
			}
			*/
			return $query; 
		}
		
		protected function toSoapQuery( $query )
		{
			return array( '__encoded_query' => $query );
		}
	}

	abstract class HRObjectClass extends ServiceNowClient
	{
		public $fieldValues = NULL;
		public $allStages = NULL;
		
		public function getRecords( $query = array() )
		{
			$allRecords = array();
			$result = parent::getRecords( $query );
			
			if( $result === false )
				return false;

			foreach( $result as $obj )
			{
				$tmpObj = clone $this;
				$tmpObj = $tmpObj -> createObjectFields( $obj );
				
				array_push( $allRecords, $tmpObj );
			}
				
			return $allRecords;
		}
		
		public function get( $sys_id )
		{			
			$result = parent::get( $sys_id );
			
			if( $result === false )
				return false;
				
			$tmpObj = clone $this;
			$tmpObj = $tmpObj -> createObjectFields( $result );
				
			return $tmpObj;
		}
		
		public function getRecord( $query )
		{			
			$result = parent::getRecord( $query );
				
			if( $result === false )
				return false;
				
			return $result;
		}
		
		public function soapToHRObject( $soapObject )
		{
			if( $soapObject -> dv_sys_class_name == "OnBoarding" )
			{
				if( $this -> tableName != $soapObject -> sys_class_name )
				{	
					$tmpObj = new OnBoarding();
					$tmpObj = $tmpObj -> get( $soapObject -> sys_id );
				}
				else
				{
					$tmpObj = clone $this;
					$tmpObj = $tmpObj -> createObjectFields( $soapObject );
				}
				return $tmpObj;
			}
		}
		
		private function createObjectFields( $soapObject )
		{
			$objAsArray = (array) $soapObject;

			$this -> fieldValues = $objAsArray;
			
			foreach( $objAsArray as $field => $value )
				$this -> $field = $value;
			
			return $this;
		}
		
		public function getAllStages()
		{
			if( $this -> allStages == NULL )
				$this -> allStages = new Stages( $this -> sys_class_name );

			return $this -> allStages;
		}
		
		public function drawStagesTable( $curStageValue, $actualStageValue  )
		{
			$this -> getAllStages();			
				
			return $this -> allStages -> drawStagesTable( $curStageValue, $actualStageValue );
		}
	}

	
class Incident extends TaskObjectClass{
    public function __construct( $tableName = "incident" ){
        parent::__construct( $tableName );
    }
}	
class HR extends HRObjectClass
{
    public function __construct( $tableName = "hr" )
    {
        parent::__construct( $tableName );
    }
    
}	

?>
