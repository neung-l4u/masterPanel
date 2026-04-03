<?php
$menuPage = isset($_GET["p"]) ? $_GET["p"] : "";

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
        $title = "Master panel : Revenue Restaurant";
        break;
    case "revMassage":
        $showPage = "revenueMassage.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "massage";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataMassage.php";
        $title = "Master panel : Revenue Massage";
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
        $title = "Master panel : Revenue IHD";
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
        $title = "Master panel : Revenue Streams";
        break;
    case "revStats":
        $showPage = "revenueStats.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "stats";
        $datatable["showDatatableStats"] = "true";
        $datatable["src"] = "pages/tableRendering/dataStats.php";
        $title = "Master panel : Revenue Stats";
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
        $title = "Master panel : Revenue Subscription";
        break;
    case "revCompany"://หน้านี้เหมาะเอาไปทำสถิติหรือสรุปจำนวนพนักงาน
        $showPage = "revenueCompany.php";
        $activeMenu["lv1"] = "revenueTracking";
        $activeMenu["lv2"] = "company";
        $title = "Master panel : Revenue Company";
        break;
    case "setStaff":
        $showPage = "setStaffs.php";
        $activeMenu["lv1"] = "userMgmt";
        $activeMenu["lv2"] = "staffs";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataStaffs.php";
        $title = "Master panel : Staffs Management";
        break;
    case "tools":
        $showPage = "tools.php";
        $activeMenu["lv1"] = "sysSettings";
        $activeMenu["lv2"] = "tools";
        $datatable["show"] = "true";
        $datatable["src"] = "";
        $title = "Master panel : Tools Management & Logs";
        break;
    case "coin":
        $showPage = "coin.php";
        $activeMenu["lv1"] = "rewardsCoin";
        $activeMenu["lv2"] = "coin";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Coin System";
        break;
    case "websiteTemplate":
        $showPage = "websiteTemplate.php";
        $activeMenu["lv1"] = "websiteMgmt";
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
        $activeMenu["lv1"] = "sysSettings";
        $activeMenu["lv2"] = "l4uPassword";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataPassword.php";
        $title = "Master panel : L4U Password Management";
        break;
    case "viewLogs":
        $showPage = "viewLogs.php";
        $activeMenu["lv1"] = "";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataViewLogs.php";
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
    case "feedbackMonday":
        $showPage = "feedbackMonday.php";
        $activeMenu["lv1"] = "logs";
        $activeMenu["lv2"] = "feedbackMonday";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataFeedbackMonday.php";
        $title = "Master panel : Feedback Monday";
        break;
    case "printersLog":
        $showPage = "printersLog.php";
        $activeMenu["lv1"] = "logs";
        $activeMenu["lv2"] = "printersLog";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataPrintersLog.php";
        $title = "Master panel : Printers Log";
        break;
    case "signupLogs":
        $showPage = "signupLogs.php";
        $activeMenu["lv1"] = "logs";
        $activeMenu["lv2"] = "signupLogs";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataSignupLogs.php";
        $title = "Master panel : SignUp Logs (Staff)";
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
        $activeMenu["lv1"] = "websiteList";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataWebsiteList.php";
        $title = "Master panel : Website Lists";
        break;
    case "voucherLogs":
        $showPage = "voucherLogs.php";
        $activeMenu["lv1"] = "voucherLogs";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataVoucherLogs.php";
        $title = "Master panel : Voucher Logs";
        break;
    case "zoomExt":
        $showPage = "zoomExt.php";
        $activeMenu["lv1"] = "zoomExt";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataZoomExt.php";
        $title = "Master panel : Zoom Extension";
        break;
    case "rewardCoin":
        $showPage = "rewardCoin.php";
        $activeMenu["lv1"] = "rewardsCoin";
        $activeMenu["lv2"] = "rewardCoin";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataRewardCoin.php";
        $title = "Master panel : Reward Coin History";
        break;
    case "checkinLogs":
        $showPage = "checkinLogs.php";
        $activeMenu["lv1"] = "userMgmt";
        $activeMenu["lv2"] = "checkinLogs";
        $datatable["show"] = "true";
        $datatable["src"] = "pages/tableRendering/dataCheckinLogs.php";
        $title = "Master panel : Check-in Logs";
        break;
    case "report":
        $showPage = "report.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Report";
        break;
    case "reportWeekly":
        $showPage = "report.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportWeekly";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Report Weekly";
        break;
    case "reportMonthly":
        $showPage = "report.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportMonthly";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Report Monthly";
        break;
    case "reportYearly":
        $showPage = "report.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportYearly";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Report Yearly";
        break;
    case "reportOverview":
        $showPage = "reportOverview.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportOverview";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Report Overview";
        break;
    case "reportGA":
        $showPage = "reportGA.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportGA";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Google Analytics";
        break;
    case "reportRevenue":
        $showPage = "reportRevenue.php";
        $activeMenu["lv1"] = "report";
        $activeMenu["lv2"] = "reportRevenue";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : Revenue Report";
        break;
    case "l4utaskBoards":
        $showPage = "l4utaskBoards.php";
        $activeMenu["lv1"] = "l4utask";
        $activeMenu["lv2"] = "l4utaskBoards";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : L4U Task Boards";
        break;
    case "l4utaskBoard":
        $showPage = "l4utaskBoard.php";
        $activeMenu["lv1"] = "l4utask";
        $activeMenu["lv2"] = "l4utaskBoard";
        $datatable["show"] = "false";
        $datatable["src"] = "";
        $title = "Master panel : L4U Task Board";
        break;
}