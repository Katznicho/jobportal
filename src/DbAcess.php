<?php
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['SCRIPT_NAME'] == '/ssentezo/admin/credit_purchase_successful.php' || $_SERVER['SCRIPT_NAME'] == '/ssentezo/admin/credit_purchase_failed.php' || $_SERVER['SCRIPT_NAME'] == '/ssentezo/loans/missed_repayment.php' ||  $_SERVER['SCRIPT_NAME'] == '/ssentezo/loans/move_loans_to_loan_status_table.php') {
} else if ($_SERVER['SCRIPT_NAME'] !=  "/ssentezo/index.php" && $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/login.php" && $_SERVER['SCRIPT_NAME'] !=  "/login.php" &&  $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/Manager_Login.php" &&  $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/staff/setEmail.php" &&  $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/Manager_Login.php" &&  $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/admin-view.php" &&  $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/passwordResetEmail.php" && $_SERVER['SCRIPT_NAME'] !=  "/ssentezo/enter_verification_code.php" && $_SERVER['SCRIPT_NAME'] !=  "/enter_verification_code.php" && ['SCRIPT_NAME'] !=  "/ssentezo/payroll/generate_payslip.php") {
    if (!isset($_SESSION["email"]) || !isset($_SESSION['user_id']) || !isset($_SESSION["role"]) ||  !isset($_SESSION["fullname"]) || !isset($_SESSION["actions"]) || !isset($_SESSION["company"]) || !isset($_SESSION["company_db"])) {
        if ($_SERVER['SCRIPT_NAME'] ==  "/ssentezo/borrowers/groups/add_borrowers_group.php" || $_SERVER['SCRIPT_NAME'] ==  "/ssentezo/borrowers/groups/add_borrowers_group_edit.php" || $_SERVER['SCRIPT_NAME'] ==  "/ssentezo/borrowers/groups/view_borrowers_groups_branch.php" || $_SERVER['SCRIPT_NAME'] ==  "/ssentezo/borrowers/groups/view_group_details.php") {
            header("location: ../../index.php");
            exit;
        } else {
            header("location: ../index.php");
            exit();
        }
    }
}

class DbAcess
{

    private $conn;
    private $mysqlKeyWords;
    public $db_name = "";
    public $query = "";

    public function __construct($database = "")
    {
        if (strlen($database) < 1 && !empty($_SESSION)) {
            $database = $_SESSION['company_db'];
        }

        $this->db_name = $database;

        $servername = "127.0.0.1";
        $username = "root";
        //$password = "!Log19tan88";
        $password = "";
        
        $ip_address = $_SERVER['REMOTE_ADDR'];


        // Create connection
        $this->conn = new mysqli($servername, $username, $password);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        mysqli_select_db($this->conn, $database);

        $this->mysqlKeyWords = ['CURRENT_TIMESTAMP'];
    }
    public function get_db()
    {
        return $this->db_name;
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
                $values .= '"' . mysqli_real_escape_string($this->conn, $value) . '",';
            }
            // $values.="'" . $value . "',";
        }

        $cols = substr($cols, 0, -1) . ")";
        $values = substr($values, 0, -1) . ")";
        $q .= $cols . " values $values";
        // echo $q."////</br>";
        $this->query = $q; //Get a copy of the query It will be useful once we want to debug($obj->query)
        $query = mysqli_query($this->conn, $q);
        if ($query) {
            return mysqli_insert_id($this->conn);
        }
        //return $q;
        return mysqli_error($this->conn);
    }

    public function sql($q)
    {
        $query = mysqli_query($this->conn, $q);
        if ($query) {
            return mysqli_insert_id($this->conn);
        }
        //return $q;
        $this->query = $q; //Get a copy of the query It will be useful once we want to debug($obj->query)
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


        $query = mysqli_query($this->conn, $q);
        // echo $q.'</br>';
        $this->query = $q; //Get a copy of the query It will be useful once we want to debug($obj->query)
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
        $query = mysqli_query($this->conn, $sql);
        // echo $q;
        $this->query = $sql; //Get a copy of the query It will be useful once we want to debug($obj->query)
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
        $this->query = $sql; //Get a copy of the query It will be useful once we want to debug($obj->query)
        $query = mysqli_query($this->conn, $sql);
    }

    public function clean($input)
    {
        return mysqli_real_escape_string($this->conn, trim($input));
    }
}
