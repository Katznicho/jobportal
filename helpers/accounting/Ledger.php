<?php

namespace Ssentezo\Accounting;

use Ssentezo\Util\AppUtil;

class Ledger
{
    static protected $transactions;

    public static function display($db, $transactions = [])
    {
        static::$transactions = $transactions;
?>
        <div class="box-body">
            <table id="transactions" class="table table-bordered table-condensed  table-hover dataTable no-footer">
                <thead>
                    <tr style="background-color: #F2F8FF">

                        <th><b>Date</b></th>
                        <th>TransId</th>
                        <th><b>Narrative</b></th>

                        <th><b>Account</b></th>
                        <th style="text-align:left"><b>Debit</b></th>
                        <th style="text-align:right"><b>Credit</b></th>
                        <!-- <th style="text-align:right"><b>Balance</b></th> -->

                    </tr>
                </thead>
                <tbody>
                    <?php

                    $totalCredits = $totalDebits = 0;

                    foreach (static::$transactions as $d) {
                        if ($d['type'] == "D") {
                            $totalDebits += $d['amount'];
                        } else {
                            $totalCredits += $d['amount'];
                        }
                        $account = new ExisitingAccount($db->select('accounts', [], ['id' => $d['account_id']])[0])

                    ?>
                        <tr>
                            <td><b><?= date('Y-m-d', strtotime(AppUtil::Comparable_date_format($d['date']))) ?></b></td>
                            <td><a href="./view_transaction.php?id=<?= $d['trans_id'] ?>"><?= $d['trans_id'] ?></a></td>
                            <td><?= $d['narrative'] ?></td>

                            <td><?= $account->getAccNumber() ?> - <a href="./view_account_transactions.php?id=<?= $account->getId() ?>" target="_blank"><?= $account->getName() ?></a></td>
                            <td style="text-align:left"><b> <?= ($d['type'] == "D" || $d['type'] == "T") ? number_format($d["amount"], 2) : "" ?></b></td>
                            <td style="text-align:right"> <b> <?= $d['type'] == "C" ? number_format($d["amount"], 2) : "" ?></b></td>
                            <!-- <td style="text-align:right"> <b><?= number_format($d['balance'], 2) ?></b></td> -->
                        </tr>
                    <?php
                    }
                    ?>

                <tfoot>
                    <tr>
                        <td colspan="4">
                            <h3>Total</h3>
                        </td>
                        <td style="text-align:left"><b>
                                <h3>
                                    <b>
                                        <?= number_format($totalDebits, 2) ?>

                                    </b>
                                </h3>
                            </b>
                        </td>

                        <td style="text-align:right"><b>
                                <h3 style="">
                                    <b>
                                        <?= number_format($totalCredits, 2) ?>
                                    </b>
                                </h3>
                            </b>
                        </td><!-- <td style="text-align:right"> <b><?= number_format($d['balance'], 2) ?></b></td> -->
                    </tr>


                    </tbody>
                </tfoot>

            </table>
        </div>

<?php
    }
}
