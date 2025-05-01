<?php
$menuPage = isset($_REQUEST["p"]) ? $_REQUEST["p"] : "";

$showPage = "home.php";
$activeMenu["lv1"] = "home";
$activeMenu["lv2"] = "";
$datatable["show"] = "false";
$datatable["src"] = "";
$datatable2["show"] = "false";
$datatable2["src"] = "";
$datatable["showDatatableStats"] = "false";
$title = "Home";

switch ($menuPage){
    case "home":
        $showPage = "home.php";
        $activeMenu["lv1"] = "home";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        $title = "Master panel : Home";
        break;
    case "dashboard":
        $showPage = "dashboard.php";
        $activeMenu["lv1"] = "dashboard";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        $title = "Master panel : dashboard";
        break;
    case "revRestaurant":
        $showPage = "revenueRestaurant.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "restaurant";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataRestaurant.php";
        break;
    case "revMassage":
        $showPage = "revenueMassage.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "massage";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataMassage.php";
        break;
    case "revIHD":
        $showPage = "revenueIHD.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "IHD";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        break;
    case "revStreams":
        $showPage = "revenueStreams.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "streams";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        break;
    case "revStats":
        $showPage = "revenueStats.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "stats";
        $datatable["showDatatableStats"] = "true";
        $datatable["src"] = "pages/tableRendering/dataStats.php";
        break;
    case "revSubscription":
        $showPage = "revenueSubscription.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "subscription";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataCostMonthly.php";
        $datatable2["show"] = "true";
        $datatable2["src"] = "pages/tableRendering/dataCostYearly.php";
        $loadTotal["totalMonthly"] = "true";
        $loadTotal["totalYearly"] = "true";
        break;
    case "revCompany"://หน้านี้เหมาะเอาไปทำสถิติหรือสรุปจำนวนพนักงาน
        $showPage = "revenueCompany.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "company";
        break;
    case "setStaff":
        $showPage = "setStaffs.php";
        $activeMenu["lv1"] = "settings";
        $activeMenu["lv2"] = "staffs";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataStaffs.php";
        $title = "Master panel : Staffs Management";
        break;
    case "coin":
        $showPage = "coin.php";
        $activeMenu["lv1"] = "coin";
        $activeMenu["lv2"] = "coin";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Coin System";
        break;
    case "websiteTemplate":
        $showPage = "websiteTemplate.php";
        $activeMenu["lv1"] = "websiteTemplate";
        $activeMenu["lv2"] = "websiteTemplate";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataWebsiteTemplate.php";
        $title = "Master panel : Website Template";
        break;
    case "myProfile":
        $showPage = "myProfile.php";
        $activeMenu["lv1"] = "myProfile";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $datatable2["show"] = "false";
        $datatable2["src"] = "";
        $datatable["showDatatableStats"] = "false";
        $title = "Master panel : My Profile";
        break;
    case "l4uPassword":
        $showPage = "l4uPassword.php";
        $activeMenu["lv1"] = "l4uPassword";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataPassword.php";
        $title = "Master panel : L4U Password Management";
        break;
    case "viewLogs":
        $showPage = "viewLogs.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/viewLogs.php";
        $title = "Master panel : SignUp Form Logs";
        break;
    case "tpSubmitted":
        $showPage = "tpSubmitted.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "";
        $title = "Master panel : Logs Website Template Submission";
        break;
    case "tools":
        $showPage = "tools.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "";
        $title = "Master panel : Tools Management & Logs";
        break;
    case "unsub":
        $showPage = "unSubmitted.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "";
        $title = "Master panel : Logs Unsubscribes Request";
        break;
    case "websiteList":
        $showPage = "websiteList.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataWebsiteList.php";
        $title = "Master panel : Website Lists";
        break;
}