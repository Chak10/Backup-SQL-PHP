<?php

class DB {
    
    public $con;
    public $type;
    public $err_c;
    
    /**
     * @var $HOST string The MySQL host name.
     * @var $USER string The MySQL user name.
     * @var $PASSWD string The MySQL password.
     * @var $NAME string The MySQL database name.
     * @var $PORT int The port number to use when connecting to the database server otherwise it uses the default port
     * @var $SOCK string The socket name to use when connecting to a local database server otherwise it uses the default socket.
     * @return Bool | object
     **/
    
    public function con($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {
        if (class_exists("mysqli")) {
            $this->type = "mysqli";
            return $this->con_mysqli($HOST, $USER, $PASSWD, $NAME, $PORT, $SOCK);
        } elseif (class_exists("PDO") && in_array('mysql', PDO::getAvailableDrivers())) {
            $this->type = "PDO";
            return $this->con_pdo($HOST, $USER, $PASSWD, $NAME, $PORT, $SOCK);
        } else {
            return false;
        }
    }
    
    public function query_pdo($con, $sql) {
        try {
            $res = $con->prepare($sql);
            if ($res)
                $res->execute();
        }
        catch (PDOException $e) {
            $this->last_err_db = $e->getMessage();
            return false;
        }
        return $res;
    }
    
    protected function con_mysqli($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {
        try {
            $res = @new mysqli($HOST, $USER, $PASSWD, $NAME, $PORT != null ? $PORT : ini_get("mysqli.default_port"), $SOCK != null ? $SOCK : ini_get("mysqli.default_socket"));
            if ($res->connect_error)
                throw new Exception($res->connect_error . '(Code ' . $res->connect_errno . ')');
            else
                return $this->con = $res;
        }
        catch (Exception $e) {
            return $this->err_c = 'Connection failed: ' . $e->getMessage();
        }
    }
    
    protected function con_pdo($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {
        try {
            $CON = "mysql:host=$HOST;dbname=$NAME;charset=utf8";
            $CON .= $PORT != null ? ";port=" . $PORT : '';
            $CON .= $SOCK != null ? ";unix_socket=" . $SOCK : '';
            $OPT = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            );
            return $this->con = new PDO($CON, $USER, $PASSWD, $OPT);
        }
        catch (PDOException $e) {
            return $this->err_c = 'Connection failed: ' . $e->getMessage();
        }
    }
    
}

class FORMAT extends DB {
    
    protected $last_err_db;
    
    protected function sql_mysqli($con, $table, $limit) {
        
        if (!is_int($limit))
            $limit = 400;
        
        $fields = '';
        
        /* DB REQUESTS */
        
        $info = $con->query("SHOW TABLE STATUS WHERE NAME LIKE '$table'");
        
        if (!$this->err_mysql($info, $con))
            return false;
        
        $info = $info->fetch_assoc();
        
        $res = $con->query("SHOW CREATE TABLE `" . $table . "`");
        
        if (!$this->err_mysql($res, $con))
            return false;
        
        $table_init = $res->fetch_row();
        
        $result = $con->query("SELECT * FROM `" . $table . "`");
        
        if (!$this->err_mysql($result, $con))
            return false;
        
        $num_fields = $result->field_count;
        $num_rows = $result->num_rows;
        
        /* FIELDS */
        
        while ($field_info = $result->fetch_field()) {
            $fields .= "`" . $field_info->name . "`,";
            $db = $field_info->db;
        }
        $fields = substr($fields, 0, -1);
        
        /* HEADER */
        
        $return = "-- Backup SQL By Chak10" . PHP_EOL . "-- Version: " . SQL_Backup::version . PHP_EOL . "-- Github: " . SQL_Backup::site . PHP_EOL . "--" . PHP_EOL . "--" . PHP_EOL . "-- Server Version: " . $con->server_info . PHP_EOL . "-- PHP Version: " . (PHP_VERSION) . PHP_EOL . "-- Host Info: " . $con->host_info . PHP_EOL . "-- Extension Used: MYSQLI" . PHP_EOL . "-- Date: " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL . PHP_EOL . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . PHP_EOL . "SET time_zone = \"+00:00\";" . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . PHP_EOL . "/*!40101 SET NAMES utf8 */;" . PHP_EOL . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Charset General: " . $con->get_charset()->charset . PHP_EOL . "-- Charset Table: " . $info['Collation'] . PHP_EOL . "--" . PHP_EOL . PHP_EOL . "-- ------------------------------------------" . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Table Name: `$table`" . PHP_EOL . "-- Database: $db" . PHP_EOL . "--" . PHP_EOL . "-- Columns: $num_fields" . PHP_EOL . "-- Rows: $num_rows" . PHP_EOL . "--" . PHP_EOL . PHP_EOL;
        
        /* TABLE CREATOR */
        
        $return .= "DROP TABLE IF EXISTS " . $table . ";" . PHP_EOL;
        $return .= $table_init[1] . ";" . PHP_EOL . PHP_EOL . PHP_EOL;
        
        /* TABLE DATA */
        
        for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
            while ($row = $result->fetch_row()) {
                if ($s == 0)
                    $return .= "INSERT INTO `$table` ( $fields ) VALUES " . PHP_EOL . "(";
                elseif (is_int($s / $limit) === true)
                    $return .= ";" . PHP_EOL . "INSERT INTO `$table` ( $fields ) VALUES " . PHP_EOL . "(";
                else
                    $return .= "," . PHP_EOL . "(";
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j]))
                        $return .= "'" . addslashes($row[$j]) . "'";
                    else
                        $return .= "''";
                    
                    if ($j < ($num_fields - 1))
                        $return .= ",";
                }
                $return .= ")";
                ++$s;
            }
        }
        
        /* FOOTER */
        
        if ($num_rows != 0)
            $return .= ';';
        $return .= PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
        
        return $return;
    }
    
    protected function sql_pdo($con, $table, $limit) {
        
        if (!is_int($limit))
            $limit = 400;
        
        $fields = '';
        
        /* DB REQUESTS */
        
        $info = $this->query_pdo($con, "SHOW TABLE STATUS WHERE NAME LIKE '$table'")->fetch(PDO::FETCH_ASSOC);
        
        if (!$info)
            return false;
        
        $table_init = $this->query_pdo($con, "SHOW CREATE TABLE `" . $table . "`")->fetch(PDO::FETCH_NUM);
        
        if (!$table_init)
            return false;
        
        $charset = $this->query_pdo($con, "SELECT @@character_set_database;")->fetch(PDO::FETCH_NUM);
        
        if (!$charset)
            return false;
        
        $db = $this->query_pdo($con, "SELECT DATABASE()")->fetchColumn();
        
        if (!$db)
            return false;
        
        $result = $this->query_pdo($con, "SELECT * FROM `" . $table . "`");
        
        if (!$result)
            return false;
        
        $num_fields = $result->columnCount();
        $num_rows = $result->rowCount();
        
        /* HEADER */
        
        $return = "-- Backup SQL By Chak10" . PHP_EOL . "-- Version: " . SQL_Backup::version . PHP_EOL . "-- Github: " . SQL_Backup::site . PHP_EOL . "--" . PHP_EOL . "--" . PHP_EOL . "-- Server Version: " . $con->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL . "-- PHP Version: " . PHP_VERSION . PHP_EOL . "-- Host Info: " . $con->getAttribute(PDO::ATTR_CONNECTION_STATUS) . PHP_EOL . "-- Extension Used: PDO" . PHP_EOL . "-- Date: " . date('Y-m-d H:i:s') . PHP_EOL . PHP_EOL . PHP_EOL . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . PHP_EOL . "SET time_zone = \"+00:00\";" . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . PHP_EOL . "/*!40101 SET NAMES utf8 */;" . PHP_EOL . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Charset General: " . $charset[0] . PHP_EOL . "-- Charset Table: " . $info['Collation'] . PHP_EOL . "--" . PHP_EOL . PHP_EOL . "-- ------------------------------------------" . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Table Name: `$table`" . PHP_EOL . "-- Database: $db" . PHP_EOL . "--" . PHP_EOL . "-- Columns: $num_fields" . PHP_EOL . "-- Rows: $num_rows" . PHP_EOL . "--" . PHP_EOL . PHP_EOL;
        
        /* TABLE CREATOR */
        
        $return .= "DROP TABLE IF EXISTS " . $table . ";" . PHP_EOL;
        $return .= $table_init[1] . ";" . PHP_EOL . PHP_EOL . PHP_EOL;
        
        /* TABLE DATA */
        
        for ($ind = 0; $ind < $num_fields; ++$ind) {
            $name_c = $result->getColumnMeta($ind);
            $fields .= "`" . $name_c['name'] . "`,";
        }
        $fields = substr($fields, 0, -1);
        
        for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                if ($s == 0)
                    $return .= "INSERT INTO `$table` ( $fields ) VALUES " . PHP_EOL . "(";
                elseif (is_int($s / $limit) === true)
                    $return .= ";" . PHP_EOL . "INSERT INTO `$table` ( $fields ) VALUES " . PHP_EOL . "(";
                else
                    $return .= "," . PHP_EOL . "(";
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j]))
                        $return .= "'" . addslashes($row[$j]) . "'";
                    else
                        $return .= "''";
                    
                    if ($j < ($num_fields - 1))
                        $return .= ",";
                }
                $return .= ")";
                ++$s;
            }
        }
        
        /* FOOTER */
        
        if ($num_rows != 0)
            $return .= ';';
        $return .= PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
        return $return;
        
    }
    
    protected function csv_mysqli($con, $table, $del, $enc, $header_name) {
        
        $return = $fields = '';
        
        /* DB REQUESTS */
        
        $result = $con->query("SELECT * FROM `" . $table . "`");
        
        if (!$this->err_mysql($result, $con))
            return false;
        
        $num_fields = $result->field_count;
        
        if (!is_string($del) || strlen($del) != 1)
            $del = ",";
        if (!is_string($enc) || strlen($enc) != 1)
            $enc = "";
        
        /* HEADER */
        
        if ($header_name === true) {
            
            /* FIELDS */
            
            while ($field_info = $result->fetch_field())
                $fields .= $enc . $field_info->name . $enc . $del;
            
            $return = substr($fields, 0, -1) . PHP_EOL;
            
        }
        
        
        /* TABLE DATA */
        
        for ($i = 0; $i < $num_fields; ++$i) {
            while ($row = $result->fetch_row()) {
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j]))
                        $return .= $enc . $row[$j] . $enc;
                    else
                        $return .= $enc . $enc;
                    if ($j < ($num_fields - 1))
                        $return .= $del;
                }
                $return .= PHP_EOL;
            }
        }
        
        return $return;
    }
    
    protected function csv_pdo($con, $table, $del, $enc, $header_name) {
        
        $return = $fields = '';
        
        /* DB REQUESTS */
        
        $result = $this->query_pdo($con, "SELECT * FROM `" . $table . "`");
        
        if (!$result)
            return false;
        
        $num_fields = $result->columnCount();
        
        if (!is_string($del) || strlen($del) != 1)
            $del = ",";
        if (!is_string($enc) || strlen($enc) != 1)
            $enc = "";
        
        /* HEADER */
        
        if ($header_name === true) {
            
            /* FIELDS */
            
            for ($ind = 0; $ind < $num_fields; ++$ind) {
                $name_c = $result->getColumnMeta($ind);
                $fields .= $enc . $name_c['name'] . $enc . $del;
            }
            
            $return = substr($fields, 0, -1) . PHP_EOL;
        }
        
        /* TABLE DATA */
        
        for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                for ($j = 0; $j < $num_fields; $j++) {
                    if (isset($row[$j]))
                        $return .= $enc . $row[$j] . $enc;
                    else
                        $return .= $enc . $enc;
                    if ($j < ($num_fields - 1))
                        $return .= $del;
                }
                $return .= PHP_EOL;
            }
        }
        return $return;
    }
    
    protected function json_mysqli($con, $table, $options) {
        
        /* DB REQUESTS */
        
        $result = $con->query("SELECT * FROM `" . $table . "`");
        
        if (!$this->err_mysql($result, $con))
            return false;
        
        /* TABLE DATA */
        
        $return = $result->fetch_all(MYSQLI_ASSOC);
        
        if (is_int($options) && $options != 0)
            return json_encode($return, $options);
        return json_encode($return);
    }
    
    protected function json_pdo($con, $table, $options) {
        
        /* DB REQUESTS */
        
        $result = $this->query_pdo($con, "SELECT * FROM `" . $table . "`");
        
        if (!$result)
            return false;
        
        /* TABLE DATA */
        
        $return = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if (is_int($options) && $options != 0)
            return json_encode($return, $options);
        return json_encode($return);
    }
    
    private function err_mysql($res, $con) {
        if (!$res) {
            $this->last_err_db = "Error Code(" . $con->errno . "): " . $con->error;
            return false;
        }
        return true;
    }
    
}

