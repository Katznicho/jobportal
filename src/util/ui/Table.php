<?php

namespace Ssentezo\Util\UI;

class Table extends UI
{
    /**
     * Creates a 2 column table with the keys in 1st column and values in second column
     * @param array $data an associative array with data we wan tot render 
     * @param string $id an id for the table It's the normal html element  id
     * @param string $class the class attribute value of the table  
     */
    public static function create($data, $id="", $class = "table table-stripped text-bold")
    {
?>
        <div>


            <table id="<?= $id ?>" class="<?= $class ?>">
                <tbody>

                    <?php
                    foreach ($data as $key => $value) {
                    ?>
                        <tr>
                            <td><?= $key ?></td>
                            <td><?= $value ?></td>

                        </tr>


                    <?php
                    }
                    ?>
                </tbody>

            </table>
        </div>
<?php
    }
}
