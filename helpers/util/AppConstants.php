<?php

namespace Ssentezo\Util;

class AppConstants
{

    public static $PENALTY_LATE_REPAYMENT = "Late Repayment";
    public static $PENALTY_AFTER_MATURITY   = "After Maturity";
    // public static $live_manager_db="manager";
    public static $live_manager_db = "ssenhogv_manager";

    public static $USER   = "user";
    public static $company   = "";
    public static $company_db   = "";

    public static $SITE_NAME   = "";
    public static $senderId = "";
}

if (!empty($_SESSION) && isset($_SESSION['company'])) {
    AppConstants::$SITE_NAME = $_SESSION['company'];
    AppConstants::$company = $_SESSION['company'];
    AppConstants::$company_db = $_SESSION['company_db'];
    AppConstants::$senderId = $_SESSION['senderId'];
}
