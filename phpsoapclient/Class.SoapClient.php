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
}

class SNSoapClient implements ServiceNowSoapClientInterface
{

	public $tableName;
	
	private $WSDL = '';
	private $LOGIN = '';
	private $PASSWORD = '';
	

	/**
	*	The SoapClient object. 
	*	@access private 
	*/
	private $client;
	
	/**
	 * Output debug content? 
	 * @var boolean
	 * @access private
	 */
    private $debug = false; 
    
    /**
     * Time zone to convert from.
     * @var String
     * @access private
     */
    private $fromZone = 'UTC';
    /**
     * Time zone to convert to.
     * @var String
     * @access private
     */
    private $toZone = 'America/New_York';
    
	/**
	*	Constructor accepts a table name as argument
	*	and sets the appropriate WSDL end point for
	*	the web services client. 
	*
	*	@param array of options:
	*	@param Required String instance
	*	@param Required String tableName
	*	@param String login = ''
	*	@param String password = ''
	*	@param String fromTimeZone = 'UTC';
	*	@param String toTimeZone = 'America/New_York';
	*	@param Boolean debug = false
	*		
	*/
	public function __construct($Options)
	{
		/*
		 * If debug is not set, assume false.
		 */
		if(array_key_exists('debug', $Options)){
			$this -> debug = $Options['debug'];
		}
		if($this->debug){
			ini_set('display_errors',1);
			error_reporting(E_ALL|E_STRICT);
		}
		/*
		 * Check to see if an instance was passed in the options. If not, throw an error.
		 * Check to see if the instance starts with http:// or https://, if not prepend https://
		 * Check to see if the instance ends with ".service-now.com", if not append it.
		 * Check to see if the instance ends with "/", if not append "/"
		 */
		if(array_key_exists('instance', $Options)){
			$this->WSDL = $Options['instance'];			
			if((strpos($this->WSDL, 'https://') !== 0) && (strpos($this->WSDL, 'http://') !== 0)){
				$this->WSDL = "https://" . $this->WSDL;
			}
			if(substr($this->WSDL, strlen($this->WSDL)-16) != ".service-now.com" ){
				$this->WSDL .= ".service-now.com";
			}
			if(substr($this->WSDL, strlen($this->WSDL)-1) != "/" ){
				$this->WSDL .= "/";
			}
			
		} else {
			//FIXME: Throw an error, as there can be no default instance of Service-Now
		}
		
		
		//table map provides lookup and if not found we assume tableName
		//provided is expected to be correct and will be used
		$tableMap = array(
				"incident" => "incident",
				"change" => "change_request",
				"problem" => "problem",
				"request" => "sc_request",
				"requestitem" => "sc_req_item",
				"department" => "cmn_department",
				"message" => "cmn_notif_message",
				"device" => "cmn_notif_device",
				"catalogtask" => "sc_task",
				"hr" => "hr",
				"requestitem" => "sc_request_item",
				"usergroup" => "sys_user_group",
				"user" => "sys_user",
				"userhasrole" => "sys_user_has_role",
				"userrole" => "sys_user_role",
				"serviceoffering" => "service_offering",
				"affectedci" => "task_ci",
				"changetask" => "change_task",
				"email" => "sys_email",
				"knowledge" => "kb_knowledge",
				"knowledgefeedback" => "kb_feedback",
				"usergroupmember" => "sys_user_grmember",
				"choice" => "sys_choice"
		);
		
		$this -> tableName = strtolower( $Options['tableName'] );
		
		
		if(array_key_exists('tableName', $Options)){
			if(in_array( $Options['tableName'], array_keys( $tableMap ))){
				$this->tableName = $tableMap[$Options['tableName']];
			} else {
				//FIXME: Throw an error, unsupported table name.
			}
		} else {
			//FIXME: Throw an error, we can't assume the tablename.
		}
		
		$this -> WSDL .= $this->tableName . ".do?WSDL&displayvalue=all";
		
		if(array_key_exists('login', $Options)){
			$this->LOGIN = $Options['login'];		
		}
		
		if(array_key_exists('password', $Options)){
			$this->PASSWORD = $Options['password'];
		}
		
		if(array_key_exists('fromTimeZone', $Options)){
			$this->fromZone = $Options['fromTimeZone'];
		}
		
		if(array_key_exists('toTimeZone', $Options)){
			$this->toZone = $Options['toTimeZone'];		
		}
		
		$this -> client = new SoapClient( $this -> WSDL , self :: getServiceNowOptions() );
	}

