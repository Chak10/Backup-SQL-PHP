# **Backup-SQL-By-Chak10**
Dump MySQL with PHP.

> ## **CLASS**

``` php
$backup = new SQL_Backup();
```

___**construct**_(_$con, $tablename, $folder, $querylimit, $compress, $ext, $alltableinfile, $save, $sqlunique_)

var _**con**_ (Object) => MySQLi connection already opened.

var _**table_name**_ (String or Array) => The tables that you want to backup. [READ TABLE SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10#-table_name)

var _**folder**_ (String) => The folder where the files will be saved 

var _**query_limit**_ (Int) => Number of queries at a time to execute in SQL (READ QUERY LIMIT SECTION)

var _**compress**_ (Bool) => If set to true the result is compressed. (.zip)

var _**ext**_ (Int) => The extension of the destination file. (READ EXT SECTION)

var _**alltable_in_file**_ (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

var _**save**_ (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) (READ SAVE SECTION)

var _**sql_unique**_ (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

### $ _con_ (_Object_)

This Function is an simple Mysql connection (new mysqli()) 

EXAMPLE:

``` php
$HOST = 'localhost';
$USER = 'root';
$PASSWD = '';
$NAME = 'francesco';
```

Case : Connection already open...

``` php
$con = new mysqli($HOST,$USER,$PASSWD,$NAME);
$backup = new SQL_Backup($con);
```

Case : Connection closed....

``` php
$backup = new SQL_Backup();
$backup->con($HOST,$USER,$PASSWD,$NAME);
```



### $ _table_name_ (_String or Array_)   

The tables that you want to backup.

EXAMPLE:

String: 

``` php
$backup->table_name = "users,alldata";
```

Array:

``` php
$backup->table_name = array('users','alldata');
```

### $_folder_ (_String_)

``` php
$backup->folder = "backup/database"; /* ONLY DIR */
```








