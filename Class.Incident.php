<?php 

/**
*	incident class provides functionality specifiy to service now
*	incidents.  It includes some class fields {requiredFields, r
*	enderFields, hiddenFields} which all define various display 
*	for an incident.  requiredFiends['insert'] provides a list of fields
*	required for insert, as does update for updating an incident.
*
*	Assignment group, urgency, impact are all defined as default values for
*	an incident insert.  
*	
*/
class Incident{
	
    public $client;
	const QueryIncidentStateClosed = "incident_state=7"; 
	const QueryIncidentStateOpen = "incident_state=2^ORincident_state=3^ORincident_state=5"; 
	
    public function __construct()
    {   
        $this->client = new SoapClient( get_class( $this ) );
    }

	public function getActivity( $id )
	{
		$auditClient = new Audit(); 
		return $audit->getHistory( $id ); 
	}	
	private function getByFieldValue( $field, $value )
	{
		return $this->client->getRecords( $field."=".$value ); 

	}
	/**
	*	function returns incidents that are owned by the system id of the specified 
	*	first argument to the function. 
	*
	*	@param string the system id of the user which is the caller id field of incidents 
	*
	*	@return array of records  
	*
	*/
	public function getByCaller( $id )
	{
		$query = 'caller_id='.$id;
		$encodedQuery = array( '__encoded_query' => $query, '__order_by_desc' => 'sys_updated_on' );
		return $this->client->getRecords( $encodedQuery );
	}

	/**
	*	function returns an array of incidents corresponding to the user name.N provided
	*	this is achieved by using ServiceNowUserManager::getSysID($nameN) before calling 
	*	this::getIncidentsByCaller() which expects a system id for a user.
	*
	*	@param string $nameN a user name.n to lookup 
	*	@return array of incidents specific to this user whcih are NOT in the closed state 	
	*
	*/
	public function getByNameN($nameN, $limit = 0){
		//instantiate new user object to query for sys id of user 
		$u = new SysUser(); 
		return $this->getIncidentsByCaller($u->getSysID("user_name", $nameN), true); 
	
	}//end getIncidentsbyNameN 
	
	/**
	*	function returns an array of incidents corresponding to the user name.N provided
	*	this is achieved by using ServiceNowUserManager::getSysID($nameN) before calling 
	*	this::getIncidentsByCaller() which expects a system id for a user.
	*
	*	@param string $nameN a user name.n to lookup 
	*	@return array of incidents specific to this user whcih ARE in the closed state 	
	*
	*/	
	public function getClosedIncidents($id , $limit = 0)
	{
        $query = 'caller_id='.$id.self::QueryIncidentStateClosed;
        $encodedQuery = array( '__encoded_query' => $query, '__order_by_desc' => 'sys_updated_on' );
        return $this->client->getRecords( $encodedQuery );
	
	} 
	
		

	/**
	*	function inserts an incident via ServiceNowSoapClient using
	*	this.$WSDL specifying the WSDL for this submission.
	*	@param associative array containing incident elements 
	*
	*/
	public function submit( $record ){ 
	
	/*	
		$incidentSubmission = array(); 
		$allowed = false; 
		switch($_POST['category']){
			case 'Financial Systems':
				//financial 
				$incident['assignment_group'] = '53f3474f0a0a3c2601a0d51ac9949b87';
				break;
			case 'Telecommunications & CATV':
				//Telecomm 
				$incident['assignment_group'] = '789f9ebb0a0a3c260076fc0b0d32f475';
				break;
			case 'OSC Services':
				$incident['assignment_group'] = 'e14c22270a0a3c0500a280ee58b3bcfb';
				break;
			default: 
				$incident['assignment_group'] = Incident::$assignmentGroup;
		}
		
		$incident['impact'] = Incident::$impact;
		$incident['incident_state'] = Incident::$incidentState;
		$incident['urgency'] = Incident::$urgency;
		return parent::insert($incident);
	*/
        return $this->client->insert( $record );

	}//end insertIncident
	
	
		
	/**
	*	function queries service now for incident with the specified 
	*	incident number (usually of the form incXXXXXX) 
	*
	*	@param string $incident the incident number in service now (diff from system id) 
	*	@return object representing the incident 
	*
	*/
	public function getIncidentByNumber($number)
	{
		return $this->client->getRecord( array( "number=".$number ) ); 
	}
	
	/**
	*	function returns incident object for last incident updated 
	*	by users or false if no object exists.
	*	@param String $callerID the system id for user 
	*	@return array(incident object) an array containing 1 element the last 
	*	updated incident
	*/
	public function getLastIncidentForUser( $id )
	{
		return $this->client->getRecord( array('__encoded_query'=>'caller_id='.$id , '__order_by_desc' => 'sys_updated_on', '__limit'=>'1') );
	}	

	public function getActivity($sys_id){
		$a = new Audit(); 
		return $a->getHistory($sys_id); 
	}
}


?>
