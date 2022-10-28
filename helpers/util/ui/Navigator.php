<?php

namespace Ssentezo\Util\UI;

class Navigator extends UI
{
    /**
     * Creates a breadcrump with links in the order as they are in the array of links
     * @param array $links An associative array of links where keys are the names of the links
     * and values ate the urls
     * i.e ['Home'=>'./']
     * @param string $currentPage This is the current page, It will be the last part in the breadcrump
     * i.e ..>...>Last Part
     */
    public static function breadCrumb($links, $currentPage)
    {
        echo "<small class=\"btn\">";
        foreach ($links as $key => $link) {
            echo "<a href=\"$link\">$key</a>";
            echo " &RightAngleBracket; ";
        }
        echo $currentPage;
        echo "</small>";
    }
}
