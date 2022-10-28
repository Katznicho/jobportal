<?php

namespace App\Util\UI;

class Alert extends UI
{
    /**
     * Creates a dissmissible alert of the given type with the given message
     * @param string $message The message of the alert
     * @param string type The type of alert (success, warning, error ...) 
     */
    public static function create($message, $type = 'success')
    {
        echo "<div class=\"alert alert-$type alert-dismissible\" role=\"alert\">" .
            "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" .
            "<span aria-hidden=\"true\">&times;</span>" .
            "</button>" .
            $message .
            "</div>";
    }
    /**
     * Makes an alert that will be in the session and it will be displayed upon @method calling getSessionAlert
     */
    public static function setSessionAlert($message, $type)
    {
        $_SESSION['__alert_message'] = $message;
        $_SESSION['__alert_type'] = $type;
    }
    public static function getSessionAlert()
    {
        $message = $_SESSION['__alert_message'];
        $type = $_SESSION['__alert_type'];
        if (strlen($message) > 3) {
            echo "<div class=\"alert alert-$type alert-dismissible\" role=\"alert\">" .
                "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" .
                "<span aria-hidden=\"true\">&times;</span>" .
                "</button>" .
                $message .
                "</div>";
            $_SESSION['__alert_message'] = '';
            $_SESSION['__alert_type'] = '';
        }
    }
}
