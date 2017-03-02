# **Backup-SQL-By-Chak10**

BACKUP TABLE OR DATABASE MySQL with PHP.

``` php
$backup = new SQL_Backup();
```

___**construct**_(_$con, $tablename, $folder, $querylimit, $compress, $ext, $alltableinfile, $save, $sqlunique_)

var _**con**_ (Object) => MySQLi connection already opened.

var _**table_name**_ (String or Array) => The tables that you want to backup. [READ TABLE SECTION](#-table_name-string-or-array)

var _**folder**_ (String) => The folder where the files will be saved 

var _**query_limit**_ (Int) => Number of queries at a time to execute in SQL (READ QUERY LIMIT SECTION)

var _**compress**_ (Bool) => If set to true the result is compressed. (.zip)

var _**ext**_ (Int) => The extension of the destination file. (READ EXT SECTION)

var _**alltable_in_file**_ (Bool) => If set to true: 
- If the 'compress' variable is true all the files will be saved in a single zip file otherwise all will be saved individually
- If the 'compress' variable is false all the files will be saved into a single folder (Depending on the extension) or else each file will be saved individually

var _**save**_ (Bool) => If set to false, the result will not be saved but will be loaded on the variable of class sql, csv, json (Based on request) (READ SAVE SECTION)

var _**sql_unique**_ (Bool) => If set to true the SQL dump is a single file with all the tables. (Valid only for the SQL format)

### _con($HOST,$USER,$PASSWD,$NAME,$PORT=null,$SOCK=null)_

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

### _execute(NULL)_

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

### $_ext_ (_Int_)

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

 _For all three formats use ($n > 100)_



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
      
      





