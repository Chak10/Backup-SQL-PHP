<?php
	
	require("../src/class_backup_SQL.php");
	
	DEFINE ('DB_USER', 'root');
	DEFINE ('DB_HOST', 'localhost');
	DEFINE ('DB_NAME', 'francesco');
	DEFINE ('DB_PASSWD', '');
	
	$con = new mysqli(DB_HOST,DB_USER,DB_PASSWD,DB_NAME);
	
	$test1 = new SQL_Backup($con);
	
	$test1->table_name='alldata'; 
	
	$test1->ext = $test1::SQL; 
	
	$test1->compress = true;
	
	$test1->alltable_in_file = true;
	
	$test1->save = false;
	
	$test1->execute();
	
	var_dump ($test1);
	
?>