class FILES extends FORMAT {
    
    public $ext_c_supported;
    
    function __construct() {
        if (class_exists('PharData'))
            $this->ext_c_supported[] = "tar";
        if (extension_loaded('zip'))
            $this->ext_c_supported[] = "zip";
    }
    
    protected static function std_file($str, $name_int, $name_ext = false, $dir_int = '') {
        if ($dir_int != '') {
            if (!is_dir($dir_int))
                mkdir($dir_int, 0764, true);
            $name_ext = $name_ext . DIRECTORY_SEPARATOR . $dir_int;
        }
        $fname = $name_int;
        if ($name_ext != false) {
            if (!is_dir($name_ext))
                mkdir($name_ext, 0764, true);
            $fname = $name_ext . DIRECTORY_SEPARATOR . $name_int;
        }
        return file_put_contents($fname, $str);
    }
    
    protected static function zip_file($str, $name_int, $name_ext) {
        $zip_array = false;
        $zip = new ZipArchive();
        if ($zip->open($name_ext, ZIPARCHIVE::CREATE)) {
            if (is_string($str) && is_string($name_int)) {
                $zip->addFromString($name_int, $str);
            } elseif (is_array($str) && is_array($name_int)) {
                if (count($str) == count($name_int))
                    $zip_array = array_combine($name_int, $str);
            } elseif (is_array($str) || is_array($name_int)) {
                if (is_array($str))
                    $zip_array = $str;
                elseif (is_array($name_int))
                    $zip_array = $name_int;
            }
            if ($zip_array != false) {
                foreach ($zip_array as $name => $string) {
                    $zip->addFromString($name, $string);
                }
            }
            return $zip->close();
        }
        return false;
    }
    
