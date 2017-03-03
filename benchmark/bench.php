<?php
	
	require("../src/class_backup_SQL.php");
	
	DEFINE ('DB_USER', 'root');
	DEFINE ('DB_HOST', 'localhost');
	DEFINE ('DB_NAME', 'francesco');
	DEFINE ('DB_PASSWD', '');
	$table_name = "alldata";
	$bench = array();
	$i=0;
	while($i <25){
		$time = -microtime(true);
		
		$con = new mysqli(DB_HOST,DB_USER,DB_PASSWD,DB_NAME);
		$test1 = new SQL_Backup($con,$table_name);
		$test1->ext=$test1::SQL;
		$test1->json_pretty=true;
		$test1->save = false;
		$test1->info_t = true;
		$test1->execute();
		
		$time += microtime(true);
		
		$bench["Time_".$i] = $time;
		++$i;
	}
	$bench =array_sum($bench);
	$fin = array("Info"=>$test1->info,"Tot"=>$bench,"Avg"=>$bench/25);
	
	$test2 = file_put_contents("res/test_nosave_SQL_X25_".time().'.json',json_encode($fin,JSON_PRETTY_PRINT));
	
	var_dump ($fin);
	
?>