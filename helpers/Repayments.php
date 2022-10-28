
<?php
class Repayments
{
    public static function addRepayment()
    {
    }
    public static function editRepayment()
    {
    }
    public static function deleteRepayment()
    {
    }
    public static function getAllRepaments()
    {
    }

    public static function getAllRepaymentsSummary($db)
    {
        $repaymentsSummary = [];
        // Get all the repayments that are not deleted(i.e active flag=1)
        $repayments = $db->select("loan_installment_paid", [], ["active_flag" => 1, "del_flag" => 0]);
        foreach ($repayments as $repayment) {
            $id = $repayment['id'];
            $loanID = $repayment['loan_id'];
            $borrowerId =  $repayment['borrower_id'];
            $amount = $repayment['amount'];
            $paymentDate = $repayment['payment_date'];
            $reducingBalance = $repayment['reducing_bal'];
            $createdBy = $repayment['creation_user'];
            $createdOn = $repayment['creation_date'];
            $collectionDate = $repayment['collection_date'];
            $repaymentMethod = $repayment['repayment_mtd'];
            $description = $repayment['description'];
            $lastModifiedBy = $repayment['last_modified_by'];
            $lastModifiedOn = $repayment['last_modified_date'];
            $isGroup = $repayment['is_group'];
            $depositedBy = $repayment['deposited_by'];
           
            // Get the loan which belogs to the repayment
            $loan = $db->select("loans", [], ['id' => $loanID])[0];
            $loanNo = $loan['loan_no'];

            // Get the borrower too
            $borrower = $db->select("borrower", [], ['id' => $borrowerId])[0];

            // Get the Loan Product
            $loan_product = $db->select('loan_product', [], ['id' => $loan['loan_product_id']])[0];
            $loan_name = $loan_product['name'];

            // Get the staff who added the repayment
            $collector = $db->select("staff", [], ["id" => $createdBy])[0];
            $loanCollector = $collector['fname'] . " " . $collector["lname"];
            
         
            $addedOn = $repayment['payment_date'];
            $collectionDate = $repayment['collection_date'];
         
            
            
            $csv_record = array(
                $borrower['unique_no'],
                $borrower['title'] . " " . $borrower['fname'] . " " . $borrower['lname'],
                $borrower['mobile_no'],
                $loan_name,
                $amount,
                $collectionDate,
                $addedOn,
                $depositedBy,
                $loanCollector



            );
            return array(
                "id",
                 "loan_id", 
                 "borrower_id", 
                 "amount", 
                 "payment_date",
                "reducing_bal", 
                "creation_user", 
                "creation_date", 
                "collection_date",
                "repayment_mtd", 
                "description", 
                "last_modified_by",
                 "last_modified_date",
                "del_flag", 
                "active_flag",
                 "is_group", 
                 "deposited_by"
            );
           
            echo implode(",", $csv_record);
            echo "\n";
        }
    }
}

?>