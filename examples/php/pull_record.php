<?php

	$error = "";
	$server = "";
	$username = "";
	$table_name = "";
	$password = "";
	$sys_id = "";
	$returned_data = array();
	
	if(isset($_POST['submit'])){
		require_once('../../phpsoapclient/class.Record.php');
		require_once('../../phpsoapclient/Class.SoapClient.php');
		if(trim(strlen($_POST['server'])) > 0){
			$server = $_POST['server'];
			if(trim(strlen($_POST['table_name'])) > 0){
				$table_name = $_POST['table_name'];
				if(trim(strlen($_POST['username']))> 0){
					$username = $_POST['username'];
					if(trim(strlen($_POST['password'])) > 0){
						$password = $_POST['password'];
						if(trim(strlen($_POST['sys_id'])) > 0){
							$sys_id = $_POST['sys_id'];

							$clientOptions = array(	'login' => $username,
									'password' => $password,
									'instance' => $server,
									'debug' => FALSE,
									'tableName' => $table_name);
							$client = new SNSoapClient($clientOptions);
							$returned_data = $client->getRecords(array('sys_id'=>$sys_id));
							
						} else {
							header('Location: pull_record.php?error=You must provide a sys_id.');
							exit;
						}
					} else {
						header('Location: pull_record.php?error=You must provide a Service-Now Password.');
						exit;
					}
				} else {
					header('Location: pull_record.php?error=You must provide a Sevice-Now Username.');
					exit;
				}
			} else {
				header('Location: pull_record.php?error=You must provide a Service-Now table name.');
				exit;
			}
		} else {
			header('Location: pull_record.php?error=You must provide a Service-Now instance/server.');
			exit;
		}
	}
?>
<html>
	<head>
		<title>Pulling a record from based on sys_id</title>
		<style type="text/css">
			#error {
				color: #FF0000; 
			}
			#input_data {
				text-align: center;
			}
			.input_field {
				
			}
			.input_line {
			
			}
			
			label {
				margin-right: 20px;
			}
			#returned_data {
				text-align: center;
			}
			
		</style>
	</head>
	<body>
		<div id="error">
			<?php isset($_GET['error']) ? print(htmlspecialchars($_GET['error'])) : NULL;?>
		</div>
		<div id="input_data">
			<form method="post" action="pull_record.php">
				<div class="input_line">
					<label for="server">Server: </label><span class="input_field"><input type="text" name="server" value="<?php $server ? print($server) : print("demo008.service-now.com")?>"></span>
				</div>
				<div class="input_line">
					<label for="table">Table: </label><span class="input_field"><input type="text" name="table_name" value="<?php $table_name ? print($table_name) : print("incident.do");?>"></span>
				</div>
				<div class="input_line">
					<label for="username">Username: </label><span class="input_field"><input type="text" name="username" value="<?php $username ? print($username) : print("admin");?>"></span>
				</div>
				<div class="input_line">
					<label for="password">Password: </label><span class="input_field"><input type="text" name="password" type="password" value="<?php $password ? print($password) : print("admin");?>"></span>
				</div>
				<div class="input_line">
					<label for="sys_id">Sys_ID: </label><span class="input_field"><input type="text" name="sys_id" value="<?php $sys_id ? print($sys_id) : NULL;?>"></span>
				</div>
				<div class="input_line">
					<span class="input_field"><input type="submit" name="submit" value="submit"></span>
				</div>	
			</form>
		</div>
		<div id="returned_data">
			<?php echo(displayTree($returned_data));?>
		</div>
	</body>

</html>
<?php 
function displayTree($array) {
	$output = "";
     $newline = "<br>";
     foreach($array as $key => $value) {    //cycle through each item in the array as key => value pairs
         if (is_array($value) || is_object($value)) {        //if the VALUE is an array, then
            //call it out as such, surround with brackets, and recursively call displayTree.
             $value = "Array()" . $newline . "(<ul>" . displayTree($value) . "</ul>)" . $newline;
         }
        //if value isn't an array, it must be a string. output its' key and value.
        $output .= "[$key] => " . $value . $newline;
     }
     return $output;
}

?>