    protected static function zip_dir($name_int, $name_ext) {
        $zip = new ZipArchive();
        if ($zip->open($name_ext, ZIPARCHIVE::CREATE)) {
            if (is_string($name_int)) {
                $zip->addEmptyDir($name_int);
            } elseif (is_array($name_int)) {
                foreach ($name_int as $name) {
                    $zip->addEmptyDir($name);
                }
            }
            return $zip->close();
        }
        return false;
    }
    
    protected static function tar_file($str, $name_int, $name_ext) {
        try {
            $tar_array = false;
            $a = new PharData($name_ext);
            if (is_string($str) && is_string($name_int)) {
                $a->addFromString($name_int, $str);
            } elseif (is_array($str) && is_array($name_int)) {
                if (count($str) == count($name_int))
                    $tar_array = array_combine($name_int, $str);
            } elseif (is_array($str) || is_array($name_int)) {
                if (is_array($str))
                    $tar_array = $str;
                elseif (is_array($name_int))
                    $tar_array = $name_int;
            }
            if ($tar_array != false) {
                foreach ($tar_array as $name => $string) {
                    $a->addFromString($name, $string);
                }
            }
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    
    protected static function tar_dir($name_int, $name_ext) {
        try {
            $a = new PharData($name_ext);
            if (is_string($name_int)) {
                $a->addEmptyDir($name_int);
            } elseif (is_array($name_int)) {
                foreach ($name_int as $name) {
                    $a->addEmptyDir($name);
                }
            }
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    
    public static function extract_tar($tar, $dir, $files = null, $ow = false) {
        try {
            $phar = new PharData($tar);
            $phar->extractTo($dir, $files, $ow);
        }
        catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    
    public static function DownloadFile($file, $del = false, $filename = '') {
        $mime = "application/octet-stream";
        $name = basename($file);
        if (!file_exists($file))
            return false;
        if (is_string($filename) && $filename != '')
            $name = $filename;
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mime = $finfo->file($file);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($file);
        }
        ob_start();
        header("Expires: 0");
        header("Pragma: public");
        header("Connection: Keep-Alive");
        header("Cache-Control: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Description: File Transfer");
        header("Content-Type: $mime");
        header("Content-Disposition: attachment; filename=\"" . $name . "\"");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . filesize($file));
        ob_end_flush();
        readfile($file);
        if ($del === true)
            unlink($file);
        exit();
        
    }
    
}

class SQL_Backup extends FILES {
    
    const version = "1.1.4 beta";
    const site = "https://github.com/Chak10/Backup-SQL-By-Chak10.git";
    
    public $table_name;
    public $folder;
    public $fname;
    public $qlimit;
    public $archive;
    public $ext;
    public $phpmyadmin;
    public $save;
    public $header_name;
    public $del_csv;
    public $enc_csv;
    public $sql_unique;
    public $down;
    public $json_options;
    public $res = false;
    
    
    /**
     * @var object $con Database connection already opened.
     * @var string | array $table_name Database tables.
     * @var string | array $ext File estensions output.
     * @var string $fname Filename output.
     * @var string $folder Folder output.
     * @var int $query_limit Query interval insert.
     * @var bool | string $archive Output compressed (zip) or archived (tar) or in a dir (false).
     * @var bool $phpmyadmin It creates files that can be imported directly with phpmyadmin.
     * @var bool $save Output saved in a file or outputed as string.
     * @var bool $sql_unique Output (sql) unified in a unique file/query.
     * @var bool $down Output automatic downloaded.
     * @var bool $header_name If is set true and the output is csv add the fields name at first row.
     * @var string $del_csv The optional delimiter parameter sets the field delimiter (one character only) (used in csv output).
     * @var string $enc_csv The optional enclosure parameter sets the field enclosure (one character only) (used in csv output).
     * @var int $json_options JSON_ENCODE OPTION.
     **/
    
    function __construct($con = null, $table_name = null, $ext = null, $fname = null, $folder = null, $query_limit = null, $archive = null, $phpmyadmin = null, $save = null, $sql_unique = null, $down = null, $header_name = null, $del_csv = null, $enc_csv = null, $json_options = null) {
        parent::__construct();
        $this->con = $con;
        $this->table_name = $table_name;
        $this->folder = $folder;
        $this->fname = $fname;
        $this->qlimit = $query_limit;
        $this->archive = $archive;
        $this->ext = $ext;
        $this->phpmyadmin = $phpmyadmin;
        $this->save = $save;
        $this->down = $down;
        $this->sql_unique = $sql_unique;
        $this->header_name = $header_name;
        $this->del_csv = $del_csv;
        $this->enc_csv = $enc_csv;
        $this->json_options = $json_options;
    }
    
    /**
     * @var bool $debug Used for debug
     * @return Bool | Array
     **/
    
    public function execute($debug = false) {
        $res = array();
        $res_x = true;
        $this->checking();
        if ($this->check($this->con, "con") == false)
            return $debug === true ? $this->debug() : false;
        if ($this->check($this->folder, "folder") == false)
            return $debug === true ? $this->debug() : false;
        $tables = $this->check($this->table_name, "tables");
        $name_temp = "temp_backup" . md5(microtime(true) . mt_rand());
        foreach ($this->ext as $type_ext) {
            $type_ext = trim($type_ext);
            if ($this->save == false || $this->down == true) {
                $res_x = false;
                $create = $this->create($type_ext, $tables);
                if ($this->last_err_db == null) {
                    if ($this->save == false)
                        $res[$type_ext] = $create;
                    if ($this->down == true) {
                        $n = 1;
                        foreach ($create as $table => $tb) {
                            $this->_save($tb, "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "]." . $type_ext, $name_temp, $type_ext, 'zip');
                            ++$n;
                        }
                    }
                }
            } else {
                if (!$this->save($type_ext, $tables, $this->folder . '/' . $this->fname))
                    $res_x = false;
            }
        }
        $this->res = $res = empty($res) ? $res_x : $res;
        if ($this->down == true)
            return $this->DownloadFile($name_temp . '.zip', true, "Backup_MYSQL(" . date("Y-m-d-H-i-s") . ").zip");
        if ($debug === true)
            return $this->debug();
        $this->clean_var();
        return $res;
    }
    
    private function checking() {
        $this->check($this->ext, "ext");
        $this->check($this->save, "save");
        $this->check($this->fname, "filename");
        $this->check($this->archive, "archive");
        $this->check($this->phpmyadmin, "one_file");
        $this->check($this->sql_unique, "unique_sql");
    }
    
    protected function create($ext, $tables) {
        $res = array();
        foreach ($tables as $table) {
            switch ($ext) {
                case "sql":
                    $res[$table] = $this->query_sql($table, $this->qlimit);
                    break;
                case "csv":
                    $res[$table] = $this->query_csv($table, $this->header_name);
                    break;
                case "json":
                    $res[$table] = $this->query_json($table, $this->json_options);
                    break;
            }
        }
        return $res;
    }
    
    protected function save($ext, $tables, $filename) {
        $tb = '';
        $n = $e = 1;
        $res = array();
        $comp = strtolower($this->archive);
        foreach ($tables as $table) {
            switch ($ext) {
                case "sql":
                    if ($this->sql_unique == true) {
                        $tb .= $this->query_sql($table, $this->qlimit);
                    } else {
                        $tb = $this->query_sql($table, $this->qlimit);
                        $this->name_file[] = $name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].sql";
                        if ($this->phpmyadmin == false) {
                            if ($this->_save($tb, $name, $filename, 'sql', $comp) == false)
                                ++$e;
                        } else {
                            if ($this->_save($tb, $name, $filename . '-' . $table . '-' . hash('crc32b', microtime(true) . mt_rand()) . ".sql", '', "zip") == false)
                                ++$e;
                        }
                    }
                    break;
                case "csv":
                    $tb = $this->query_csv($table, $this->header_name);
                    $this->name_file[] = $name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].csv";
                    if ($this->phpmyadmin == false) {
                        if ($this->_save($tb, $name, $filename, 'csv', $comp) == false)
                            ++$e;
                    } else {
                        if ($this->_save($tb, $name, $filename . '-' . $table . '-' . hash('crc32b', microtime(true) . mt_rand()) . ".csv", '', "zip") == false)
                            ++$e;
                    }
                    break;
                case "json":
                    if ($this->phpmyadmin == false) {
                        $tb = $this->query_json($table, $this->json_options);
                        $this->name_file[] = $name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].json";
                        if ($this->_save($tb, $name, $filename, 'json', $comp) == false)
                            ++$e;
                    }
                    break;
            }
            ++$n;
        }
        if ($this->sql_unique == true) {
            $this->name_file[] = $name = "TB" . $n . "_Name[ALLTABLES]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].sql";
            if ($this->_save($tb, $name, $filename, 'sql', $comp) == false)
                ++$e;
        }
        if ($e == 1)
            return true;
        return false;
    }
    
