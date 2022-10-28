<?php

namespace Ssentezo\Accounting;

class Transaction
{
    protected $id;
    protected $accountId;
    protected $date;
    protected $narrative;
    protected $type;
    protected $amount;
    protected $balance;
    protected $createdBy;
    protected $activeFlag;
    protected $delFlag;
    // Setters and getters

    /**
     * Create a table for the transactions 
     * @param DbAccess $db The database connection of the company 
     * @param array $transactions An associative array of the transactions to display
     * @param string $description A simple heading tha will be diaplayed above the table
     * @param array $link An associative array with 2 keys href and text, The href is the url of the link and text will be the display text of the link
     * 
     */
    public static function tabularViewTransactions($db, $transactions, $description, $link = 0)
    {
?>

        <div class="box box-info">
            <!-- Horizontal Form -->
            <div class="box-header">
                <div class="row">
                    <div class="col-sm-10">
                        <b><?= $description ?> </b>
                    </div>
                    <div class="col-sm-2">
                        <?php
                        if ($link) {
                        ?>
                            <a href="<?= $link['href'] ?>" class="btn btn-success"><?= $link['text'] ?></a>
                        <?php
                        }
                        ?>
                    </div>

                </div>

            </div>

            <div class="box-body">

                <table id="transactions" class="table table-bordered table-condensed  table-hover dataTable no-footer">
                    <thead>
                        <tr style="background-color: #F2F8FF">
                            <th><b>Trans Id</b></th>
                            <th><b>Date</b></th>
                            <th>Type</th>
                            <th><b>Time</b></th>
                            <th><b>Narrative</b></th>
                            <th>Amount</th>
                            <th><b>Accounts Involved</b></th>
                            <th><b>status</b></th>
                            <th><b>Action</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($transactions as $transaction) {

                            $accounts = explode(",", $transaction['accounts']);

                            $id = $transaction['id'];
                            $description = $transaction['description'];
                            $status = $transaction['status'];
                            $date = $transaction['date'];
                            $time = date("H:i:s", $transaction['time']);
                            $type = $transaction['type'];
                            $edit_link = "./edit_" . strtolower($type) . ".php";
                            $editable_types = array("Income", "Expense", "Asset", "Liability", "Equity");

                        ?>
                            <tr>
                                <td><a href="./view_transaction.php?id=<?= $id ?>"><?= $id ?></a></td>
                                <td><?= $date ?></td>
                                <td><?= $type ?></td>
                                <td><?= $time ?></td>
                                <td><?= $description ?></td>
                                <td><?= number_format($transaction['amount'], 2) ?></td>
                                <td>
                                    <?php
                                    $data = [];
                                    foreach ($accounts as $account_id) {
                                        if (!$account_id)
                                            continue;
                                        $acc = new ExisitingAccount($db->select('accounts', [], ['id' => $account_id])[0]);
                                    ?>

                                        <?= $acc->getAccNumber() ?>
                                        <a href="./view_account.php?id=<?= $acc->getId() ?>">
                                            <?= $acc->getName() ?></a> <br>

                                    <?php
                                    }
                                    ?>
                                </td>

                                <td>
                                    <div class="badge " style="background-color: <?= $transaction['status'] == 'success' ? "green" : "brown" ?>;">
                                        <?= $transaction['status'] ?>
                                    </div>

                                </td>
                                <td>
                                    <div class="btn-group-vertical">
                                        <?php
                                        if (in_array($type, $editable_types)) {
                                        ?>
                                            <a type="button" class="btn btn-xs btn-default btn-block" href="<?= $edit_link . "?id=" . $id ?>">Edit</a>
                                        <?php
                                        }
                                        ?>
                                        <a type="button" class="btn btn-xs btn-default btn-block" href="?from_date=<?= $_GET['from_date'] . "&to_date=" . $_GET['to_date'] ?>&delete=yes&id=<?= $id ?>" onClick="javascript:return confirm('Are you sure you want to Delete this transaction?')">Delete</a>
                                    </div>
                                </td>
                            </tr>



                        <?php
                        }
                        ?>

                    </tbody>

                </table>

            </div>



        </div>
<?php
    }
    public static function getTransactionsFiltered()
    {
        if (isset($_GET['from_date']) && strlen($_GET['from_date']) > 0 && isset($_GET['to_date']) && strlen($_GET['to_date']) > 0) {
            $to = $_GET['to_date'];
            $from = $_GET['from_date'];
            $to_timestamp = date('Y-m-d H:i:s', strtotime($to));
            $from_timestamp = date('Y-m-d H:i:s', strtotime($from));
            if ($to == $from) { //someone can enter from 4th to 4th😂
                $query = "SELECT * FROM transactions WHERE active_flag=1 and `type`='Income' and del_flag=0 and 	`date` LIKE'$from%'";
                $description = "Transactions for <b>" . date('l, d F Y', strtotime($from)) . "</b>";
            } else {
                $query = "SELECT * FROM transactions WHERE active_flag=1 and `type`='Income' and del_flag=0 and 	`date`>='$from_timestamp' and `date`<='$to_timestamp'";
                $description = "Transactions from " . date('d M Y', strtotime($from)) . " to " . date('d M Y', strtotime($to));
            }
        } else if (isset($_GET['from_date']) && strlen($_GET['from_date']) > 0) {
            $to = $_GET['to_date'];
            $from = $_GET['from_date'];
            $to_timestamp = date('Y-m-d H:i:s', strtotime($to));
            $from_timestamp = date('Y-m-d H:i:s', strtotime($from));

            $query = "SELECT * FROM transactions WHERE active_flag=1 and `type`='Income' and del_flag=0 and 	`date`>='$from_timestamp'";
            $description = "Transactions from " . date('d M Y', strtotime($from)) . " up-to-date ";
        } else if (isset($_GET['to_date']) && strlen($_GET['to_date']) > 0) {
            $to = $_GET['to_date'];
            $from = $_GET['from_date'];
            $to_timestamp = date('Y-m-d H:i:s', strtotime($to));
            $from_timestamp = date('Y-m-d H:i:s', strtotime($from));

            $query = "SELECT * FROM transactions WHERE active_flag=1 and `type`='Income' and del_flag=0 and 	`date`<='$to_timestamp' ";
            $description = "All Transactions  up to " . date('d M Y H:i:s', strtotime($to));
        } else {
            //'Load transactions for today
            $search = false;
            // $today = date('Y-m-d');
            // $query = "SELECT * FROM transactions WHERE active_flag=1 and `type`='Income' and del_flag=0 and 	`date` LIKE'$today%'";
            $description = "Select the dates below generate ";
        }
    }
}
