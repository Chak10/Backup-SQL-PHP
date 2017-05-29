<?php
	
	require("../src/class_backup_SQL.php");
	
	DEFINE ('DB_USER', 'root');
	DEFINE ('DB_HOST', 'localhost');
	DEFINE ('DB_NAME', 'francesco');
	DEFINE ('DB_PASSWD', '');
	
	$con = new mysqli(DB_HOST,DB_USER,DB_PASSWD,DB_NAME);
	
	$final = array();	
	$type = 13;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["SQL_NOCOMPRESSED_X10"]=$fin/$n;
	
	$type = 26;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["CSV_NOCOMPRESSED_X10"]=$fin/$n;
	
	$type = 49;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;;
		$test1->alltable_in_file=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["JSON_NOCOMPRESSED_X10"]=$fin/$n;
	$type = 13;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->alltable_in_file=true;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["SQL_COMPRESSED_X10(No-separate)"]=$fin/$n;
	
	$type = 26;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->alltable_in_file=true;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["CSV_COMPRESSED_X10(No-separate)"]=$fin/$n;
	
	$type = 49;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->alltable_in_file=true;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["JSON_COMPRESSED_X10(No-separate)"]=$fin/$n;
	$type = 13;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["SQL_COMPRESSED_X10(Separate)"]=$fin/$n;
	
	$type = 26;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["CSV_COMPRESSED_X10(Separate)"]=$fin/$n;
	
	$type = 49;
	$n = 10;
	$fin =0;
	for($x=0;$x<$n;++$x){
		$time = -microtime(true);
		$test1 = new SQL_Backup($con,'alldata');
		$test1->ext=$type;
		$test1->compress=true;
		$test1->execute();
		$time += microtime(true);
		$fin += $time;
	}
	$final["JSON_COMPRESSED_X10(Separate)"]=$fin/$n;
	
	var_dump(json_encode($final,JSON_PRETTY_PRINT));
	
?>