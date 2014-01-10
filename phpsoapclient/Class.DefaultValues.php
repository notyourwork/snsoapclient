<?php 

//Class for default values of records 
class DefaultValues
{

	private static function getDefaults( )
	{
		return array(
			"incident" => array(
				"urgency" => "3",						//low urgency 
				"impact" => "3",						//low impact 
				"incident_state" => "2",				//assigned state 
				"assignment_group" => eval("
					switch( $query->category ){
						'T&N Finance':
							$query->assignment_group = 'Finances';  
							break; 
						'default':
							$query->assignment_group = '8help'; 
					}"
				),	 
				"contact_type" => "self-service"
			),	
			 
			"hr" => array(

			), 
			"sc_request" => array(
				"assignment_group" => "" 
			),
			"sc_req_item" => array(
				"" => ""
			),
			"default" => array(
				"urgency" => "3",
				"impact" => "3" 
			),  
		); 
	}

	public static function getValuesForTable( $tableName ) 
	{
		return array_merge( DefaultValues::getDefaults( ), $this->defaults[ $tableName ] ); 
	}
	
	public static function set( $query , $tableName = "" )
	{	
		$allValues = DefaultValues::getDefaults();
		
		$values = isset( $allValues[ $tableName ] ) ? $allValues[ $tableName ] : array();
			
		foreach( $values as $field => $value ) 
			if( !isset( $query[ $field ] ) )
				$query[ $field ] = $value;
		
		$values = $allValues['default'];
		
		foreach( $values as $field => $value ) 
			if( !isset( $query[ $field ] ) )
				$query[ $field ] = $value;
				
		return $query;
	}
	
}
?>
