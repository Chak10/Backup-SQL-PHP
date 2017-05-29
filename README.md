# **Backup-SQL-By-Chak10** (BETA V1.1.4)

**New** BACKUP TABLE OR DATABASE MySQL with PHP.

``` php
 function __construct($con = null, $table_name = null, $ext = null, $fname = null, $folder = null, $query_limit = null, $archive = null, $phpmyadmin = null, $save = null, $sql_unique = null, $down = null, $header_name = null, $del_csv = null, $enc_csv = null, $json_options = null) {}
 ```
var _**con**_ (Object) => MySQLi or PDO connection already opened. (N.B. It is recommended that you use the connection to the database inside the class) [READ CON SECTION](#con)

var _**table_name**_ (String or Array) => The tables that you want to backup. [READ TABLE SECTION](#-table_name)

var _**ext**_  (_String or Array_) => The extension of the destination file. [READ EXT SECTION](#execute)

var _**fname**_ (String) => The name of the output file/directory secondary

var _**folder**_ (String) => The folder where the files will be saved 

var _**query_limit**_ (Int) => Number of queries at a time to execute in SQL [READ QUERY LIMIT SECTION](#query_limit)

var _**archive**_ (Bool) => It results as an archive. (.zip|.tar)

var _**phpadmin**_ (Bool) => If set to true, it creates files that can be imported directly with phpmyadmin. (sql|csv)

var _**save**_ (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) [READ SAVE SECTION](#save)

var _**sql_unique**_ (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

var _**down**_ (Bool) => If set to true, the output is automatic downloaded. (zip)

[**Execution Times**](https://github.com/Chak10/Backup_SQL-PHP-ByChak10/blob/master/beta/bench.md)

## USE

``` php
$backup = new SQL_Backup();
```

### _con()_

``` php

/**
* @var $HOST string The MySQL host name.
* @var $USER string The MySQL user name.
* @var $PASSWD string The MySQL password.
* @var $NAME string The MySQL database name.
* @var $PORT int The port number to use when connecting to the database server otherwise it uses the default port
* @var $SOCK string The socket name to use when connecting to a local database server otherwise it uses the default socket.
* @return Bool | object
**/

public function con($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {}
```

This feature based on the extensions present uses the most appropriate connection (MYSQLi|PDO)

EXAMPLE:

``` php
$HOST = 'localhost';
$USER = 'root';
$PASSWD = '';
$NAME = 'francesco';
```

Case : Connection already open...

``` php
$con = new mysqli($HOST,$USER,$PASSWD,$NAME); // or PDO
$backup = new SQL_Backup($con); 
```

Case : Connection closed....

``` php
$backup = new SQL_Backup();
$backup->con($HOST,$USER,$PASSWD,$NAME);
```

### _execute()_

``` php
/**
* @var $debug Bool (True|False)
* @return Bool | Array
**/
public function execute($debug = false) {}
```

Run Command

If $debug **does not coincide** __(===)__ with true the result will be:

- If $save is true and there are no errors return true otherwise return false
- If $save is false and there are no errors return array with tables [READ SAVE SECTION](#save) otherwise return false

If $debug **coincides** __(===)__ with true the result will be an array with all the class variables.

EXAMPLE:

``` php
$backup = new SQL_Backup(...);
$res = $backup->execute();
var_dump($res);
```

**Is critical . To run every time at the end!**

### $ _table_name_
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
**If not set, it will backup all the database**

### $_folder_ 

``` php
$backup->folder = "backup/database"; /* ONLY DIR */
```


### $_query_limit_ 

Number of queries at a time to execute in SQL.

Exemple q_limit = 400

>INSERT INTO table ( '' ,'' ,'') VALUES ('' ,'' ,'') ,('' ,'' ,'') ,('' ,'' ,'') ...... * 400 ,('' ,'' ,'');
>INSERT INTO table ( '' ,'' ,'') VALUES ('' ,'' ,'') ,('' ,'' ,'') ,('' ,'' ,'') ...... * 400 ,('' ,'' ,'');
..... etc.


``` php
$backup->qlimit=400;
```

### $_archive_

If set to true the result is an archive. (.zip|.tar)

N.B. Zip is compressed - Tar is not compressed.


``` php
$backup->archive = false;
// or
$backup->archive = 'tar';
// or
$backup->archive = 'zip';
```

[More info](https://github.com/Chak10/Backup_SQL-PHP-ByChak10/blob/master/benchmark/bench_size_exec.md)

### $_ext_

The extension of the destination file.

- SQL extension

``` php
$backup->ext = "sql";

// or 

$backup->ext = "SQL";

// or 

$backup->ext = array("sql");

// or 

$backup->ext = array("SQL");
```
- CSV extension

``` php
$backup->ext = "csv";
```
and other ... (Look SQL)

- JSON extension

``` php
$backup->ext = "json";
```
and other ... (Look SQL)

#### Combinations
​
- SQL + CSV 
​
``` php
$backup->ext = "sql,csv"; // Only the comma as a separator. !!!

// or

$backup->ext = array("sql","csv");
```
**Same for JSON and CSV or JSON and SQL**

 **_For all three formats_**
 
- SQL + CSV + JSON
​
``` php
$backup->ext = "all";

// or

$backup->ext = array("all");

// or

$backup->ext = "sql,csv,json"; // Only the comma as a separator. !!!

// or

$backup->ext = array("sql","csv","json");
```
> NOTE: If you use the CSV extension, if you want you can add the field delimiter and the enclosure of the camps.  
By default they are:
- Delimiter => ,
- Enclosure => 

>Example: Data,DATA2,datA3

SETTING

``` php
$backup->del_csv=";";
$backup->enc_csv="'";
```

### $_phpmyadmin_

If set to true it gives a result that can be imported directly with phpmyadmin (sql|csv)

``` php
$backup->phpmyadmin = true;
```


### $_save_ 

If set to FALSE, the result will not be saved but will be loaded on the variable of Class sql, csv, json (**ARRAY**)

EXAMPLE

JSON:

```txt
array (size=1)
  'json' => 
    array (size=1)
      'airports' => string '[{"id":"6523","ident":"00A","type":"heliport","name":"Total Rf Heliport","latitude_deg":"40.07080078125","longitude_deg":"-74.93360137939453","elevation_ft":"11","continent":"NA","iso_country":"US","iso_region":"US-PA","municipality":"Bensalem","scheduled_service":"no","gps_code":"00A","iata_code":"","local_code":"00A","home_link":"","wikipedia_link":"","keywords":""},{"id":"323361","ident":"00AA","type":"small_airport","name":"Aero B Ranch Airport","latitude_deg":"38.704022","longitude_deg":"-101.473911","'... (length=20358868)
```
 
### $_sql_unique_  

If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

> Table 1 SQL + Table 2 SQL + ETC.

### $_json_option_

The classic options that can be used with json_encode.

[README](http://php.net/manual/en/function.json-encode.php)

EXAMPLE

``` php
$backup->json_option = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES;
```

``` json
{
    "Info": {
        "alldata": {
            "R": 35954,
            "C": 14
        }
    },
    "Avg": 0.5024807643890381
}
```


## DEFAULT SETTING

- Folder = "backup/database";
- Query Limit = 400
- Archive = zip
- Extension = SQL
- Phpmyadmin = false
- Save = true
- Sql_unique = false
- Del= [,]
- Enc= []
- Json_option = null
- Down = null

> **Note: Attention this class has not been tested in all possible situations. So if you have problems you disclose them to me.**.

> **Note 2: I do not take responsibility in case of failure to backup or if the result you do not like or does not work**
