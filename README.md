# Backup-SQL-By-Chak10
Dump MySQL with PHP.

# =>CLASS<=

  SQL_Backup::__construct($con = null,$table_name =null,$folder = null,$query_limit=null,$compress = null,$ext=null,$alltable_in_file=null,$save =null,$sql_unique=null)

$con (Object) => MySQLi connection already opened.

$table_name (String or Array) => The tables that you want to backup. (READ TABLE SECTION)

$folder (String) => The folder where the files will be saved 

$query_limit (Int) => Number of queries at a time to execute in SQL (READ QUERY LIMIT SECTION)

$compress (Bool) => If set to true the result is compressed. (.zip)

$ext (Int) => The extension of the destination file. (READ EXT SECTION)

$alltable_in_file (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

$save (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) (READ SAVE SECTION)

$sql_unique (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)
