<?php
$config["log"]["0001"] = array("category" => "SYS","level" => 2,"message" => "configから取得");

// 500 Error
$config["log"]["0011"] = array("category" => "SYS","level" => 2,"message" => "[500 Error] Server execute error.");




$config["log"]["1001"] = array("category" => "INFO","level" => 1,"message" => "configから取得2");

// Success local login.
$config["log"]["1011"] = array("category" => "INFO","level" => 1,"message" => "[LOCAL LOGIN] Succeed.");
// Fail local login.
$config["log"]["1012"] = array("category" => "INFO","level" => 1,"message" => "[LOCAL LOGIN] Failed.");
// Success single sign on login.
$config["log"]["1013"] = array("category" => "INFO","level" => 1,"message" => "[SSO LOGIN] Succeed.");
// Fail single sign on login.
$config["log"]["1014"] = array("category" => "INFO","level" => 1,"message" => "[SSO LOGIN] Failed.");
// Success logout.
$config["log"]["1015"] = array("category" => "INFO","level" => 1,"message" => "[LOGOUT] Succeed.");
// Change user succeed.
$config["log"]["1016"] = array("category" => "INFO","level" => 1,"message" => "[CHANGE USER] Succeed.");

// Binder PDF downloaded.
$config["log"]["1021"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD PDF] Binder PDF downloaded.");
// BinderPage PDF downloaded.
$config["log"]["1022"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD PDF] BinderPage PDF downloaded.");
// OriginalPage PDF downloaded.
$config["log"]["1023"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD PDF] OriginalPage PDF downloaded.");
// BinderPage Image with annotation downloaded.
$config["log"]["1024"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD Image] BinderPage Image downloaded.");
// OriginalPage Image with annotation downloaded.
$config["log"]["1025"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD Image] OriginalPage Image downloaded.");
// Binder XML with Image downloaded.
$config["log"]["1026"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD XML] Binder XML downloaded.");
// BinderPage XML with Image downloaded.
$config["log"]["1027"] = array("category" => "INFO","level" => 1,"message" => "[DOWNLOAD XML] BinderPage XML downloaded.");
// OriginalPage Image upload failed.
$config["log"]["1028"] = array("category" => "INFO","level" => 1,"message" => "[UPLOAD IMAGE] OriginalPage Image upload failed.");

// Binder saved.
$config["log"]["1031"] = array("category" => "INFO","level" => 1,"message" => "[DATA SAVE] Binder saved.");
// BinderPage saved.
$config["log"]["1032"] = array("category" => "INFO","level" => 1,"message" => "[DATA SAVE] BinderPage saved.");
// OriginalPage saved.
$config["log"]["1033"] = array("category" => "INFO","level" => 1,"message" => "[DATA SAVE] OriginalPage saved.");
// Binder deleted.
$config["log"]["1034"] = array("category" => "INFO","level" => 1,"message" => "[DATA DELETE] Binder deleted.");
// BinderPage deleted.
$config["log"]["1035"] = array("category" => "INFO","level" => 1,"message" => "[DATA DELETE] BinderPage deleted.");
// OriginalPage deleted.
$config["log"]["1036"] = array("category" => "INFO","level" => 1,"message" => "[DATA DELETE] OriginalPage deleted.");
