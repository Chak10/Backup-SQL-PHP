# **Backup-SQL-By-Chak10**

BACKUP TABLE OR DATABASE MySQL with PHP.

``` php
$backup = new SQL_Backup();
```

___**construct**_(_$con, $tablename, $folder, $querylimit, $compress, $ext, $alltableinfile, $save, $sqlunique_)

var _**con**_ (Object) => MySQLi connection already opened.

var _**table_name**_ (String or Array) => The tables that you want to backup. [READ TABLE SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10/blob/master/README.md#-table_name-string-or-array)

var _**folder**_ (String) => The folder where the files will be saved 

var _**query_limit**_ (Int) => Number of queries at a time to execute in SQL [READ QUERY LIMIT SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10/blob/master/README.md#query_limit-int)

var _**compress**_ (Bool) => If set to true the result is compressed. (.zip)

var _**ext**_  (_String or Array_) [NEW VERSION >= V1.0.7] => The extension of the destination file. [READ EXT SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10/blob/master/README.md#ext-int)

var _**ext**_ (Int) [OLD VERSION < V1.0.7] => The extension of the destination file. [READ EXT SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10/blob/master/README.md#ext-int)

var _**alltable_in_file**_ (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

var _**save**_ (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) [READ SAVE SECTION](https://github.com/Chak10/Backup-SQL-By-Chak10/blob/master/README.md#save-bool)

var _**sql_unique**_ (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

[**Execution Times**](https://github.com/Chak10/Backup_SQL-PHP-ByChak10/blob/master/benchmark/bench_time_exec.md)

### _con($HOST,$USER,$PASSWD,$NAME,$PORT=null,$SOCK=null)_

This Function is an simple MySQL connection (new mysqli()) 

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

### _execute(void)_

Run Command

**Is critical . To run every time at the end!**


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
**If not set, it will backup all the database**

### $_folder_ (_String_)

``` php
$backup->folder = "backup/database"; /* ONLY DIR */
```


### $_query_limit_ (_Int_)

Number of queries at a time to execute in SQL.

Exemple q_limit = 400

>INSERT INTO table ( '' ,'' ,'') VALUES ('' ,'' ,'') ,('' ,'' ,'') ,('' ,'' ,'') ...... * 400 ,('' ,'' ,'');
>INSERT INTO table ( '' ,'' ,'') VALUES ('' ,'' ,'') ,('' ,'' ,'') ,('' ,'' ,'') ...... * 400 ,('' ,'' ,'');
..... etc.


``` php
$backup->qlimit=400;
```

### $_compress_ (_Bool_)

If set to true the result is compressed. (.zip)

``` php
$backup->compress = true;
```

[More info](https://github.com/Chak10/Backup_SQL-PHP-ByChak10/blob/master/benchmark/bench_size_exec.md)

### $_ext_ (_String or Array_)

**NEW VERSION >= V1.0.7**

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
### $_ext_ (_Int_)

**OLD VERSION < V1.0.7**

The extension of the destination file.

``` php
const SQL = 13;
const CSV = 26;
const JSON = 49;
```
- SQL extension


``` php
$backup->ext = $backup::SQL;
```

``` php
$backup->ext = 13;
```
- CSV extension

``` php
$backup->ext = $backup::CSV;
```

``` php
$backup->ext = 26;
```
- JSON extension

``` php
$backup->ext = $backup::JSON;
```

``` php
$backup->ext = 49;
```

#### Combinations

SQL + CSV 

``` php
$backup->ext = $backup::SQL + $backup::CSV;
```

``` php
$backup->ext = 13 + 26; // 39 
```

``` php
$backup->ext = array($backup::SQL, $backup::CSV);
```

``` php
$backup->ext = array(13,26);
```

``` php
$backup->ext = "39";
```

 **Same for JSON and CSV or JSON and SQL**

 **_For all three formats use ($n > 100)_**

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

### $_alltable_in_file_ (_Bool_)

If set to TRUE: 

- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually.

- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension. Example Choose SQL extension dir/sql/name_file) or else each file will be saved individually.

``` php
$backup->alltable_in_file = true;
```


### $_save_ (_Bool_) 

If set to FALSE, the result will not be saved but will be loaded on the variable of Class sql, csv, json (**ARRAY**)

EXAMPLE

SQL:

object(SQL_Backup)[1]

  ...  
  public 'sql' =>
    array (size=1)    
      'name_table' => string '...'
      
 
### $_sql_unique_ (_Bool_) 

If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

> Table 1 SQL + Table 2 SQL + ETC.

## In V 1.0.5

Added:

### $_close_ (_Bool_)

If set to true, at the end of operations the MySQL connection is closed otherwise the connection will be allowed in the class. 

### $_json_pretty_ (_Bool_)

If set to true, the output of the json will be like this:

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
Otherwise:

``` json
{"Info":{"alldata":{"R":35954,"C":14}},"Avg":0.5024807643890381}
```

### $_info_t_ (_Bool_)

If set to true, some information will be returned in the info array. (Look under)

### $_info_  (_Array_)

Here it will be returned any errors or information.

MySQL Error, table columns and rows, etc.

Example:

``` json
{"alldata":{"R":35954,"C":14}
```

## DEFAULT SETTING

- Folder = "backup/database";
- Query Limit = 400
- Compress = true
- Extension = SQL
- Alltable_in_file = false
- Save = true
- Sql_unique = false
- Del= [,]
- Enc= []
- Close = null
- Info_t = null
- Json_pretty = null

> **Note: Attention this class has not been tested in all possible situations. So if you have problems you disclose them to me.**.

> **Note 2: I do not take responsibility in case of failure to backup or if the result you do not like or does not work**
