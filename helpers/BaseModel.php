<?php

// namespace Models;

// use Error;
// use ErrorException;

// /**
//  * This is a simple and elegant way of populating, retrieving,  processing
//  * databases.
//  * You only need to create a model for each table in the database
//  * After you enjoy the easy manipulation of the table
//  * 
//  *This is the base model which every model should inherit from. 
//  */
// class BaseModel
// {
    // ALTER TABLE `loans` ADD `overriden_due` INT NULL DEFAULT NULL AFTER `field3`;
//     private $con;
//     function __construct()
//     {

//         $database = 'welearn';
//         $pass = "!Log19tan88";
//         $host = 'localhost';
//         $user = 'root';
//         $this->con = mysqli_connect($host, $user, $pass, $database);
//         if ($this->con) {
//         } else {
//             throw new Error("Database Connection failed." . json_encode(error_get_last()));
//         }
//     }
//     function getCon()
//     {
//         return $this->con;
//     }
// }
// class UserModel extends BaseModel
// {
//     public $all=[];
//     private $tableName='';
//     function __construct($tableName)
//     {
//         $this->tableName = $tableName;
//         parent::__construct();
//     }


//     public function getAll()
//     {
//         $query = "select * from ".$this->tableName;
//         $the_rows = [];
//         $result = mysqli_query(parent::getCon(),$query);
//         while ($row = $result->fetch_array()) {
//             $the_rows[] = $row;
//         }
//         $this->all = $the_rows;
//         return $this->all;
//     }    

// }
// // use \Models\BaseModel;

// // $myModel = new BaseModel();
// $userModel = new UserModel('users');
// $result = $userModel->getAll();
// // print_r($result);
// // while($ret = mysqli_fetch_assoc($result)){
//     // echo json_encode($result);
// // }
// echo md5('@dbR100%');

// class Test
// {
//     private $mark;
//     private $date;
//     public function __construct($mark, $date)
//     {
//         echo "Contructor is builing your object";
//         $this->mark = $mark;
//         $this->date = $date;

//     }
//     public function set_mark($mark){
//         $this->mark = $mark;
//     }
//     public function get_mark(){
//         return $this->mark;
//     }

//     public function set_date($date)
//     {
//         $this->date = $date;
//     }
//     public function get_date()
//     {
//         return $this->date;
//     }
    
//     public function __destruct()
//     {
//         echo "I'll die after 3 secons";
//         sleep(3);
//         echo "Why destroy me ";
//     }  
//     public function __clone()
//     {
//         return new Test($this->mark, $this->date);
//     }  
// }

// $test1 = new Test(90,"12-12-2021");
// $test2 = $test1->__clone();
// echo "\nTest will be available on " . $test1->get_date();

// $test2->set_date("11-18-2021");
// echo "\nTest will be available on ".$test1->get_date();


