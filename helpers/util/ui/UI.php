<?php

namespace Ssentezo\Util\UI;

class UI
{
    /**
     * Creates a generic header for a page
     * @param string $heading the Heading of the page
     * @param callable $callback A callback to run after the header, This is mainly used to generate a breadcrumb
     * @param array $data The data that you may need in your callback 
     */
    public static function pageHeader($heading, $callback, $data = [])
    {
        echo "<section class=\"content-header row\">";
        echo "<div class=\"col-md-8\">";
        echo " <h1>$heading</h1>";
        echo "</div>";
        echo "<div class=\"col-md-4 \" >";
        echo "<small class=\"breadcrumb style=\"width: 100%;\" bg-white\">";

        call_user_func($callback, $data);

        echo "</small>";

        echo "</div>";
        echo "</section>";
    }
}
