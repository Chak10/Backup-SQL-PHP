# Backup-SQL-By-Chak10
Dump MySQL with PHP.

# =>CLASS<=

__construct($con,$table_name,$folder,$query_limit,$compress,$ext,$alltable_in_file,$save,$sql_unique)

var _con_ (Object) => MySQLi connection already opened.

var _table_name_ (String or Array) => The tables that you want to backup. (READ TABLE SECTION)

var _folder_ (String) => The folder where the files will be saved 

var _query_limit_ (Int) => Number of queries at a time to execute in SQL (READ QUERY LIMIT SECTION)

var _compress_ (Bool) => If set to true the result is compressed. (.zip)

var _ext_ (Int) => The extension of the destination file. (READ EXT SECTION)

var _alltable_in_file_ (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

var _save_ (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) (READ SAVE SECTION)

var _sql_unique_ (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)
