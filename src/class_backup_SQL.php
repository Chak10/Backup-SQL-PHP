<?php
class SQL_Backup {
    
    private $version = "1.0.5";
    private $site = "https://github.com/Chak10/Backup-SQL-By-Chak10.git";
    
    var $con;
    var $compress;
    var $table_name;
    var $folder;
    var $qlimit;
    var $alltable_in_file;
    var $err;
    var $res;
    var $del_csv;
    var $enc_csv;
    var $save;
    var $sql_unique;
    var $close;
    var $json_pretty;
    var $info_t;
    var $info = array();
    
    const SQL = 13;
    const CSV = 26;
    const JSON = 49;
    
    function __construct($con = null, $table_name = null, $folder = null, $query_limit = null, $compress = null, $ext = null, $alltable_in_file = null, $save = null, $sql_unique = null) {
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
    
    function con($HOST, $USER, $PASSWD, $NAME, $PORT = null, $SOCK = null) {
        $con = new mysqli($HOST, $USER, $PASSWD, $NAME, $PORT != null ? $PORT : ini_get("mysqli.default_port"), $SOCK != null ? $SOCK : ini_get("mysqli.default_socket"));
        $this->con = $con;
    }
    
    function execute() {
        $this->checkcon($this->con);
        $this->checkfolder($this->folder);
        $this->checklimit($this->qlimit);
        $this->checkcompress($this->compress);
        $this->checkext($this->ext);
        $this->checksep($this->alltable_in_file);
        $this->checksave($this->save);
        $this->checksql_unique($this->sql_unique);
        $save = $this->save;
        if ($this->res != null && $this->res == true) {
            $con = $this->con;
            $limit = $this->qlimit;
            is_string($this->table_name) || is_array($this->table_name) ? $table_name = $this->table_name : $table_name = "*";
            if ($table_name == "*") {
                $tables = array();
                $result = $con->query("SHOW TABLES");
                while ($table_row = $result->fetch_row()) {
                    $tables[] = $table_row[0];
                }
            } else {
                $tables = is_array($table_name) ? $table_name : explode(",", $table_name);
            }
            $this->del_csv != null ? $del_c = $this->del_csv : $del_c = ',';
            $this->enc_csv != null ? $enc_c = $this->enc_csv : $enc_c = '"';
            switch ($this->ext) {
                case self::SQL:
                    $save == true ? $this->sql_exec($con, $tables, $limit) : $this->sql = $this->sql($con, $tables, $limit);
                    break;
                case self::CSV:
                    $save == true ? $this->csv_exec($con, $tables, $del_c, $enc_c) : $this->csv = $this->csv($con, $tables, $del_c, $enc_c);
                    break;
                case self::JSON:
                    $save == true ? $this->json_exec($con, $tables) : $this->json = $this->json($con, $tables);
                    break;
                case (self::SQL + self::CSV):
                    $save == true ? $this->sql_exec($con, $tables, $limit) : $this->sql = $this->sql($con, $tables, $limit);
                    $save == true ? $this->csv_exec($con, $tables, $del_c, $enc_c) : $this->csv = $this->csv($con, $tables, $del_c, $enc_c);
                    break;
                case (self::SQL + self::JSON):
                    $save == true ? $this->sql_exec($con, $tables, $limit) : $this->sql = $this->sql($con, $tables, $limit);
                    $save == true ? $this->json_exec($con, $tables) : $this->json = $this->json($con, $tables);
                    break;
                case (self::JSON + self::CSV):
                    $save == true ? $this->json_exec($con, $tables) : $this->json = $this->json($con, $tables);
                    $save == true ? $this->csv_exec($con, $tables, $del_c, $enc_c) : $this->csv = $this->csv($con, $tables, $del_c, $enc_c);
                    break;
                default:
                    $save == true ? $this->sql_exec($con, $tables, $limit) : $this->sql = $this->sql($con, $tables, $limit);
                    $save == true ? $this->json_exec($con, $tables) : $this->json = $this->json($con, $tables);
                    $save == true ? $this->csv_exec($con, $tables, $del_c, $enc_c) : $this->csv = $this->csv($con, $tables, $del_c, $enc_c);
                    break;
            }
        }
        
    }
    
    function __destruct() {
        $con = $this->con;
        $this->checkcon($con);
        $this->res != null && $this->res == true && $this->close === true ? $con->close() : '';
    }
    
    private function json_exec($con, $tables) {
        if (is_array($tables) === false)
            return false;
        if ($this->compress == true) {
            $result = $this->json($con, $tables);
            $res_f = $this->joiner($result);
            foreach ($result as $tab => $res) {
                $this->compress($res, $tab, 'json', $this->alltable_in_file == false ? false : true, $res_f[1]);
            }
        } else {
            $result = $this->json($con, $tables);
            foreach ($result as $tab => $res) {
                $this->nocompress($res, $tab, 'json');
            }
        }
    }
    
    private function csv_exec($con, $tables, $del_c, $enc_c) {
        if (is_array($tables) === false)
            return false;
        if ($this->compress == true) {
            $result = $this->csv($con, $tables, $del_c, $enc_c);
            $res_f = $this->joiner($result);
            foreach ($result as $tab => $res) {
                $this->compress($res, $tab, 'csv', $this->alltable_in_file == false ? false : true, $res_f[1]);
            }
        } else {
            $result = $this->csv($con, $tables, $del_c, $enc_c);
            foreach ($result as $tab => $res) {
                $this->nocompress($res, $tab, 'csv');
            }
        }
    }
    
    private function sql_exec($con, $tables, $limit) {
        if (is_array($tables) === false)
            return false;
        if ($this->compress == true) {
            if ($this->sql_unique == true) {
                $result = $this->sql($con, $tables, $limit);
                $res = $this->joiner($result);
                $this->compress($res[0], $res[1], 'sql', $this->alltable_in_file == false ? false : true, $res[1]);
            } else {
                $result = $this->sql($con, $tables, $limit);
                $res_f = $this->joiner($result);
                foreach ($result as $tab => $res) {
                    $this->compress($res, $tab, 'sql', $this->alltable_in_file == false ? false : true, $res_f[1]);
                }
            }
        } else {
            if ($this->sql_unique == true) {
                $result = $this->sql($con, $tables, $limit);
                $res = $this->joiner($result);
                $this->nocompress($res[0], $res[1], 'sql');
            } else {
                $result = $this->sql($con, $tables, $limit);
                $res_f = $this->joiner($result);
                foreach ($result as $tab => $res) {
                    $this->nocompress($res, $tab, 'sql', $this->alltable_in_file == true ? true : false, $res_f[1]);
                }
            }
        }
    }
    
    private function joiner($result) {
        is_array($result) === false ? $this->res = false && $this->err = -8 : '';
        if (!$this->res)
            return false;
        $str = '';
        $tabnm = array();
        foreach ($result as $tab => $res) {
            $str .= $res;
            !in_array($tab, $tabnm) ? $tabnm[] = $tab : '';
        }
        $tb = implode("-", $tabnm);
        return array(
            $str,
            $tb
        );
    }
    
    private function json($con, $tables) {
        if (is_array($tables) === false)
            return false;
        foreach ($tables as $k => $table) {
            $result = $con->query("SELECT * FROM `" . $table . "`");
            $result == false ? $this->res = false && $this->err = -7 && $this->info["MySQL_Errno"] = $con->errno && $this->info["MySQL_Error"] = $con->error : '';
            if (!$this->res)
                return false;
            $num_fields = $result->field_count;
            $num_rows = $result->num_rows;
            if ($result) {
                $info = $result->fetch_all(MYSQLI_ASSOC);
                $forjson[$table] = json_encode($info, $this->json_pretty === true ? JSON_PRETTY_PRINT : 0);
            }
            $this->info_t === true ? $this->info[$table] = array(
                "R" => $num_rows,
                "C" => $num_fields
            ) : '';
        }
        return $forjson;
    }
    
    private function csv($con, $tables, $del = ',', $enc = '"') {
        if (is_array($tables) === false)
            return false;
        $return = array();
        foreach ($tables as $table) {
            if ($result = $con->query("SELECT * FROM `" . $table . "`")) {
                $x = 0;
                $fields = array();
                while ($field_info = $result->fetch_field()) {
                    $fields[$x] = $field_info->name;
                    ++$x;
                }
                $num_fields = count($fields);
                $x = '';
                foreach ($fields as $k => $field) {
                    if ($k != count($fields) - 1) {
                        $x .= $enc . $field . $enc . $del;
                    } else {
                        $x .= $enc . $field . $enc . PHP_EOL;
                    }
                }
                $num_rows = 0;
                while ($row = $result->fetch_row()) {
                    foreach ($row as $k => $f) {
                        if ($k != count($row) - 1) {
                            $x .= $enc . $f . $enc . $del;
                        } else {
                            $x .= $enc . $f . $enc . PHP_EOL;
                        }
                    }
                    ++$num_rows;
                }
                $this->info_t === true ? $this->info[$table] = array(
                    "R" => $num_rows,
                    "C" => $num_fields
                ) : '';
                $return[$table] = $x;
            } else {
                $this->res = false;
                $this->err = -6;
                $this->info["MySQL_Errno"] = $con->errno;
                $this->info["MySQL_Error"] = $con->error;
                return false;
            }
        }
        return $return;
    }
    
    private function sql($con, $tables, $limit) {
        if (is_array($tables) === false)
            return false;
        $foreturn = array();
        foreach ($tables as $table) {
            $info = $con->query("SHOW TABLE STATUS WHERE NAME LIKE '$table'");
            $info == false ? $this->res = false && $this->err = -5 && $this->info["MySQL_Errno"] = $con->errno && $this->info["MySQL_Error"] = $con->error : '';
            if (!$this->res)
                return false;
			$nl = PHP_EOL;
            $info = $info->fetch_assoc();
			$charset = $con->get_charset();
            $return = "-- Backup SQL By Chak10" . $nl ."-- Version: " . ($this->version) . $nl ."-- Github: " . ($this->site) . $nl . "--" . $nl ."--" . $nl;
            $return .= "-- Server Version: " . ($con->server_info) . $nl. "-- PHP Version: " . (PHP_VERSION) . $nl . "-- Host Info: " . ($con->host_info) . $nl;
            $return .= "-- Date: " . (date('Y-m-d H:i:s')) . $nl . $nl. $nl;
            $return .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";" . $nl. "SET time_zone = \"+00:00\";" . $nl. $nl. $nl. $nl;
            $return .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;" . $nl. "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;" . $nl. "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;" . $nl. "/*!40101 SET NAMES utf8 */;" . $nl. $nl. $nl;
             $return .= "--" . $nl. "-- Charset General: " . ($charset->charset) . $nl."-- Charset Table: " . ($info['Collation']) . $nl."--" . $nl. $nl;
            $return .= "-- ------------------------------------------" . $nl. $nl."--" . $nl;
            $return .= "-- Table Name: `" . ($table) . "`" . $nl;
            $res = $con->query("SHOW CREATE TABLE `" . $table . "`");
            $table_init = $res->fetch_row();
            $result = $con->query("SELECT * FROM `" . $table . "`");
            $num_fields = $result->field_count;
            $num_rows = $result->num_rows;
			$fields = '';
			while ($field_info = $result->fetch_field()){
				$fields .= "`".$field_info->name."`,";
			}
			$fields = substr($fields,0,-1);
            $return .= "-- Database: " . ($db) . $nl. "--" . $nl;
            $this->info_t === true ? $this->info[$table] = array(
                "R" => $num_rows,
                "C" => $num_fields
            ) : '';
			$db = $field_info->db;
            $return .= "-- Columns: $num_fields" . $nl;
            $return .= "-- Rows: $num_rows" . $nl. "--" . $nl.$nl;
            $return .= "DROP TABLE IF EXISTS " . $table . ";" . $nl;
            $return .= $table_init[1] . ";" . $nl.$nl.$nl;
            for ($i = 0, $s = 0; $i < $num_fields; ++$i) {
                while ($row = $result->fetch_row()) {
                    if ($s == 0) {
						$return .= "INSERT INTO `" . $table . "` ( $fields ) VALUES ".$nl.'(';
					} elseif (is_int($s / $limit) === true) {
						$return .= ';'.$nl."INSERT INTO `" . $table . "` ( $fields ) VALUES ".$nl.'(';
					} else {
						$return.= ','.$nl."(";
					}
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                        if (isset($row[$j])) {
                            $return .= '"' . $row[$j] . '"';
                        } else {
                            $return .= '""';
                        }
                        if ($j < ($num_fields - 1)) $return .= ',';
                    }
                    $return .= ")";
                    ++$s;
                }
            }
            $return .= ';' . $nl.$nl.$nl;
            $return .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;" . $nl;
            $return .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;" . $nl;
            $return .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;" . $nl;            
            $foreturn[$table] = $return;
        }
        return $foreturn;
    }
    
