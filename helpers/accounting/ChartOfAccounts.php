<?php

namespace Ssentezo\Accounting;

use Ssentezo\Util\UI\Alert;
use Ssentezo\Util\UI\Navigator;

class ChartOfAccounts
{
    public static function renderLeafAccount($account)
    {
        echo "<li style=\"list-style: none;\" class=\"list-item d-flex justify-content-between align-items-center\">" .
            $account['account_no'] . " <a href=\"./view_chart_of_account.php?id=" . $account['id'] . "\">" .
            $account['name'] .
            "</a>" .
            "</li>";
    }
    public static function renderChartOfAccounts($account, $db)
    {
        // Check if it's a category in some way.
        // If is_leaf is 1 then the account is has no children under it
        if ($account['is_leaf'] == 1) {
        } else {
            // Here we treat this as a sub category.
        }
        $accounts = $db->select("accounts", [], ['category' => $account['id']]);
?>




        <strong><?= $account['name'] ?></strong><br>
        <ul class="list-group " style="list-style: none;">
            <?php
            foreach ($accounts as $account) {
                static::renderChartOfAccounts($account, $db);
            }
            ?>

        </ul>
        </div>

        </div>
    <?php




        // We use recursion
    }
    public static function listViewAccounts($db, $error = "", $message = "")
    {
    ?>
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>Chart of Accounts</h1>

            </section>

            <!-- Main content -->
            <section class="content">
                <small>
                    <!-- <a href="./index.php">Back to Accounts</a>  -->
                    <?php

                    Navigator::breadCrumb(["Admin" => "../admin/", "Accounting" => "./index.php"], "View Chart Of Accounts");
                    ?>
                </small>

                <?php
                if ($message) {
                    Alert::create($message, $error ? 'error' : 'success');
                }
                ?>
                <!-- <small> -->
                <!-- <p style="margin-bottom: 5;"> -->
                <a style="padding-top: 0;padding-bottom:5px" class="btn btn-success  pull-right mb-5" href="./view_chart_of_accounts.php?view=tabular">Change to Tabular View</a>

                <!-- </p> -->
                <!-- </small> -->
                <div class="box box-info">
                    <!-- Horizontal Form -->

                    <div class="box-body with-border">

                        <div class="col-sm-12">
                            <div>
                                <ul class="list">



                                    <?php
                                    // Get all the charts of account Categories
                                    $coa_categories = $db->select("account_categories", [], ['active_flag' => 1, 'del_flag' => 0]);
                                    // print_r($coa_categories);

                                    foreach ($coa_categories as $category) {
                                        // Get all the accounts under this category
                                        $total_category = 0;
                                        $total_subcategory = 0;
                                        $accounts = $db->select("accounts", [], ['category' => $category['id'], 'active_flag' => 1, 'del_flag' => 0, 'is_leaf' => 0]);
                                    ?>

                                        <h3 class="main-category"> <?= $category['name'] ?></h3>
                                        <li class="list-item">

                                            <ul class="list">
                                                <?php
                                                $count = 0;
                                                foreach ($accounts as $account) {
                                                    $count++;
                                                    // Get the individual accounts
                                                    $leafAccounts =         $db->select("accounts", [], ['category' => $category['id'], 'active_flag' => 1, 'del_flag' => 0, 'is_leaf' => 1, 'sub_category_id' => $account['id']]);
                                                ?>
                                                    <li>
                                                        <h4 class="sub-category">
                                                            <!-- <b> -->

                                                            <?= $account['account_no'] . "  " . $account['name'] ?>
                                                            <!-- </b> -->

                                                        </h4>

                                                        <ul class="list">
                                                            <?php
                                                            foreach ($leafAccounts as $acc) {
                                                                $total_subcategory += $acc['balance'];
                                                            ?>
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    <!-- <h5 class="leaf-account"> -->
                                                                    <b>
                                                                        <?= $acc['account_no'] ?>
                                                                        <a href="./view_account.php?id=<?= $acc['id'] ?>"> <?= $acc['name'] ?></a>

                                                                    </b>

                                                                    <span class="badge badge-primary badge-pill">UGX <?= number_format($acc['balance']) ?></span>

                                                                </li>

                                                            <?php
                                                            }
                                                            $total_category += $total_subcategory;

                                                            ?>
                                                        </ul>
                                                        <h4>
                                                            <div class="row bg-dark text-white">
                                                                <div class="col-sm-10 text-right"> <b>Total</b> </div>
                                                                <div class="col-sm-2 text-center">
                                                                    UGX <?= number_format($total_subcategory, 2) ?>
                                                                </div>
                                                            </div>
                                                        </h4>
                                                        <hr>
                                                    </li>
                                                <?php
                                                    $total_subcategory = 0;
                                                }
                                                ?>
                                            </ul>

                                        </li>
                                        <h4>
                                            <div class="row" style="background-color: rgb(10,10,10); color:white;border-radius:5px">
                                                <div class="col-sm-10">Total <?= $category['name'] ?></div>
                                                <div class="col-sm-2 text-light text-right"><?= number_format($total_category, 2) ?></div>
                                            </div>
                                        </h4>
                                        <hr>
                                    <?php
                                        $total_category = 0;
                                    }
                                    ?>


                            </div>
                            </ul>
                        </div>

                    </div>

                </div>

            </section>
        </div>
    <?php

    }
    public static function tabularViewAccounts($db, $error = '', $message = "")
    {

    ?>
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>Chart of Accounts</h1>

            </section>

            <!-- Main content -->
            <section class="content">
                <small>
                    <!-- <a href="./index.php">Back to Accounts</a>  -->
                    <?php
                    Navigator::breadCrumb(["Admin" => "../admin/", "Accounting" => "./index.php"], "View Chart Of Accounts");
                    ?>
                </small>

                <?php
                if ($message) {
                    Alert::create($message, $error ? 'error' : 'success');
                }
                ?>

                <a style="padding-top: 0;padding-bottom:5px; " class="btn btn-success  pull-right mb-5" href="./view_chart_of_accounts.php?view=list">Change to List View</a>


                <div class="box box-info">
                    <!-- Horizontal Form -->

                    <div class="box-body with-border">

                        <div class="col-sm-12">
                            <div>
                                <table id="viewChartOfAccounts" class="table table-bordered table-condensed table-hover dataTable">
                                    <thead>
                                        <tr style="background-color: #D1F9FF">
                                            <th> Code </th>
                                            <th> Name </th>
                                            <th> SubCategory </th>
                                            <th>Category</th>
                                            <th>Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get all the charts of account Categories
                                        $query = "SELECT " .
                                            " accounts.id as id, accounts.account_no as account_no, accounts.name as name, accounts.balance as balance,  " .
                                            "cat.id as sub_category_id,cat.name as sub_category_name, cat.account_no as sub_category_no," .
                                            " mainCat.name as main_category_name " .
                                            " FROM `accounts` INNER JOIN accounts as cat on accounts.sub_category_id = cat.id " .
                                            " INNER JOIN account_categories as mainCat on accounts.category = mainCat.id " .
                                            " WHERE accounts.is_leaf=1";
                                        $accounts = $db->selectQuery($query);
                                        // print_r($coa_categories);

                                        foreach ($accounts as $account) {
                                            // print_r($account);
                                        ?>
                                            <tr>
                                                <td><?= $account['account_no'] ?></td>
                                                <td>
                                                    <a href="./view_account.php?id=<?= $account['id'] ?>">
                                                        <?= $account['name'] ?></a>

                                                </td>
                                                <td>
                                                    <?= $account['sub_category_no'] . " - " . $account['sub_category_name'] ?>
                                                </td>
                                                <td><?= $account['main_category_name'] ?></td>
                                                <td><?= number_format($account['balance'], 2) ?></td>
                                            </tr>


                                        <?php
                                        }
                                        ?>


                                    </tbody>
                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </section>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                // Reduce the size of the sidebar
                var dataTable = $('#viewChartOfAccounts').DataTable({
                    "autoWidth": true,
                    "drawCallback": function(settings) {
                        $("#view-loans").wrap("<div class='table-responsive'></div>");
                    },
                    "lengthMenu": [
                        [20, 50, 100, 250, 500, -1],
                        [20, 50, 100, 250, 500, "All (Slow)"]
                    ],
                    "iDisplayLength": 20,
                    "processing": true,
                    "serverSide": false,
                    "responsive": true,
                    "language": {
                        "processing": "<img src='https://x.loandisk.com/images/ajax-loader.gif'> Processing..",
                        "searchPlaceholder": "Search accounts",
                        "emptyTable": "No data found. To add accounts , Click on Add Account"
                    },
                    "columnDefs": [{
                        "targets": [], // column or columns numbers
                        "orderable": false // set orderable for selected columns

                    }]
                });
            });
        </script>

<?php
    }
}
