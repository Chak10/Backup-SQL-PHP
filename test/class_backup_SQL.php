<?php
	class SQL_EXT {
		
		function __construct() {}
		
		protected static function sql_mysqli($con,$table,$limit) {
			if ($con === null) return false;
			if (!is_int($limit)) $limit = 400;
			$fields = '';
			$info = $con->query("SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch_assoc();
			$res = $con->query("SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch_row();
			$charset = $con->get_charset();
			$result = $con->query("SELECT * FROM `" . $table . "`");
			$num_fields = $result->field_count;
			$num_rows = $result->num_rows;
			while ($field_info = $result->fetch_field()) {
				$fields .= "`" . $field_info->name . "`,";
				$db = $field_info->db;
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			$return = "-- Backup SQL By Chak10" . PHP_EOL . "-- Version: " . (SQL_Backup::version) . PHP_EOL . "-- Github: " . (SQL_Backup::site) . PHP_EOL . "--" . PHP_EOL . "--" . PHP_EOL . "-- Server Version: " . ($con->server_info) . PHP_EOL . "-- PHP Version: " . (PHP_VERSION) . PHP_EOL . "-- Host Info: " . ($con->host_info) . PHP_EOL .  "-- Extension Used: MYSQLI" . PHP_EOL .  "-- Date: " . (date('Y-m-d H:i:s')) . PHP_EOL . PHP_EOL . PHP_EOL . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . PHP_EOL . "SET time_zone = \"+00:00\";" . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . PHP_EOL . "/*!40101 SET NAMES utf8 */;" . PHP_EOL . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Charset General: " . ($charset->charset) . PHP_EOL . "-- Charset Table: " . ($info['Collation']) . PHP_EOL . "--" . PHP_EOL . PHP_EOL . "-- ------------------------------------------" . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Table Name: `" . ($table) . "`" . PHP_EOL . "-- Database: " . ($db) . PHP_EOL . "--" . PHP_EOL . "-- Columns: $num_fields" . PHP_EOL . "-- Rows: $num_rows" . PHP_EOL . "--" . PHP_EOL . PHP_EOL;
			
			/* TABLE CREATOR */
			
			$return .= "DROP TABLE IF EXISTS " . $table . ";" . PHP_EOL;
			$return .= $table_init[1] . ";" . PHP_EOL . PHP_EOL . PHP_EOL;
			
			/* TABLE DATA */
			
			for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
				while ($row = $result->fetch_row()) {
					if ($s == 0)
					$return .= "INSERT INTO `" . $table . "` ( $fields ) VALUES " . PHP_EOL . '(';
					elseif (is_int($s / $limit) === true)
					$return .= ';' . PHP_EOL . "INSERT INTO `" . $table . "` ( $fields ) VALUES " . PHP_EOL . '(';
					else
					$return .= ',' . PHP_EOL . "(";
					for ($j = 0; $j < $num_fields; $j++) {
						$row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
						if (isset($row[$j]))
						$return .= "'" . $row[$j] . "'";
						else
						$return .= "''";
						
						if ($j < ($num_fields - 1))
						$return .= ',';
					}
					$return .= ")";
					++$s;
				}
			}
			
			/* FOOTER */
			
			$return .= ';' . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
			return $return;
		}
		
		protected static function sql_pdo($con,$table,$limit,$db) {
			if ($con === null) return false;
			if (!is_int($limit)) $limit = 400;
			$fields = '';
			$info = DB::query_pdo($con,"SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch(PDO::FETCH_ASSOC);
			$res = DB::query_pdo($con,"SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch(PDO::FETCH_NUM);
			$charset = DB::query_pdo($con, "SELECT @@character_set_database;");
			$charset = $charset->fetch(PDO::FETCH_NUM);
			$charset = $charset[0];
			$result = DB::query_pdo($con, "SELECT * FROM `" . $table . "`");
			$num_fields = $result->columnCount();
			$num_rows = $result->rowCount();
			for ($ind=0;$ind<$num_fields;++$ind) {
				$name_c = $result->getColumnMeta($ind);
				$fields .= "`" . $name_c['name'] . "`,";				
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			$return = "-- Backup SQL By Chak10" . PHP_EOL . "-- Version: " . (SQL_Backup::version) . PHP_EOL . "-- Github: " . (SQL_Backup::site) . PHP_EOL . "--" . PHP_EOL . "--" . PHP_EOL . "-- Server Version: " . ($con->getAttribute(PDO::ATTR_SERVER_VERSION)) . PHP_EOL . "-- PHP Version: " . (PHP_VERSION) . PHP_EOL . "-- Host Info: " . ($con->getAttribute(PDO::ATTR_CONNECTION_STATUS)) . PHP_EOL .  "-- Extension Used: PDO" . PHP_EOL . "-- Date: " . (date('Y-m-d H:i:s')) . PHP_EOL . PHP_EOL . PHP_EOL . "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . PHP_EOL . "SET time_zone = \"+00:00\";" . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . PHP_EOL . "/*!40101 SET NAMES utf8 */;" . PHP_EOL . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Charset General: " . ($charset) . PHP_EOL . "-- Charset Table: " . ($info['Collation']) . PHP_EOL . "--" . PHP_EOL . PHP_EOL . "-- ------------------------------------------" . PHP_EOL . PHP_EOL . "--" . PHP_EOL . "-- Table Name: `" . ($table) . "`" . PHP_EOL . "-- Database: " . ($db) . PHP_EOL . "--" . PHP_EOL . "-- Columns: $num_fields" . PHP_EOL . "-- Rows: $num_rows" . PHP_EOL . "--" . PHP_EOL . PHP_EOL;
			
			/* TABLE CREATOR */
			
			$return .= "DROP TABLE IF EXISTS " . $table . ";" . PHP_EOL;
			$return .= $table_init[1] . ";" . PHP_EOL . PHP_EOL . PHP_EOL;
			
			/* TABLE DATA */
			
			for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
				while ($row = $result->fetch(PDO::FETCH_NUM)) {
					if ($s == 0)
					$return .= "INSERT INTO `" . $table . "` ( $fields ) VALUES " . PHP_EOL . '(';
					elseif (is_int($s / $limit) === true)
					$return .= ';' . PHP_EOL . "INSERT INTO `" . $table . "` ( $fields ) VALUES " . PHP_EOL . '(';
					else
					$return .= ',' . PHP_EOL . "(";
					for ($j = 0; $j < $num_fields; $j++) {
						$row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
						if (isset($row[$j]))
						$return .= "'" . $row[$j] . "'";
						else
						$return .= "''";
						
						if ($j < ($num_fields - 1))
						$return .= ',';
					}
					$return .= ")";
					++$s;
				}
			}
			
			/* FOOTER */
			
			$return .= ';' . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
			return $return;
		}
		
	}
	
	class CSV_EXT extends SQL_EXT{
		
		function __construct() {}
		
		protected static function csv_mysqli($con,$table,$del, $enc,$header_name) {
			if ($con === null) return false;
			$fields = '';
			$del = trim($del);
			$info = $con->query("SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch_assoc();
			$res = $con->query("SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch_row();
			$charset = $con->get_charset();
			$result = $con->query("SELECT * FROM `" . $table . "`");
			$num_fields = $result->field_count;
			$num_rows = $result->num_rows;
			while ($field_info = $result->fetch_field()) {
				$fields .= $enc . $field_info->name . $enc . $del;
				$db = $field_info->db;
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			if($header_name === true) $return = $fields.PHP_EOL;
			else $return = "";
			
			/* TABLE DATA */
			
			for ($i = 0; $i < $num_fields; ++$i) {
				while ($row = $result->fetch_row()) {
					for ($j = 0; $j < $num_fields; $j++) {
						$row[$j] = addslashes($row[$j]);
						if (isset($row[$j]))
						$return .= $enc . $row[$j] . $enc;
						else
						$return .= $enc.$enc;
						if ($j < ($num_fields - 1))
						$return .= $del;
					}
					$return .= PHP_EOL;
				}
			}
			
			return $return;
		}
		
		protected static function csv_pdo($con,$table,$del, $enc,$header_name,$db) {
			if ($con === null) return false;
			$fields = '';
			$info = DB::query_pdo($con,"SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch(PDO::FETCH_ASSOC);
			$res = DB::query_pdo($con,"SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch(PDO::FETCH_NUM);
			$charset = DB::query_pdo($con, "SELECT @@character_set_database;");
			$charset = $charset->fetch(PDO::FETCH_NUM);
			$charset = $charset[0];
			$result = DB::query_pdo($con, "SELECT * FROM `" . $table . "`");
			$num_fields = $result->columnCount();
			$num_rows = $result->rowCount();
			for ($ind=0;$ind<$num_fields;++$ind) {
				$name_c = $result->getColumnMeta($ind);
				$fields .= $enc . $name_c['name'] . $enc . $del;				
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			if($header_name === true) $return = $fields .PHP_EOL;
			else $return = "";
			
			/* TABLE DATA */
			
			for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
				while ($row = $result->fetch(PDO::FETCH_NUM)) {
					for ($j = 0; $j < $num_fields; $j++) {
						$row[$j] = addslashes($row[$j]);
						if (isset($row[$j]))
						$return .= $enc . $row[$j] . $enc;
						else
						$return .= $enc.$enc;
						if ($j < ($num_fields - 1))
						$return .= $del;
					}
					$return .= PHP_EOL;
				}
			}
			return $return;
		}
	}
	
	class JSON_EXT extends CSV_EXT{
		
		function __construct() {}
		
	}
	
	class DB extends JSON_EXT{
		
		public $con;
		public $type;
		public $err_c;
		public $err;
		public $res;
		
		public function con($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {
			if (!class_exists("mysqli")) {
				$this->type = "mysqli";
				return $this->con_mysqli($HOST, $USER, $PASSWD, $NAME, $PORT, $SOCK);
				} elseif (class_exists("PDO") && in_array('mysql', PDO::getAvailableDrivers())) {
				$this->type = "PDO";
				return $this->con_pdo($HOST, $USER, $PASSWD, $NAME);
				} else {
				return false;
			}
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
				$CON = "mysql:host=$HOST;dbname=$NAME";
				$CON .= $PORT != null ? ";port=" . $PORT : '';
				$CON .= $SOCK != null ? ";unix_socket=" . $SOCK : '';
				$OPT = array(
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				);
				$this->db = $NAME;
				return $this->con = new PDO($CON, $USER, $PASSWD, $OPT);
			}
			catch (PDOException $e) {
				return $this->err_c = 'Connection failed: ' . $e->getMessage();
			}
		}
		
		public static function query_pdo($con, $sql) {
			$res = $con->prepare($sql);
			$res->execute();
			return $res;
		}
		
	}
	
	class SQL_Backup extends DB {
		
		public const version = "2.0.0 beta";
		public const site = "https://github.com/Chak10/Backup-SQL-By-Chak10.git";
		
		public $con;
		public $compress;
		public $table_name;
		public $folder;
		public $qlimit;
		public $alltable_in_file;
		public $err;
		public $res;
		public $del_csv;
		public $enc_csv;
		public $save;
		public $sql_unique;
		public $close;
		public $json_pretty;
		public $info_t;
		public $info = array();
		
		
		public function __construct($con = null, $table_name = null, $folder = null, $query_limit = null, $compress = null, $ext = null, $alltable_in_file = null, $save = null, $sql_unique = null) {
			$this->con = $con;
			$this->table_name = $table_name;
			$this->folder = $folder;
			$this->qlimit = $query_limit;
			$this->compress = $compress;
			$this->ext = $ext;
			$this->alltable_in_file = $alltable_in_file;
			$this->save = $save;
			$this->sql_unique = $sql_unique;
		}
		
		public function query_sql($table,$limit = 400) {
			$this->type_con();
			if ($this->type === null) return false;
			elseif($this->type == "mysqli") return SQL_EXT::sql_mysqli($this->con,$table,$limit);
			elseif ($this->type == "PDO") return SQL_EXT::sql_pdo($this->con,$table,$limit,$this->db);
		}
		
		public function query_csv($table,$header_name = true) {
			if($header_name !== true && $header_name !== false)$header_name = true;
			if($this->del_csv !==null && is_string($this->del_csv)) $del = $this->del_csv;
			else $del = ',';
			if($this->enc_csv !==null && is_string($this->enc_csv)) $enc = $this->enc_csv;
			else $enc = '';
			$this->type_con();
			if(!isset($this->db)) $db="Unknown";
			else $db= $this->db;
			if ($this->type === null) return false;
			elseif($this->type == "mysqli") return CSV_EXT::csv_mysqli($this->con,$table,$del, $enc,$header_name);
			elseif ($this->type == "PDO") return CSV_EXT::csv_pdo($this->con,$table,$del, $enc,$header_name, $db);
			
		}
		private function type_con() {
			if ($this->type === null){
				$con = $this->con;
				if (is_object($con) && isset($con->host_info)) return $this->type = "mysqli";
				elseif(is_object($con) && $con->getAttribute(PDO::ATTR_CONNECTION_STATUS) !==null) return $this->type = "PDO";
				else return $this->type = null;
			}
		}
	}
	
?>