	/**  
	*	Used when soap client or needs to perform interaction. 
	*	Creates the array to authenticate with the web service. 
	*
	*	@return associative_array, this.array('login' -> $this->login, 
	*	'password'->$this->password') 
	*/
	private function getServiceNowOptions()
	{
		return array( 'login'=> $this->LOGIN, 'password' => $this->PASSWORD, 
			'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'cache_wsdl' => WSDL_CACHE_NONE ); 
	}

    
	private function caughtException($E){
        if( $this -> debug )
		    echo $E -> getMessage();
		else
            return false;
	}

	public function insert( $query, $defaultValues = array()  ){
		try{
			if( is_object($query) ){		
				$query = $query->getArray(); 
			}
			
			$query = DefaultValues::set( $query , $this->tableName );
			return $this -> client -> insert( $query );
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}
	

	/**
	*	update accepts as a first argument either a record
	*	object in which case it will call getArray() on the
	*	record 
	*
	*	@param object|array If object we will cast to array or
	*	if array we will array itself. 
	*	@param array $defaultValues 
	*	@return 
	*	@see Record 
	*/
	public function update( $query, $defaultValues = array()  ){
		try{
			if( is_object($query) ){		
				$query = $query->getArray(); 
			}
			
			$query = DefaultValues::set( $query , $this->tableName );
			
			return $this -> client -> update( $query );
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}
	

	public function get( $sys_id ){
		try{
			$res = $this -> client -> get( array( 'sys_id' => $sys_id ) );
			$res = DefaultLabels::set( new Record( $res ) , $this->tableName );
			return $res;
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}
	/**
	*	getRecord is a pseudo method which uses 
	*	getRecords method and delimits result set to
	*	1 record.  
	*
	*	@param String or Array 
	*	@return if successful query return a record otherwise
	*	return false 
	*/
	public function getRecord( $query ){
		try{
			//if argument is array add another index for __limit = 1 query delimter
			if( is_array( $query ) )
				$query['__limit'] = 1;
	
			//otherwise this query is a string so append to string the limit = 1 
			else
				$query .= "^__limit=1";
				
			//call get records to obtain the single record 
			$res = $this -> getRecords( $query );
			
			//if result set is empty return false 
			if( empty( $res ) || !$res )
				return false;
			
			//update default labels for resulting record 
			$res = DefaultLabels::set( new Record( $res[0], $this -> tableName ) ); 			
			return $res;
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}
	
	/**
	*	getRecords makes a soap query call to obtain
	*	a record set from Service now web services.
	*	This method accepts an array or a string.  
	*	If string is passed we will call private method
	*	toSoapQuery to "querify" the string before 
	*	making the call.  Additionally, after obtainined
	*	record set dates/times are updated to local time 
	*
	*	@see toSoapQuery
	*	@see fixDates
	*	@param string|array $query which is either a string (encoded query) 
	*	or an array of field:value parameters to query for. 
	*	@return 
	*		
	*/
	public function getRecords( $query = array() ){
		try{
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
	
			$allRecords = array();
			
			foreach( $ret as $soapRecord )
                array_push( 
                    $allRecords, 
                    new Record( $soapRecord , $this -> tableName ) 
                );
				return $allRecords;
// 			return $this -> fixDates( $ret );
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}


	/**
	*	deleteRecord facilitates deleting a record from service now.
	*	The record will be deleted in the table determined when 
	*	constructor is called and is specified by argument to function.
	*	The argument is the sys_id of the record to delete.
	*
	*	@param string $sys_id the uniquely identifying sys_id of the
	*	record to delete. 
	*	@return 
	*		
	*/
	public function deleteRecord( $sys_id ){
		try{
			return $this -> client -> deleteRecord( array( 'sys_id' => $sys_id ) );
		}catch( Exception $E ){
			return $this -> caughtException($E);
		}
	}

	/**
	*	fixTime is a private method which adjusts time fields
	*	to appropriate local timezone. 
	*	
	*	@param string $strTime the time string to adjust 
	*	@return string time string adjusted to local timezone unless
	*	input argument is empty string in which case return empty string. 
	*	@uses DateTimeZone
	*		
	*/
	private function fixTime( $strTime ){
		if( strlen($strTime) > 0 ){
			$date = new DateTime($strTime, new DateTimeZone($this -> fromZone));
				
			$date->setTimezone(new DateTimeZone($this -> toZone));
			
			return $date->format('Y-m-d H:i:s'); 
		}
		else
			return '';
	}
	
	/**
	*	fixDates accepts an array or a string 
	*	and will adjust 
	*	
	*	@param array|string array to adjust dates for or 
	* 	a string which will 
	*	@uses fixTime
	*/
	public function fixDates( $result ){
		return $result;
		
		$ret = $result;
		
		if( !is_array( $ret ) ){
			$ret = $this -> fixDates( array( $ret ) );
			$ret = $ret[0];
		}
				
		foreach( $ret as &$obj ){
			if( isset( $obj -> sys_created_on ) )
				$obj -> created = $this -> fixTime( $obj -> sys_created_on );
		}
		
		return $ret;
	}
	
	/**
	*	toSoapQuery accepts a string argument
	*	and returns an encoded query associative
	*	array. 
	*
	*	@param string $query which we will transform to
	*	a appropriate soap query. 
	*	@return array The associative array containing encoded 
	*	query. 
	*/
	protected function toSoapQuery( $query ){
		return array( '__encoded_query' => $query );
	}
	
}

?>


