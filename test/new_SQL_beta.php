<?php
	
	class DB {
		
		public $con;
		public $type;
		public $err_c;
		
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
	
	class FORMAT extends DB {
		
		
		protected static function sql_mysqli($con, $table, $limit) {
			
			if (!is_int($limit))
            $limit = 400;
			
			$fields = '';
			
			/* DB REQUESTS */
			
			$info = $con->query("SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch_assoc();
			
			$res = $con->query("SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch_row();
			
			$result = $con->query("SELECT * FROM `" . $table . "`");
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
						$row[$j] = addslashes($row[$j]);
						if (isset($row[$j]))
                        $return .= "'" . $row[$j] . "'";
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
			
			$return .= ';' . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
			
			return $return;
		}
		
		protected static function sql_pdo($con, $table, $limit, $db) {
			
			if (!is_int($limit))
            $limit = 400;
			
			$fields = '';
			
			/* DB REQUESTS */
			
			$info = DB::query_pdo($con, "SHOW TABLE STATUS WHERE NAME LIKE '$table'");
			$info = $info->fetch(PDO::FETCH_ASSOC);
			
			$res = DB::query_pdo($con, "SHOW CREATE TABLE `" . $table . "`");
			$table_init = $res->fetch(PDO::FETCH_NUM);
			
			$charset = DB::query_pdo($con, "SELECT @@character_set_database;");
			$charset = $charset->fetch(PDO::FETCH_NUM);
			
			$result = DB::query_pdo($con, "SELECT * FROM `" . $table . "`");
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
						$row[$j] = addslashes($row[$j]);
						if (isset($row[$j]))
                        $return .= "'" . $row[$j] . "'";
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
			
			$return .= ';' . PHP_EOL . PHP_EOL . PHP_EOL . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . PHP_EOL . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . PHP_EOL . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . PHP_EOL;
			return $return;
			
		}
		
		protected static function csv_mysqli($con, $table, $del, $enc, $header_name) {
			
			$return = $fields = '';
			$del = trim($del);
			
			/* DB REQUESTS */
			
			$result = $con->query("SELECT * FROM `" . $table . "`");
			$num_fields = $result->field_count;
			
			/* FIELDS */
			
			while ($field_info = $result->fetch_field()) {
				$fields .= $enc . $field_info->name . $enc . $del;
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			if ($header_name === true)
            $return = $fields . PHP_EOL;
			
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
		
		protected static function csv_pdo($con, $table, $del, $enc, $header_name, $db) {
			
			$return = $fields = '';
			$del = trim($del);
			
			/* DB REQUESTS */
			
			$result = DB::query_pdo($con, "SELECT * FROM `" . $table . "`");
			$num_fields = $result->columnCount();
			
			/* FIELDS */
			
			for ($ind = 0; $ind < $num_fields; ++$ind) {
				$name_c = $result->getColumnMeta($ind);
				$fields .= $enc . $name_c['name'] . $enc . $del;
			}
			$fields = substr($fields, 0, -1);
			
			/* HEADER */
			
			if ($header_name === true)
            $return = $fields . PHP_EOL;
			
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
		
		protected static function json_mysqli($con, $table, $options) {
			
			$return = array();
			
			/* DB REQUESTS */
			
			$result = $con->query("SELECT * FROM `" . $table . "`");
			
			/* TABLE DATA */
			
			while ($rows = $result->fetch_assoc()) {
				if (extension_loaded("xml"))
                $return[] = array_map('utf8_encode', $rows);
				else
                $return[] = $rows;
			}
			
			if (is_int($options) && $options != 0)
            return json_encode($return, $options);
			return json_encode($return);
		}
		
		protected static function json_pdo($con, $table, $options) {
			
			$return = array();
			
			/* DB REQUESTS */
			
			$result = DB::query_pdo($con, "SELECT * FROM `" . $table . "`");
			
			/* TABLE DATA */
			
			while ($rows = $result->fetch(PDO::FETCH_ASSOC)) {
				if (extension_loaded("xml"))
                $return[] = array_map('utf8_encode', $rows);
				else
                $return[] = $rows;
			}
			
			if (is_int($options) && $options != 0)
            return json_encode($return, $options);
			return json_encode($return);
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
		
		protected static function std_file($str, $name_int, $name_ext = false) {
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
		
		protected static function tar_compress($name_tar, $type = "gz", $index = 9) {
			$temp = dirname($name_tar) . DIRECTORY_SEPARATOR . "temp" . microtime(true) . ".tar";
			$temp_dir = dirname($name_tar) . DIRECTORY_SEPARATOR . "temp";
			switch ($type) {
				case 'gz':
                $name = $name_tar . '.gz';
                if (!file_exists($name))
				return file_put_contents($name, gzencode(file_get_contents($name_tar), $index, FORCE_GZIP)) && unlink($name_tar);
                self::gz_decompress($name, $temp);
                self::overvrite_comp($temp, $temp_dir, $name_tar);
                return file_put_contents($name, gzencode(file_get_contents($name_tar), $index, FORCE_GZIP)) && unlink($name_tar);
                break;
				case 'bz2':
                $name = $name_tar . '.bz2';
                if (!file_exists($name))
				return file_put_contents($name_tar . '.bz2', bzcompress(file_get_contents($name_tar), $index)) && unlink($name_tar);
                self::bz2_decompress($name, $temp);
                self::overvrite_comp($temp, $temp_dir, $name_tar);
                return file_put_contents($name_tar . '.bz2', bzcompress(file_get_contents($name_tar), $index)) && unlink($name_tar);
                break;
				case 'deflate':
                $name = $name_tar . '.gz';
                if (!file_exists($name))
				return file_put_contents($name_tar . '.gz', gzencode(file_get_contents($name_tar), $index, FORCE_DEFLATE)) && unlink($name_tar);
                self::gz_decompress($name, $temp);
                self::overvrite_comp($temp, $temp_dir, $name_tar);
                return file_put_contents($name_tar . '.gz', gzencode(file_get_contents($name_tar), $index, FORCE_DEFLATE)) && unlink($name_tar);
                break;
			}
			return false;
		}
		
		protected static function overvrite_comp($temp, $temp_dir, $name_tar) {
			self::extract_tar($temp, $temp_dir, true) && unlink($temp);
			self::extract_tar($name_tar, $temp_dir, true) && unlink($name_tar);
			self::create_tar($name_tar, $temp_dir);
			self::delete_temp($temp_dir);
		}
		
		protected static function gz_size($file_gz) {
			$file = fopen($file_gz, "rb");
			fseek($file, -4, SEEK_END);
			$buf = fread($file, 4);
			$size = unpack("V", $buf);
			$size = end($size);
			fclose($file);
			return $size;
		}
		
		protected static function gz_decompress($name, $temp) {
			$fh = gzopen($name, "r");
			$contents = gzread($fh, self::gz_size($name));
			gzclose($fh);
			return file_put_contents($temp, $contents);
		}
		
		protected static function bz2_decompress($name, $temp) {
			$decomp_file = '';
			$fh = bzopen($name, 'r');
			do {
				$decomp_file .= $buffer = bzread($fh, 4096);
				if ($buffer === FALSE)
                $sp = true;
				if (bzerror($fh) !== 0)
                $sp = true;
				$sp = feof($fh);
			} while (!$sp);
			bzclose($fh);
			file_put_contents($temp, $decomp_file);
		}
		
		protected static function extract_tar($tar, $dir, $ow = false) {
			try {
				$phar = new PharData($tar);
				$phar->extractTo($dir, null, $ow);
			}
			catch (Exception $e) {
				return $e->getMessage();
			}
			return true;
		}
		
		protected static function create_tar($tar, $dir) {
			try {
				$phar = new PharData($tar);
				$phar->buildFromDirectory($dir);
			}
			catch (Exception $e) {
				return $e->getMessage();
			}
			return true;
		}
		
		protected static function delete_temp($dir) {
			$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
			$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file) {
				if ($file->isDir()) {
					rmdir($file->getRealPath());
					} else {
					unlink($file->getRealPath());
				}
			}
			rmdir($dir);
		}
		
	}
	
	class SQL_Backup extends FILES {
		
		const version = "1.0.8 beta";
		const site = "https://github.com/Chak10/Backup-SQL-By-Chak10.git";
		
		public $table_name;
		public $folder;
		public $qlimit;
		public $compress;
		public $ext;
		public $alltable_in_file;
		public $save;
		public $header_name;
		public $del_csv;
		public $enc_csv;
		public $sql_unique;
		public $json_options;
		
		public $res;
		
		
		function __construct($con = null, $table_name = null, $ext = null, $fname = null, $folder = null, $query_limit = null, $compress = null, $alltable_in_file = null, $save = null, $sql_unique = null) {
			parent::__construct();
			$this->con = $con;
			$this->table_name = $table_name;
			$this->folder = $folder;
			$this->fname = $fname;
			$this->qlimit = $query_limit;
			$this->compress = $compress;
			$this->ext = $ext;
			$this->alltable_in_file = $alltable_in_file;
			$this->save = $save;
			$this->sql_unique = $sql_unique;
		}
		
		public function execute() {
			$res = array();
			$con = $this->con;
			if ($this->check($con, "con") == false)
            return false;
			if ($this->check($this->folder, "folder") == false)
            return false;
			if ($this->check($this->table_name, "table") !== false) {
				$tables = array();
				$result = $con->query("SHOW TABLES");
				while ($table_row = $result->fetch_row()) {
					$this->table_name[] = $tables[] = $table_row[0];
				}
				} else {
				$tables = $this->table_name;
			}
			$this->check($this->ext, "ext");
			$this->check($this->save, "save");
			$this->check($this->fname, "filename");
			$this->check($this->compress, "compress");
			$this->check($this->alltable_in_file, "one_file");
			$this->check($this->sql_unique, "unique_sql");
			$save = $this->save;
			foreach ($this->ext as $type_ext) {
				$type_ext = trim($type_ext);
				if ($save == false)
                $res[$type_ext] = $this->create($type_ext, $tables);
				else
                $res = $this->save($type_ext, $tables, $this->folder . DIRECTORY_SEPARATOR . $this->fname);
			}
			$this->clean_var();
			return $this->res = $res;
		}
		
		protected function create($type, $tables) {
			$res = array();
			foreach ($tables as $table) {
				if ($type == "sql") {
					$option = 400;
					if (is_int($this->qlimit))
                    $option = $this->qlimit;
					$res[$table] = $this->query_sql($table, $option);
					} elseif ($type == "csv") {
					$option = true;
					if ($this->header_name === true || $this->header_name === false)
                    $option = $this->header_name;
					$res[$table] = $this->query_csv($table, $option);
					} elseif ($type == "json") {
					$option = 0;
					if (is_int($this->json_options))
                    $option = $this->json_options;
					$res[$table] = $this->query_json($table, $option);
				}
			}
			return $res;
		}
		
		protected function save($ext, $tables, $filename) {
			$tb = '';
			$n = $e = 1;
			$res = array();
			foreach ($tables as $table) {
				if ($ext == "sql") {
					$option = 400;
					if (is_int($this->qlimit))
                    $option = $this->qlimit;
					if ($this->sql_unique == true) {
						$tb .= $this->query_sql($table, $option);
						} else {
						$tb = $this->query_sql($table, $option);
						$name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].sql";
						if ($this->_save($tb, $name, $filename, 'sql') == false)
                        ++$e;
					}
					} elseif ($ext == "csv") {
					$option = true;
					if ($this->header_name === true || $this->header_name === false)
                    $option = $this->header_name;
					$tb = $this->query_csv($table, $option);
					$name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].csv";
					if ($this->_save($tb, $name, $filename, 'csv') == false)
                    ++$e;
					} elseif ($ext == "json") {
					$option = 0;
					if (is_int($this->json_options))
                    $option = $this->json_options;
					$tb = $this->query_json($table, $option);
					$name = "TB" . $n . "_Name[" . $table . "]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].json";
					if ($this->_save($tb, $name, $filename, 'json') == false)
                    ++$e;
				}
				++$n;
			}
			if ($this->sql_unique == true) {
				$name = "TB" . $n . "_Name[ALLTABLES]_Date[" . date("d-m-Y-H-i-s") . "]_Crc32b[" . hash("crc32b", $tb) . "].sql";
				if ($this->_save($tb, $name, $filename, 'sql') == false)
                    ++$e;
			}
			if ($e == 1){
				if (in_array('tar', $this->ext_c_supported) && $this->compress == "tar") $this->tar_compress($filename . '.tar', "gz", 9);
				return true;
			}
			return false;
		}
		
		private function _save($str, $name_int, $name_ext, $dir_int = '') {
			if ($this->ext_c_supported == null) {
				if ($dir_int != '') {
					if (!is_dir($dir_int))
                    mkdir($dir_int, 0764, true);
					$name_ext = $name_ext . DIRECTORY_SEPARATOR . $dir_int;
				}
				return $this->std_file($str, $name_int, $name_ext);
			}
			if (in_array('tar', $this->ext_c_supported) && $this->compress != "zip") {
				if ($dir_int != '') {
					$this->tar_dir($dir_int, $name_ext . '.tar');
					$name_int = $dir_int . '/' . $name_int;
				}
				return $this->tar_file($str, $name_int, $name_ext . '.tar');
			}
			if (!in_array('tar', $this->ext_c_supported) && $this->compress == "tar") 
				$this->compress = "zip";        
			if (in_array('zip', $this->ext_c_supported) && $this->compress == "zip") {
				if ($dir_int != '') {
					$this->zip_dir($dir_int, $name_ext . '.zip');
					$name_int = $dir_int . '/' . $name_int;
				}
				return $this->zip_file($str, $name_int, $name_ext . '.zip');
			}			
		}
		
		protected function query_sql($table, $limit) {
			$this->type_con();
			if ($this->type === null)
            return false;
			elseif ($this->type == "mysqli")
            return FORMAT::sql_mysqli($this->con, $table, $limit);
			elseif ($this->type == "PDO")
            return FORMAT::sql_pdo($this->con, $table, $limit, $this->db);
		}
		
		protected function query_csv($table, $header_name) {
			if ($header_name !== true && $header_name !== false)
            $header_name = true;
			if ($this->del_csv != null)
            $del = $this->del_csv;
			else
            $del = ',';
			if ($this->enc_csv != null)
            $enc = $this->enc_csv;
			else
            $enc = '';
			$this->type_con();
			if (!isset($this->db))
            $db = "Unknown";
			else
            $db = $this->db;
			if ($this->type === null)
            return false;
			elseif ($this->type == "mysqli")
            return FORMAT::csv_mysqli($this->con, $table, $del, $enc, $header_name);
			elseif ($this->type == "PDO")
            return FORMAT::csv_pdo($this->con, $table, $del, $enc, $header_name, $db);
			
		}
		
		protected function query_json($table, $options) {
			$this->type_con();
			if ($this->type === null)
            return false;
			elseif ($this->type == "mysqli")
            return FORMAT::json_mysqli($this->con, $table, $options);
			elseif ($this->type == "PDO")
            return FORMAT::json_pdo($this->con, $table, $options);
		}
		
		private function type_con() {
			if ($this->type === null) {
				$con = $this->con;
				if (is_object($con) && isset($con->host_info))
                return $this->type = "mysqli";
				elseif (is_object($con) && $con->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== null)
                return $this->type = "PDO";
				else
                return $this->type = null;
			}
		}
		
		private function check($in, $t) {
			switch ($t) {
				case "con":
                if (is_object($in) && isset($in->host_info))
				return true;
                if (is_object($in) && $in->getAttribute(PDO::ATTR_CONNECTION_STATUS) !== null)
				return true;
                return false;
                break;
				case "tables":
                if (is_array($in))
				return true;
                if (is_string($in) && $in != "*" && $in != "")
				return $this->table_name = explode(",", $in);
                return false;
                break;
				case "filename":
                if (!is_string($in))
				$in = "Backup_MYSQL";
                elseif (pathinfo($in, PATHINFO_EXTENSION) != '')
				$in = pathinfo($in, PATHINFO_FILENAME);
                $this->fname = $in;
                break;
				case "folder":
                $res = $res_2 = true;
                if (!is_string($in))
				$in = "backup/database";
                if (!is_dir($in))
				$res = mkdir($in, 0764, true);
                if (!is_writable($in))
				$res_2 = chmod($in, 0764);
                $this->folder = $in;
                return $res && $res_2;
                break;
				case "ext":
                if (is_string($in)) {
                    $in = explode(',', strtolower($in));
					} elseif (is_array($in)) {
                    $in = array_map('strtolower', $in);
					} else {
                    $in = array();
				}
                if (in_array("sql", $in) || in_array("csv", $in) || in_array("json", $in)) {
                    $this->ext = $in;
					} elseif (in_array("all", $in)) {
                    $this->ext = array(
					"sql",
					"csv",
					"json"
                    );
					} else {
                    $this->ext = array(
					"sql"
                    );
				}
                break;
				case "compress":
                if ($in === "tar" || $in === "zip" || $in === false)
				$this->compress = $in;
                else
				$this->compress = false;
                break;
				case "save":
                if ($in === true || $in === false)
				$this->save = $in;
                else
				$this->save = true;
                break;
				case "one_file":
                if ($in === true || $in === false)
				$this->alltable_in_file = $in;
                else
				$this->alltable_in_file = false;
                break;
				case "unique_sql":
                if ($in === true || $in === false)
				$this->sql_unique = $in;
                else
				$this->sql_unique = false;
                break;
			}
			
		}
		
		private function clean_var() {
			unset($this->con);
			unset($this->type);
			unset($this->ext_c_supported);
			unset($this->table_name);
			unset($this->fname);
			unset($this->folder);
			unset($this->qlimit);
			unset($this->compress);
			unset($this->header_name);
			unset($this->del_csv);
			unset($this->enc_csv);
			unset($this->ext);
			unset($this->alltable_in_file);
			unset($this->save);
			unset($this->sql_unique);
			unset($this->json_options);
			unset($this->err_c);
		}
		
	}
	
	
?>