    private function compress($str, $table, $extens, $recursive = false, $tab_name = null) {
        if (!$this->res)
            return false;
        !is_dir($this->folder . "/" . $extens) ? $res = mkdir($this->folder . "/" . $extens, 0764, true) : '';
        !is_writable($this->folder . "/" . $extens) ? $res = chmod($this->folder . "/" . $extens, 0764) : '';
        $zip = new ZipArchive;
        if ($recursive) {
            if ($tab_name == null) {
                $this->res = false;
                $this->err = -11;
                return false;
            }
            $name_zip = $this->folder . "/" . $extens . '/' . $tab_name . '_' . md5($tab_name) . '.' . $extens . '.zip';
            $res = $zip->open($name_zip, ZIPARCHIVE::CREATE);
            if (!file_exists($name_zip)) {
                $comment = "Created with Backup SQL By Chak10" . PHP_EOL;
                $comment .= "Version: " . ($this->version) . PHP_EOL;
                $comment .= "Github: " . ($this->site) . PHP_EOL;
            } else {
                $comment = $zip->getArchiveComment() . PHP_EOL;
            }
        } else {
            $res = $zip->open($this->folder . "/" . $extens . '/' . $table . '_' . time() . '-' . md5($table . microtime(true)) . '.' . $extens . '.zip', ZipArchive::CREATE);
            $comment = "Created with Backup SQL By Chak10" . PHP_EOL;
            $comment .= "Version: " . ($this->version) . PHP_EOL;
            $comment .= "Github: " . ($this->site) . PHP_EOL;
        }
        if ($res === TRUE) {
            $zip->addFromString($table . '-' . date('Y-m-d_H:i:s') . '-' . md5($table . microtime(true)) . '.' . $extens, $str);
            $comment .= "Table: " . $table . " - CRC32b: " . hash('crc32b', $str) . ' - Date: ' . date('Y-m-d H:i:s');
            $zip->setArchiveComment($comment);
            $zip->close();
            $this->res = true;
        } else {
            $this->res = false;
        }
    }
    
