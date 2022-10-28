<?php
session_start();
include '../helpers/DbAcess.php';
include '../helpers/AppUtil.php';
echo "hello"."<br/>";
$db = new DbAcess();
$transactions = $db->select("savings_transcations",[]);
foreach($transactions as $trans){
    if($trans['savings_account_id'] > 0){
        continue;
    }
    $description = $trans['description'];
    //$name = explode(" ",$description)[1];
    $name = explode(" ",$description)[0];
   //echo $name." ".$name1."<br/>";
    //$name = "%".$name."%";
   /* $sql = "select * from borrower where fname like '$name'";
    $borrower = $db->selectQuery($sql); */
    $borrower = $db->select("borrower",[],[ "lname" => $name ]);
    if(empty($borrower)){
        continue;
    }
    foreach($borrower as $person){
         
            //echo $person['id']."<br/>";
        
        $savings_account = $db->select("savings_account",[],["borrower_id" => $person['id']]);
        if(!empty($savings_account)){
            continue;
        }
        
        $default_saving_product_id = "0";
        $tablehre = 'savings_account';
                $acct = AppUtil::create_account($person['id']);
                echo $acct."<br/>";
                $staff = "1";
                $dataInsert = [
                    'savings_product_id' => $default_saving_product_id,
                    'account_no' => $acct,
                    'borrower_id' => $person['id'],
                    'creation_user' => $staff
                ];

                $saveAcct = $db->insert($tablehre, $dataInsert);
                if(is_numeric($saveAcct)){
                    $updated=$db->update("savings_transcations",['savings_account_id'=>$saveAcct],["id"=>$trans['id']]);
                    echo "insertion worked for ".$name."<br/>";
                }
                else{
                    echo $saveAcct;
                }
    } 
}

