<?php 

/**
*	function autoloads any classes which have not been loaded. 
*	this would be placed in a global configuration file or on a index
*	page. 
*/
function __autoload( $class_name ) {
    require_once( 'ServiceNowSoapClient/Class.'.$class_name . '.php' );
} 

?>