    private function nocompress($res, $table, $extens, $new_dir = false, $name_new = null) {
        if (!$this->res)
            return false;
        !is_dir($this->folder . "/" . $extens) ? $res = mkdir($this->folder . "/" . $extens, 0764, true) : '';
        !is_writable($this->folder . "/" . $extens) ? $res = chmod($this->folder . "/" . $extens, 0764) : '';
        if ($new_dir === true) {
            if ($name_new == null) {
                $this->res = false;
                $this->err = -9;
                return false;
            }
            !is_dir($this->folder . "/" . $extens . '/' . $name_new) ? $res = mkdir($this->folder . "/" . $extens . '/' . $name_new, 0764, true) : '';
            $name = $this->folder . "/" . $extens . '/' . $name_new . '/' . $table . '_' . time() . '-' . md5($table . microtime(true)) . '.' . $extens;
            $fpc = @fopen($name, 'w');
            $fpc === false ? $this->res = false : $this->res = true;
            if (!$this->res)
                return false;
            fwrite($fpc, $res) === false ? $this->res = false : $this->res = true;
            fclose($fpc);
        } else {
            $name = $this->folder . "/" . $extens . '/' . $table . '_' . time() . '-' . md5($table . microtime(true)) . '.' . $extens;
            $fpc = @fopen($name, 'w');
            $fpc === false ? $this->res = false && $this->err = -10 : $this->res = true;
            if (!$this->res)
                return false;
            fwrite($fpc, $res) === false ? $this->res = false : $this->res = true;
            fclose($fpc);
        }
        
    }
    
