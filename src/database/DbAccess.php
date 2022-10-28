<?php

namespace Ssentezo\Database;

use Exception;
use mysqli;
use Ssentezo\Database\Connection;

class DbAccess
{

    private $conn;
    private $mysqlKeyWords;
    public $db_name = "";
    private $host;
    private $pass;
    private $user;
    public $query;

    /**
     * @param string $database The database to use 
     * @throws Exception When the connection to the database fails
     */
    public function __construct($database = "")
    {
        if (strlen($database) < 1 && isset($_SESSION['company_db']) && !empty($_SESSION)) {
            $database = $_SESSION['company_db'];
        }
        $this->db_name = $database;

        $this->host = "127.0.0.1";
        $this->user = "root";
        //$this->pass = "!Log19tan88";
        $this->pass = "";
        // $this->pass = "";
        // Create connection

        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);

        // Check connection
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        $this->mysqlKeyWords = ['CURRENT_TIMESTAMP'];
    }
    public function get_db()
    {
        return $this->db_name;
    }
    public function getConnection()
    {
        return $this->conn;
    }
    /**
     * @param string $table name of the table
     *@param Array $set array(key=value,key=>vale)
     */
    public function update($table, $set = array(), $where = array())
    {

        $q = "UPDATE $table SET ";
        if ($set) {
            foreach ($set as $key => $value) {
                if (in_array($value, $this->mysqlKeyWords)) {
                    $q .= $key . " =$value,";
                } else {
                    $q .= $key . " ='" . mysqli_real_escape_string($this->conn, $value) . "',";
                }
            }

            $q = substr($q, 0, -1) . " where ";
        }
        foreach ($where as $key1 => $value1) {
            $q .= "$key1 = '$value1' and ";
        }
        $q = substr($q, 0, -4);
        $this->query = $q;
        $query = mysqli_query($this->conn, $q);
        if ($query) {
            return mysqli_affected_rows($this->conn);
            //return $query;
            //return $rh;
        } else {
            echo 'Erorr ' . mysqli_error($this->conn);
            return mysqli_error($this->conn);
        }

        return $query;
    }

    /**
     * @param string $table name of the table
     *@param Array $set array(key=value,key=>vale)
     */
    public function insert($table, $data = array())
    {
        $q = "insert into $table ";
        $values = "(";
        $cols = "(";
        foreach ($data as $key => $value) {
            $cols .= "$key,";
            // $values.="'" . mysqli_real_escape_string($this->conn, $value) . "',";
            if (in_array($value, $this->mysqlKeyWords)) {
                $values .= $value;
            } else {
                $values .= "'" . mysqli_real_escape_string($this->conn, $value) . "',";
            }
            // $values.="'" . $value . "',";
        }

        $cols = substr($cols, 0, -1) . ")";
        $values = substr($values, 0, -1) . ")";
        $q .= $cols . " values $values";
        // echo $q."////</br>";
        $this->query = $q;
        $query = mysqli_query($this->conn, $q);
        if ($query) {
            return mysqli_insert_id($this->conn);
        }
        //return $q;
        return mysqli_error($this->conn);
    }

    public function sql($q)
    {
        $this->query = $q;
        $query = mysqli_query($this->conn, $q);
        if ($query) {
            return mysqli_insert_id($this->conn);
        }
        //return $q;
        return mysqli_error($this->conn);
    }
    public function updateQuery($query)
    {
        $this->query = $query;
        $query = mysqli_query($this->conn, $query);
        if ($query) {
            return    mysqli_affected_rows($this->conn);
        }
        return mysqli_error($this->conn);
    }
    public function select($table, $cols = array(), $where = [])
    {
        $q = "select ";
        if (empty($cols)) {
            $q = "select *";
        } else {
            $strCols = "";
            foreach ($cols as $col) {
                $strCols .= $col . ",";
            }

            $strColsfinal = substr($strCols, 0, -1);
            $q = "select " . $strColsfinal;
        }
        $q .= " from $table";

        if ($where) {
            // $keys=  array_keys($where);
            $wherestr = "";
            $orderbyValue = "";
            $orderby = FALSE;
            foreach ($where as $key => $value) {
                if ($key == "order by") {
                    //echo "True key=$key and ".($key=="order by");
                    $orderbyValue = $value;
                    $orderby = TRUE;
                } elseif ($key == "between") {
                    $wherestr .= " $value and";
                } else {
                    $checkEmail = $this->isValidEmail($value);
                    if ($checkEmail) {
                        $wherestr .= " $key ='$value' and";
                    } else {
                        $explodable = explode('.', $value);

                        //print_r($explodable);
                        if (count($explodable) > 1) { //if column in for l.id=m.id
                            $wherestr .= " $key =$value and";
                        } else {
                            if ($value[0] == "!") { //not equal to expression
                                $wherestr .= " $key !='$value' and";
                            } else {
                                $wherestr .= " $key ='" . mysqli_real_escape_string($this->conn, $value) . "' and";
                            }
                        }
                    }
                }
            }

            $wherestr = substr($wherestr, 0, -3);
            if ($wherestr && strlen($wherestr) > 3) {
                $q .= " where " . $wherestr;
            }

            if ($orderby) {

                $q .= " order by $orderbyValue";
            }
        }

        $this->query = $q;
        $query = mysqli_query($this->conn, $q);
        // echo $q.'</br>';
        $result = array();
        if ($query) {
            while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                $result[] = $row;
            }
            /* if (count($result) == 1) {
              return $result[0];
              } else {
              return $result;
              } */
            return $result;
        } else {
            echo 'Erorr ' . mysqli_error($this->conn);
        }

        return FALSE;
    }

    public function selectQuery($sql)
    {
        $this->query = $sql;
        $query = mysqli_query($this->conn, $sql);
        // echo $q;
        $result = array();
        if ($query) {
            while ($row = mysqli_fetch_array($query)) {
                $result[] = $row;
            }
            /* if (count($result) == 1) {
              return $result[0];
              } else { */
            return $result;
            //}
        } else {
            echo 'Erorr ' . mysqli_error($this->conn);
        }

        return FALSE;
    }

    public function switchDb($database)
    {


        $this->db_name = $database;

        if ($this->dbExists($database)) {
            $this->conn->close();

            $this->connect();
            // Connection::switchDatabase($database);
        } else {
            throw new Exception("Database $database not found ");
        }

        return $this;
    }
    private function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);
    }
    private function isValidEmail($string)
    {
        if (filter_var($string, FILTER_VALIDATE_EMAIL)) {
            //echo "This ($email_a) email address is considered valid.";
            return TRUE;
        }
        return FALSE;
    }

    public function delete($sql)
    {
        $query = mysqli_query($this->conn, $sql);
    }
    private function dbExists($database)
    {
        $query = "SHOW DATABASES LIKE '$database'";
        $result = $this->selectQuery($query);
        if (is_array($result) && count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function clean($input)
    {
        return mysqli_real_escape_string($this->conn, trim($input));
    }
    public function close()
    {
        $this->conn->close();
    }
}
