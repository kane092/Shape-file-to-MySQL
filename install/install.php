<?php

error_reporting(0); //Setting this to E_ALL showed that that cause of not redirecting were few blank lines added in some php files.

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	// Load the classes and create the new objects
	require_once('includes/core_class.php');
	require_once('includes/database_class.php');

	$core = new Core();
	$database = new Database();

	// Validate the post data
	if($core->validate_post($_POST) == true)
	{
		// First create the database, then create tables, then write config file
		if($database->create_database($_POST) == false) {
			$message = $core->show_message('error',"The database could not be created, please check if the username and password combination is correct.");
			$arr = array(
				'success' => false, 
				'msg' => $message
			);
			echo json_encode($arr);
		} else if ($database->create_tables($_POST) == false) {
			$message = $core->show_message('error',"The database tables could not be created, please try again.");
			$arr = array(
				'success' => false, 
				'msg' => $message
			);
			echo json_encode($arr);
		} else if ($core->write_config($_POST) == false) {
			$message = $core->show_message('error',"The database configuration file could not be written, please chmod application/config/database.php file to 777");
			$arr = array(
				'success' => false, 
				'msg' => $message
			);
			echo json_encode($arr);
		}

		// If no errors, redirect to registration page
		if(!isset($message)) {
            $arr = array(
			'success' => true, 
			'msg' => 'The installation has been completed succesfully'
		);
        echo json_encode($arr);
		} 

		//
	}else
	{
		$arr = array(
			'success' => false, 
			'msg' => 'Please correct the errors below'
		);
		echo json_encode($arr);
	}
} else
{
	$arr = array(
		'success' => false, 
		'msg' => 'Please resubmit the form again and check that you have filled all fields correctly.'
	);
	echo json_encode($arr);
}

?>