    private function _save($str, $name_int, $name_ext, $dir_int = '', $comp) {
        do {
            switch ($comp) {
                case "tar":
                    if (in_array('tar', $this->ext_c_supported)) {
                        if ($dir_int != '') {
                            self::tar_dir($dir_int, $name_ext . '.tar');
                            $name_int = $dir_int . '/' . $name_int;
                        }
                        $res = self::tar_file($str, $name_int, $name_ext . '.tar');
                        $this->path_file[] = realpath($name_ext . '.tar');
                        return $res;
                    }
                    $comp = "zip";
                    break;
                case "zip":
                    if (in_array('zip', $this->ext_c_supported)) {
                        if ($dir_int != '') {
                            self::zip_dir($dir_int, $name_ext . '.zip');
                            $name_int = $dir_int . '/' . $name_int;
                        }
                        $res = self::zip_file($str, $name_int, $name_ext . '.zip');
                        $this->path_file[] = realpath($name_ext . '.zip');
                        return $res;
                    }
                    $comp = false;
                    break;
                default:
                    $res = self::std_file($str, $name_int, $name_ext, $dir_int);
                    $this->path_file[] = realpath($name_ext);
                    return $res;
                    break;
            }
        } while (true);
    }
    
    protected function query_table($con, $type) {
        $tables = array();
        if ($type == "mysqli") {
            $result = $con->query("SHOW TABLES");
            while ($table_row = $result->fetch_row())
                $tables[] = $table_row[0];
            return $tables;
        }
        if ($type == "PDO") {
            $result = self::query_pdo($con, "SHOW TABLES");
            while ($table_row = $result->fetch(PDO::FETCH_NUM))
                $tables[] = $table_row[0];
            return $tables;
        }
        return false;
    }
    