    private function checksql_unique($s) {
        if (!$this->res)
            return false;
        !is_bool($s) && $s == null ? $this->sql_unique = false : '';
        $this->res = true;
    }
    
    private function checksave($s) {
        if (!$this->res)
            return false;
        !is_bool($s) && $s == null ? $this->save = true : '';
        $this->res = true;
    }
    
    private function checksep($s) {
        if (!$this->res)
            return false;
        !is_bool($s) && $s == null ? $this->alltable_in_file = false : '';
        $this->res = true;
    }
    
    private function checkext($ext) {
        if (!$this->res)
            return false;
        if (is_null($ext)) {
            $this->ext = self::SQL;
        } elseif (is_string($ext)) {
            if (strpos($ext, ',') === true) {
                $ext = explode(',', $ext);
                $this->ext = array_sum($ext);
            } else {
                $this->ext = (int) $ext;
            }
        } elseif (is_array($ext)) {
            $this->ext = array_sum($ext);
        } elseif (is_int($ext)) {
            $this->ext = $ext;
        } else {
            $this->err = -12;
            $this->res = false;
        }
    }
    
    private function checkcompress($comp) {
        if (!$this->res)
            return false;
        !is_bool($comp) && $comp == null ? $this->compress = true : '';
        $this->res = true;
    }
    
    private function checklimit($limit) {
        if (!$this->res)
            return false;
        is_int($limit) && $limit >= 1 ? $this->qlimit = $limit : $this->qlimit = 400;
        $this->res = true;
    }
    
    private function checkfolder($folder) {
        if (!$this->res)
            return false;
        !is_string($folder) ? $folder = "backup/database" : '';
        $res = true;
        !is_dir($folder) ? $res = mkdir($folder, 0764, true) : '';
        !is_writable($folder) ? $res = chmod($folder, 0764, true) : '';
        $this->folder = $folder;
        $this->res = $res;
    }
    
    private function checkcon($con) {
        if ($con == null) {
            $this->err = -1;
            $this->res = false;
        } elseif (empty($con)) {
            $this->err = -2;
            $this->res = false;
        } elseif (!is_object($con)) {
            $this->err = -3;
            $this->res = false;
        } elseif ($con->connect_error) {
            $this->log_ce = $con->connect_error;
            $this->err = -4;
            $this->res = false;
        } else {
            $this->con = $con;
            $this->res = true;
        }
    }
    
}

?>