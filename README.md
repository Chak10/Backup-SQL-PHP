# Backup-SQL-By-Chak10
Dump MySQL with PHP.

# =>CLASS<=

__construct($con,$table_name,$folder,$query_limit,$compress,$ext,$alltable_in_file,$save,$sql_unique)

var con (Object) => MySQLi connection already opened.

var table_name (String or Array) => The tables that you want to backup. (READ TABLE SECTION)

var folder (String) => The folder where the files will be saved 

var query_limit (Int) => Number of queries at a time to execute in SQL (READ QUERY LIMIT SECTION)

var compress (Bool) => If set to true the result is compressed. (.zip)

var ext (Int) => The extension of the destination file. (READ EXT SECTION)

var alltable_in_file (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

var save (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) (READ SAVE SECTION)

var sql_unique (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)
