<?php
	
	require("../src/class_backup_SQL.php");
	
	DEFINE ('DB_USER', 'root');
	DEFINE ('DB_HOST', 'localhost');
	DEFINE ('DB_NAME', 'francesco');
	DEFINE ('DB_PASSWD', '');
	
	$con = new mysqli(DB_HOST,DB_USER,DB_PASSWD,DB_NAME);

	$test1 = new SQL_Backup($con,'alldata',null,200,null,null,null,false);
	
	$test1->execute();
	
	var_dump ($test1);
	
?>