    protected function query_sql($table, $limit) {
        if ($this->type == "mysqli")
            return $this->sql_mysqli($this->con, $table, $limit);
        if ($this->type == "PDO")
            return $this->sql_pdo($this->con, $table, $limit);
        return false;
    }
    
    protected function query_csv($table, $header_name) {
        if ($this->type == "mysqli")
            return $this->csv_mysqli($this->con, $table, $this->del_csv, $this->enc_csv, $header_name);
        if ($this->type == "PDO")
            return $this->csv_pdo($this->con, $table, $this->del_csv, $this->enc_csv, $header_name);
        return false;
    }
    
    protected function query_json($table, $options) {
        if ($this->type == "mysqli")
            return $this->json_mysqli($this->con, $table, $options);
        if ($this->type == "PDO")
            return $this->json_pdo($this->con, $table, $options);
        return false;
    }
    
    private function check($in, $t) {
        
        switch ($t) {
            
            case "con":
                if (!is_object($in))
                    return false;
                if (isset($in->host_info)) {
                    if (!$in->set_charset("utf8"))
                        return false;
                    $this->type = "mysqli";
                    return true;
                }
                if (!method_exists($in, 'getAttribute'))
                    return false;
                if ($in->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== null) {
                    $this->type = "PDO";
                    return true;
                }
                return false;
                break;
            
            case "tables":
                if (is_array($in))
                    return $this->table_name = $in;
                if (is_string($in) && $in != "*" && $in != "")
                    return $this->table_name = explode(",", $in);
                return $this->table_name = $this->query_table($this->con, $this->type);
                break;
            
            case "filename":
                if (!is_string($in))
                    $in = "Backup_MYSQL";
                elseif (pathinfo($in, PATHINFO_EXTENSION) != '')
                    $in = pathinfo($in, PATHINFO_FILENAME);
                return $this->fname = preg_replace("/[^\w-]/", "", $in);
                break;
            
            case "folder":
                $res = $res_2 = true;
                if (!is_string($in))
                    $in = "backup/database/" . date("Y-m-d");
                else
                    $in = rtrim(str_replace("\\", "/", $in), '/') . '/' . date("Y-m-d");
                if (!is_dir($in))
                    $res = mkdir($in, 0764, true);
                if (!is_writable($in))
                    $res_2 = chmod($in, 0764);
                $this->folder = $in;
                return $res && $res_2;
                break;
            
            case "ext":
                if (is_string($in))
                    $in = explode(',', strtolower($in));
                elseif (is_array($in))
                    $in = array_map('strtolower', $in);
                else
                    $in = array();
                if (in_array("sql", $in) || in_array("csv", $in) || in_array("json", $in))
                    return $this->ext = $in;
                elseif (in_array("all", $in))
                    return $this->ext = array(
                        "sql",
                        "csv",
                        "json"
                    );
                else
                    return $this->ext = array(
                        "sql"
                    );
                break;
            
            case "archive":
                if ($in === "tar" || $in === "zip" || $in === false)
                    return $this->archive = $in;
                return $this->archive = "zip";
                break;
            
            case "save":
                if ($in === true || $in === false)
                    return $this->save = $in;
                return $this->save = true;
                break;
            
            case "one_file":
                if ($in === true || $in === false)
                    return $this->phpmyadmin = $in;
                return $this->phpmyadmin = false;
                break;
            
            case "unique_sql":
                if ($in === true || $in === false)
                    return $this->sql_unique = $in;
                return $this->sql_unique = false;
                break;
                
        }
        
    }
    
    private function clean_var() {
        unset($this->con);
        unset($this->res);
        unset($this->type);
        unset($this->ext_c_supported);
        unset($this->table_name);
        unset($this->fname);
        unset($this->folder);
        unset($this->qlimit);
        unset($this->archive);
        unset($this->header_name);
        unset($this->del_csv);
        unset($this->enc_csv);
        unset($this->ext);
        unset($this->phpmyadmin);
        unset($this->save);
        unset($this->sql_unique);
        unset($this->json_options);
        unset($this->err_c);
        unset($this->name_file);
        unset($this->path_file);
        unset($this->last_err_db);
        unset($this->down);
    }
    
    private function debug() {
        $return = get_object_vars($this);
        $this->clean_var();
        return $return;
    }
    
}


?>
