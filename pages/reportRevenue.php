<?php
// Revenue Report — Monday.com Summary Dashboard
?>
<div class="container-fluid revenue-dash">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center rev-header">
        <h5 class="mb-0 font-weight-bold">Revenue Report</h5>
        <div class="d-flex align-items-center" style="gap:10px;">
            <span class="rc-label" id="fetchInfo">—</span>
            <button class="btn btn-sm rev-refresh-btn" id="btnRefresh">
                <i class="bi bi-arrow-clockwise" id="refreshIcon"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Main Tabs -->
    <ul class="nav rev-main-tabs" id="revMainTabs">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="#" data-toggle="dropdown" id="ddStripe">Stripe <span class="dd-sub" id="ddStripeSub">US</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item active" href="#" data-tab="stripe_au">Stripe AU</a>
                <a class="dropdown-item" href="#" data-tab="stripe_us">Stripe US</a>
                <a class="dropdown-item" href="#" data-tab="stripe_th">Stripe TH</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-tab="connect">Stripe Connect</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" id="ddSms">SMS <span class="dd-sub" id="ddSmsSub">AU</span></a>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" data-tab="sms_au">SMS AU</a>
                <a class="dropdown-item" href="#" data-tab="sms_us">SMS US</a>
                <a class="dropdown-item" href="#" data-tab="sms_uk">SMS UK</a>
            </div>
        </li>
        <li class="nav-item"><a class="nav-link" href="#" data-tab="yelp">Yelp Ads</a></li>
        <li class="nav-item"><a class="nav-link" href="#" data-tab="summary">Summary</a></li>
    </ul>

    <!-- Loading -->
    <div id="revLoading" class="text-center py-5">
        <div class="spinner-border" style="width:2.5rem;height:2.5rem;color:#635bff;" role="status"></div>
        <p class="mt-3 text-muted">Loading revenue data...</p>
        <p class="mt-2 text-muted" style="font-size:13px;"><span id="loadTimer">0.0</span>s</p>
    </div>

    <!-- Error -->
    <div id="revError" class="alert alert-danger mt-3" style="display:none;"></div>

    <!-- Dashboard -->
    <div id="revDashboard" style="display:none;">

        <!-- ====== TAB CONTENT: Stripe / SMS / Yelp (individual) ====== -->
        <div id="tabSingle" style="display:none;">

            <!-- Toolbar: Title + Date Range + Mode -->
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
                            <div>
                                <h5 class="card-title mb-0" id="tabTitle">—</h5>
                                <span class="rc-label" id="tabDateRange"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== MODE: Split View (Stripe tabs only) ===== -->
            <div id="stripeSplitView" style="display:none;">
                <div class="row" style="position:relative;">
                    <!-- Stripe API (full width) -->
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap" style="gap:8px;">
                                    <span class="source-badge source-stripe"><i class="bi bi-credit-card"></i> Stripe API</span>
                                    <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                                        <div class="d-flex align-items-center" style="gap:6px;">
                                            <label class="rc-label mb-0" style="font-size:11px;">Mode:</label>
                                            <select id="tabMode" class="form-control form-control-sm" style="width:auto;font-size:11px;">
                                                <option value="alltime">All Time</option>
                                                <option value="compare">Compare</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center" style="gap:4px;">
                                            <button class="btn btn-outline-secondary btn-xs sp-preset active" data-days="28">28d</button>
                                            <button class="btn btn-outline-secondary btn-xs sp-preset" data-days="90">90d</button>
                                            <button class="btn btn-outline-secondary btn-xs sp-preset" data-days="365">1Y</button>
                                            <button class="btn btn-outline-secondary btn-xs sp-preset" data-days="9999">All</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3" style="gap:6px;">
                                    <input type="date" id="spDateStart" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <span style="font-size:11px;color:#697386;">to</span>
                                    <input type="date" id="spDateEnd" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <button class="btn btn-sm btn-primary" id="spDateGo" style="font-size:11px;padding:2px 10px;">Go</button>
                                </div>
                                <div id="splitStripeLoading" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" style="color:#635bff;" role="status"></div>
                                    <span class="ml-2 text-muted" style="font-size:12px;">Loading Stripe data...</span>
                                </div>
                                <div id="splitStripeContent" style="display:none;">
                                    <div class="row" id="splitStripeMetrics"></div>
                                    <hr class="my-2">
                                    <h6 class="mb-2" style="font-size:12px;color:#697386;font-weight:600;">Monthly Breakdown</h6>
                                    <div style="max-height:420px;overflow-y:auto;">
                                        <table class="table table-sm table-striped mb-0" style="font-size:11px;">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Period</th>
                                                    <th class="text-right">Amount</th>
                                                    <th class="text-right">Fee</th>
                                                    <th class="text-right">Revenue</th>
                                                    <th class="text-right">Charges</th>
                                                    <th class="text-right">Refund</th>
                                                </tr>
                                            </thead>
                                            <tbody id="splitStripeMonthly"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-muted text-right mt-2" style="font-size:10px;" id="spPeriodInfo">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#stripeSplitView -->

            <!-- ===== MODE: Yelp Split View (Yelp Ads tab only) ===== -->
            <div id="yelpSplitView" style="display:none;">
                <div class="row" style="position:relative;">
                    <!-- Yelp Sync chain icon -->
                    <div id="yelpSyncChainWrap" style="position:absolute;left:50%;top:18px;transform:translateX(-50%);z-index:10;">
                        <button id="btnYelpSyncChain" class="sync-chain-btn" title="Sync datepickers between Monday &amp; Yelp">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                    <!-- LEFT: Monday.com -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="source-badge source-monday"><i class="bi bi-table"></i> Monday.com</span>
                                </div>
                                <div class="d-flex align-items-center mb-3" style="gap:6px;">
                                    <input type="month" id="yelpMonDateStart" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <span style="font-size:11px;color:#697386;">to</span>
                                    <input type="month" id="yelpMonDateEnd" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <button class="btn btn-sm btn-outline-primary" id="yelpMonDateGo" style="font-size:11px;padding:2px 10px;">Filter</button>
                                    <button class="btn btn-sm btn-outline-secondary" id="yelpMonDateReset" style="font-size:11px;padding:2px 8px;">All</button>
                                </div>
                                <div class="row" id="yelpMondayMetrics"></div>
                                <hr class="my-2">
                                <h6 class="mb-2" style="font-size:12px;color:#697386;font-weight:600;">Monthly Breakdown</h6>
                                <div style="max-height:420px;overflow-y:auto;">
                                    <table class="table table-sm table-striped mb-0" style="font-size:11px;">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Period</th>
                                                <th class="text-right">Revenue</th>
                                                <th class="text-right">Items</th>
                                            </tr>
                                        </thead>
                                        <tbody id="yelpMondayMonthly"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- RIGHT: Yelp Billing -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="source-badge" style="background:#d32323;color:#fff;"><i class="bi bi-megaphone"></i> Yelp Billing</span>
                                    <div class="d-flex align-items-center" style="gap:4px;">
                                        <label for="yelpFileUpload" class="btn btn-outline-secondary btn-xs mb-0" style="cursor:pointer;">
                                            <i class="bi bi-upload"></i> Upload
                                        </label>
                                        <input type="file" id="yelpFileUpload" accept=".xlsx,.xls,.csv,.pdf,.png,.jpg,.jpeg,.webp" style="display:none;">
                                    </div>
                                </div>
                                <div class="d-flex align-items-center mb-3" style="gap:6px;">
                                    <select id="yelpFileSelect" class="form-control form-control-sm" style="font-size:11px;">
                                        <option value="">-- Select billing file --</option>
                                    </select>
                                    <button class="btn btn-sm btn-primary" id="yelpFileLoad" style="font-size:11px;padding:2px 10px;white-space:nowrap;">Load</button>
                                    <button class="btn btn-sm btn-outline-danger" id="yelpFileDelete" style="font-size:11px;padding:2px 8px;white-space:nowrap;" title="Delete selected file"><i class="bi bi-trash"></i></button>
                                </div>
                                <div id="yelpBillingLoading" style="display:none;" class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" style="color:#d32323;" role="status"></div>
                                    <span class="ml-2 text-muted" style="font-size:12px;">Loading billing data...</span>
                                </div>
                                <div id="yelpBillingContent" style="display:none;">
                                    <div class="row" id="yelpBillingMetrics"></div>
                                    <hr class="my-2">
                                    <h6 class="mb-2" style="font-size:12px;color:#697386;font-weight:600;">By Business</h6>
                                    <div style="max-height:300px;overflow-y:auto;">
                                        <table class="table table-sm table-striped mb-0" style="font-size:11px;">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Business</th>
                                                    <th class="text-right">Revenue</th>
                                                    <th class="text-right">CPC</th>
                                                </tr>
                                            </thead>
                                            <tbody id="yelpBillingBusiness"></tbody>
                                        </table>
                                    </div>
                                    <hr class="my-2">
                                    <h6 class="mb-2" style="font-size:12px;color:#697386;font-weight:600;">By Feature</h6>
                                    <div style="max-height:200px;overflow-y:auto;">
                                        <table class="table table-sm table-striped mb-0" style="font-size:11px;">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Feature</th>
                                                    <th class="text-right">Revenue</th>
                                                    <th class="text-right">Count</th>
                                                </tr>
                                            </thead>
                                            <tbody id="yelpBillingFeature"></tbody>
                                        </table>
                                    </div>
                                    <div class="text-muted text-right mt-2" style="font-size:10px;" id="ypPeriodInfo">—</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#yelpSplitView -->

            <!-- ===== MODE: All Time ===== -->
            <div id="modeAllTime">
                <!-- Overview Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-end mb-2">
                                    <div class="d-flex align-items-center" style="gap:6px;">
                                        <label class="rc-label mb-0" style="font-size:11px;">Mode:</label>
                                        <select id="tabModeAlt" class="form-control form-control-sm" style="width:auto;font-size:11px;">
                                            <option value="alltime">All Time</option>
                                            <option value="compare">Compare</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Amount</div>
                                        <div class="rc-big" id="tabAmount">0</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Revenue</div>
                                        <div class="rc-big" id="tabRevenue">0</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Fee</div>
                                        <div class="rc-big" id="tabFee">0</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Net</div>
                                        <div class="rc-big" id="tabNet">0</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Charges</div>
                                        <div class="rc-big" id="tabCharges">0</div>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <div class="rc-label">Items</div>
                                        <div class="rc-big" id="tabItems">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MoM & YoY side by side -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Compare MoM</h5>
                                <table class="table table-sm table-bordered rev-compare-table mt-2">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th class="text-center" id="tabMomPrev">Prev</th>
                                            <th class="text-center" id="tabMomCur">Current</th>
                                            <th class="text-center">Change</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabMomBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Compare YoY</h5>
                                <table class="table table-sm table-bordered rev-compare-table mt-2">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th class="text-center" id="tabYoyPrev">Prev</th>
                                            <th class="text-center" id="tabYoyCur">Current</th>
                                            <th class="text-center">Change</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabYoyBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Breakdown -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title mb-0">Monthly Breakdown</h5>
                                    <select id="tabMonthView" class="form-control form-control-sm" style="width:auto;">
                                        <option value="month">By Month</option>
                                        <option value="year">By Year</option>
                                    </select>
                                </div>
                                <table class="table table-sm table-striped table-hover" style="font-size:12px;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Period</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Fee</th>
                                            <th class="text-right">Net</th>
                                            <th class="text-right">Revenue</th>
                                            <th class="text-right">Charges</th>
                                            <th class="text-right">Items</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabMonthlyBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#modeAllTime -->

            <!-- ===== MODE: Compare ===== -->
            <div id="modeCompare" style="display:none;">
                <!-- Month Pickers -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card compare-picker-card">
                            <div class="card-body d-flex align-items-center" style="gap:10px;">
                                <span class="compare-side-label" style="background:#635bff;">A</span>
                                <label class="rc-label mb-0">Month:</label>
                                <input type="month" id="cmpMonthA" class="form-control form-control-sm" style="width:auto;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card compare-picker-card">
                            <div class="card-body d-flex align-items-center" style="gap:10px;">
                                <span class="compare-side-label" style="background:#0ea5e9;">B</span>
                                <label class="rc-label mb-0">Month:</label>
                                <input type="month" id="cmpMonthB" class="form-control form-control-sm" style="width:auto;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side-by-side overview cards -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card" style="border-top:3px solid #635bff;">
                            <div class="card-body">
                                <h6 class="card-title"><span class="compare-side-label" style="background:#635bff;">A</span> <span id="cmpLabelA">—</span></h6>
                                <div class="row mt-2" id="cmpCardA"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card" style="border-top:3px solid #0ea5e9;">
                            <div class="card-body">
                                <h6 class="card-title"><span class="compare-side-label" style="background:#0ea5e9;">B</span> <span id="cmpLabelB">—</span></h6>
                                <div class="row mt-2" id="cmpCardB"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Comparison: A vs B</h5>
                                <table class="table table-sm table-bordered rev-compare-table mt-2">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th class="text-center" style="color:#635bff;" id="cmpThA">A</th>
                                            <th class="text-center" style="color:#0ea5e9;" id="cmpThB">B</th>
                                            <th class="text-center">Diff</th>
                                            <th class="text-center">Change %</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cmpTableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#modeCompare -->

        </div><!-- /#tabSingle -->

        <!-- ====== TAB CONTENT: Summary ====== -->
        <div id="tabSummary" style="display:none;">

            <!-- ===== Stripe Group ===== -->
            <div class="sum-group mt-3">
                <div class="sum-group-header"><i class="bi bi-credit-card mr-1"></i> Stripe <span class="sum-meta" id="sumMetaStripe"></span></div>
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                        <div class="card h-100 border-left-au">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge badge-au">AU</span> Stripe AU</div>
                                <div class="rc-big" id="sum_stripe_au">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_stripe_au_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                        <div class="card h-100 border-left-us">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge badge-us">US</span> Stripe US</div>
                                <div class="rc-big" id="sum_stripe_us">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_stripe_us_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                        <div class="card h-100 border-left-th">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge badge-th">TH</span> Stripe TH</div>
                                <div class="rc-big" id="sum_stripe_th">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_stripe_th_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                        <div class="card h-100" style="border-left:4px solid #635bff;">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><i class="bi bi-diagram-3 mr-1" style="color:#635bff;"></i> Stripe Connect <span class="rc-label" id="sum_connect_period"></span></div>
                                <div class="rc-big" id="sum_connect">—</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_connect_rev">—</span></div>
                                <div class="rc-sub"><span class="rc-label">Fee:</span> <span id="sum_connect_fee">—</span></div>
                                <div class="rc-sub"><span class="rc-label">Net:</span> <span id="sum_connect_net">—</span></div>
                                <div class="rc-sub"><span class="rc-label">Charges:</span> <span id="sum_connect_charges">—</span></div>
                                <div class="rc-sub"><span class="rc-label">Items:</span> <span id="sum_connect_items">—</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SMS Group ===== -->
            <div class="sum-group">
                <div class="sum-group-header"><i class="bi bi-chat-dots mr-1"></i> SMS <span class="sum-meta" id="sumMetaSms"></span></div>
                <div class="row">
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-left-sms">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge" style="background:#00c875;color:#fff;">AU</span> SMS AU</div>
                                <div class="rc-big" id="sum_sms_au">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_sms_au_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-left-sms">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge" style="background:#00c875;color:#fff;">US</span> SMS US</div>
                                <div class="rc-big" id="sum_sms_us">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_sms_us_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <div class="card h-100 border-left-sms">
                            <div class="card-body py-2">
                                <div class="rc-type-label"><span class="badge" style="background:#00c875;color:#fff;">UK</span> SMS UK</div>
                                <div class="rc-big" id="sum_sms_uk">0</div>
                                <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_sms_uk_rev">0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== Other + Grand Total ===== -->
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card h-100 border-left-yelp">
                        <div class="card-body py-2">
                            <div class="rc-type-label"><i class="bi bi-megaphone mr-1"></i> Yelp Ads</div>
                            <div class="rc-big" id="sum_yelp">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                    <div class="card h-100" style="background:#f0edff;border-color:#d4cfff;">
                        <div class="card-body py-2">
                            <div class="rc-type-label"><i class="bi bi-calculator mr-1"></i> Grand Total <span class="sum-meta" id="sumMetaGrand"></span></div>
                            <div class="rc-big" id="sum_grand">0</div>
                            <div class="rc-sub"><span class="rc-label">Revenue:</span> <span id="sum_grand_rev">0</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Summary Active Users (Charge Transactions) <span class="sum-meta" id="sumMetaUsers"></span></h5>
                            <div class="row mt-2">
                                <div class="col text-center">
                                    <div class="rc-label">Stripe US</div>
                                    <div class="rc-big text-success" id="users_stripe_us">0</div>
                                </div>
                                <div class="col text-center">
                                    <div class="rc-label">Stripe AU</div>
                                    <div class="rc-big text-primary" id="users_stripe_au">0</div>
                                </div>
                                <div class="col text-center">
                                    <div class="rc-label">Stripe TH</div>
                                    <div class="rc-big text-danger" id="users_stripe_th">0</div>
                                </div>
                                <div class="col text-center" style="background:#f0edff;border-radius:8px;">
                                    <div class="rc-label">Total</div>
                                    <div class="rc-big" id="users_total">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MoM & YoY side by side -->
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Compare Summary — MoM <span class="sum-meta" id="sumMetaMom"></span></h5>
                            <table class="table table-sm table-bordered rev-compare-table mt-2">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-center" id="sumMomPrev">Prev</th>
                                        <th class="text-center" id="sumMomCur">Current</th>
                                        <th class="text-center">Change</th>
                                    </tr>
                                </thead>
                                <tbody id="sumMomBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Compare Summary — YoY <span class="sum-meta" id="sumMetaYoy"></span></h5>
                            <table class="table table-sm table-bordered rev-compare-table mt-2">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-center" id="sumYoyPrev">Prev</th>
                                        <th class="text-center" id="sumYoyCur">Current</th>
                                        <th class="text-center">Change</th>
                                    </tr>
                                </thead>
                                <tbody id="sumYoyBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Top Spend of Customer <span class="sum-meta" id="sumMetaTop"></span></h5>
                            <table class="table table-sm table-striped table-hover mt-2" style="font-size:12px;">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-right">Count</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>
                                <tbody id="topChargesBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /#tabSummary -->

        <!-- ====== TAB CONTENT: SMS Detail (AU/US/UK) ====== -->
        <div id="tabSmsDetail" style="display:none;">

            <!-- Toolbar -->
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
                            <div>
                                <h5 class="card-title mb-0" id="smsTabTitle"><i class="bi bi-chat-dots mr-1"></i> SMS AU</h5>
                                <span class="rc-label" id="smsPeriodInfo">—</span>
                            </div>
                            <div class="d-flex align-items-center" style="gap:10px;">
                                <span class="rc-label" id="smsBalLabel">Balance: —</span>
                                <button class="btn btn-sm rev-refresh-btn" id="btnSmsRefresh">
                                    <i class="bi bi-arrow-clockwise" id="smsRefreshIcon"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="smsLoading" class="text-center py-4" style="display:none;">
                <div class="spinner-border spinner-border-sm" style="color:#0ea5e9;" role="status"></div>
                <span class="ml-2 text-muted">Loading SMS data...</span>
            </div>

            <div id="smsContent" style="display:none;">
                <!-- Monthly Summary Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <table class="table table-sm table-hover sms-monthly-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-right">SMS Sent</th>
                                            <th class="text-right">SMS Margin</th>
                                            <th class="text-right">VN Earnings</th>
                                            <th class="text-right">KW Earnings</th>
                                            <th class="text-right">Deposits</th>
                                            <th class="text-right">Turnover</th>
                                            <th class="text-right">Profit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="smsMonthlyBody"></tbody>
                                    <tfoot id="smsMonthlyFoot"></tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#smsContent -->

        </div><!-- /#tabSmsDetail -->

        <!-- ====== TAB CONTENT: Stripe Connect ====== -->
        <div id="tabConnect" style="display:none;">

            <!-- Toolbar: Title + Period selector + Datepicker -->
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
                            <div>
                                <h5 class="card-title mb-0"><i class="bi bi-stripe"></i> Stripe Connect</h5>
                                <span class="rc-label" id="conPeriodInfo">—</span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap:10px;">
                                <label class="rc-label mb-0">Period:</label>
                                <select id="conPeriod" class="form-control form-control-sm" style="width:auto;">
                                    <option value="7">Last 7 days</option>
                                    <option value="28" selected>Last 28 days</option>
                                    <option value="90">Last 90 days</option>
                                    <option value="180">Last 180 days</option>
                                    <option value="365">Last 365 days</option>
                                    <option value="9999">All time</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                                <div id="conDateRange" class="d-flex align-items-center" style="gap:6px;display:none;">
                                    <input type="date" id="conDateStart" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <span style="font-size:11px;color:#697386;">to</span>
                                    <input type="date" id="conDateEnd" class="form-control form-control-sm" style="font-size:11px;width:auto;">
                                    <button class="btn btn-sm btn-primary" id="conDateGo" style="font-size:11px;padding:2px 10px;">Go</button>
                                </div>
                                <button class="btn btn-sm rev-refresh-btn" id="btnConRefresh">
                                    <i class="bi bi-arrow-clockwise" id="conRefreshIcon"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading for Connect -->
            <div id="conLoading" class="text-center py-4" style="display:none;">
                <div class="spinner-border spinner-border-sm" style="color:#635bff;" role="status"></div>
                <span class="ml-2 text-muted">Loading Stripe Connect... <span id="conTimer">0.0</span>s</span>
            </div>

            <div id="conContent" style="display:none;">
                <!-- Balance + Payout + Net Volume -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-left-connect">
                            <div class="card-body py-2">
                                <div class="rc-label">Available Balance</div>
                                <div class="rc-big" id="conBalAvail">$0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-left-connect">
                            <div class="card-body py-2">
                                <div class="rc-label">Pending Balance</div>
                                <div class="rc-big" id="conBalPending">$0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-left-connect">
                            <div class="card-body py-2">
                                <div class="rc-label">Payout</div>
                                <div class="rc-big" id="conNextPayout">$0.00</div>
                                <div class="rc-sub" id="conPayoutDate">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body py-2">
                                <div class="rc-label">Net Volume</div>
                                <div class="rc-big" id="conNet">$0.00</div>
                                <div class="rc-sub"><span class="rc-label">Prev:</span> <span id="conNetPrev">$0.00</span> <span id="conNetChg"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Connected Accounts -->
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body py-2">
                                <div class="rc-label">New Connected Accounts</div>
                                <div class="rc-big" id="conNewCust">0</div>
                                <div class="rc-sub"><span class="rc-label">Prev:</span> <span id="conNewCustPrev">0</span> <span id="conNewCustChg"></span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payouts + Top Customers side by side -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Recent Payouts</h5>
                                <table class="table table-sm table-striped table-hover mt-2" style="font-size:12px;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-right">Amount</th>
                                            <th>Status</th>
                                            <th>Arrival</th>
                                        </tr>
                                    </thead>
                                    <tbody id="conPayoutsBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Top Customers by Spend <span class="badge badge-secondary" style="font-size:10px;">All time</span></h5>
                                <table class="table table-sm table-striped table-hover mt-2" style="font-size:12px;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="conTopCustBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /#conContent -->

        </div><!-- /#tabConnect -->

    </div><!-- /#revDashboard -->
</div>

<style>
.revenue-dash { background:#f6f8fa; margin:-15px; padding:0 0 30px; }
.rev-header { background:#fff; padding:12px 20px; border-bottom:1px solid #e3e8ee; }

/* Main tabs */
.rev-main-tabs { background:#fff; padding:0 20px; border-bottom:2px solid #e3e8ee; margin:0; }
.rev-main-tabs .nav-link {
    border:none; border-radius:0; padding:10px 18px; color:#6c757d; font-weight:500; font-size:13px;
    border-bottom:3px solid transparent; transition:all .15s; cursor:pointer;
}
.rev-main-tabs .nav-link:hover { color:#635bff; }
.rev-main-tabs .nav-link.active { color:#635bff; border-bottom-color:#635bff; font-weight:600; }

/* Cards */
.card { border-radius:8px; margin-bottom:12px; border:1px solid #e3e8ee; }
.card-title { font-size:14px; font-weight:700; color:#1a1a2e; }
.border-left-us { border-left:4px solid #198754 !important; }
.border-left-au { border-left:4px solid #0d6efd !important; }
.border-left-th { border-left:4px solid #dc3545 !important; }
.border-left-sms { border-left:4px solid #0ea5e9 !important; }
.border-left-yelp { border-left:4px solid #ef4444 !important; }
.border-left-connect { border-left:4px solid #635bff !important; }
.payout-status { font-size:10px; padding:2px 6px; border-radius:4px; font-weight:600; }
.payout-paid { background:#d1fae5; color:#065f46; }
.payout-pending, .payout-in_transit { background:#fef3c7; color:#92400e; }
.payout-failed { background:#fee2e2; color:#991b1b; }

.rc-label { font-size:11px; color:#697386; }
.rc-big { font-size:20px; font-weight:700; color:#1a1a2e; line-height:1.3; }
.rc-sub { font-size:12px; color:#3c4257; line-height:1.6; }
.rc-type-label { font-size:12px; font-weight:600; color:#3c4257; margin-bottom:4px; }

/* Badges */
.badge-au { background:#0d6efd; color:#fff; font-size:10px; }
.badge-us { background:#198754; color:#fff; font-size:10px; }
.badge-th { background:#dc3545; color:#fff; font-size:10px; }

/* Compare tables */
.rev-compare-table { font-size:12px; }
.rev-compare-table th { font-size:11px; background:#f8f9fa; }
.rev-compare-table .change-up { color:#0e6245; font-weight:600; }
.rev-compare-table .change-down { color:#cd3d64; font-weight:600; }
.rev-compare-table .change-neutral { color:#697386; }

/* Refresh button */
.rev-refresh-btn {
    font-size:12px; padding:5px 14px; border:1px solid #635bff; color:#635bff; background:#fff;
    font-weight:500; border-radius:6px; cursor:pointer; white-space:nowrap; transition:all .15s;
}
.rev-refresh-btn:hover { background:#635bff; color:#fff; }
.rev-refresh-btn:disabled { opacity:0.6; cursor:not-allowed; }
.rev-refresh-btn .spinning { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Type labels */
.type-label { font-weight:600; font-size:12px; }
.type-stripe-au { color:#0d6efd; }
.type-stripe-us { color:#198754; }
.type-stripe-th { color:#dc3545; }
.type-sms { color:#0ea5e9; }
.type-yelp { color:#ef4444; }

#revDashboard .row { padding:0 8px; }

/* Compare mode */
.compare-side-label {
    display:inline-block; width:22px; height:22px; border-radius:50%; color:#fff;
    font-size:11px; font-weight:700; text-align:center; line-height:22px; flex-shrink:0;
}
.compare-picker-card .card-body { padding:10px 16px; }
.cmp-metric-cell { text-align:center; }
.cmp-metric-cell .rc-big { font-size:16px; }

/* Dropdown nav */
.rev-main-tabs .dropdown-toggle::after { margin-left:4px; vertical-align:middle; }
.rev-main-tabs .dropdown-menu { min-width:160px; border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.12); border:1px solid #e3e8ee; padding:4px 0; }
.rev-main-tabs .dropdown-item { font-size:13px; padding:6px 16px; color:#3c4257; }
.rev-main-tabs .dropdown-item:hover { background:#f0edff; color:#635bff; }
.rev-main-tabs .dropdown-item.active { background:#635bff; color:#fff; font-weight:600; }
.dd-sub { font-size:10px; color:#fff; background:#635bff; border-radius:3px; padding:1px 5px; margin-left:2px; font-weight:600; }

/* Source badges for split view */
.source-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; letter-spacing:0.3px; }
.source-monday { background:#f0f4ff; color:#0073ea; border:1px solid #d0daf0; }
.source-stripe { background:#f0edff; color:#635bff; border:1px solid #d6d0f7; }
.split-metric-label { font-size:10px; color:#697386; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px; }
.split-metric-val { font-size:18px; font-weight:700; color:#1a1f36; line-height:1.2; }
.split-metric-sub { font-size:10px; color:#8792a2; margin-top:2px; }
#splitMondayMetrics .split-mon-cell { text-align:center; margin-bottom:8px; }
#splitMondayMetrics .split-mon-cell .rc-label { font-size:10px; }
#splitMondayMetrics .split-mon-cell .rc-big { font-size:16px; }
.btn-xs { padding:1px 6px; font-size:10px; line-height:1.5; border-radius:3px; }
.sp-preset.active { background:#635bff; border-color:#635bff; color:#fff; }
.sp-preset:not(.active):hover { background:#f0edff; color:#635bff; border-color:#635bff; }
.sum-group { margin-bottom:8px; }
.sum-group-header { font-size:14px; font-weight:700; color:#1a1f36; padding:8px 4px 4px; border-bottom:2px solid #e3e8ee; margin-bottom:12px; }
.sum-meta { font-size:10px; font-weight:400; color:#8792a2; margin-left:8px; }

/* SMS monthly table */
.sms-monthly-table { font-size:13px; }
.sms-monthly-table thead th { background:#f8f9fa; border-bottom:2px solid #dee2e6; font-weight:600; font-size:12px; color:#697386; padding:10px 16px; white-space:nowrap; }
.sms-monthly-table tbody td { padding:10px 16px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.sms-monthly-table tbody tr:hover { background:#f8f9fe; }
.sms-monthly-table tfoot td { padding:10px 16px; font-weight:700; background:#f0edff; border-top:2px solid #635bff; }

/* Split view: Stripe metrics */
#splitStripeMetrics .split-mon-cell { text-align:center; margin-bottom:8px; }
#splitStripeMetrics .split-mon-cell .rc-label { font-size:10px; }
#splitStripeMetrics .split-mon-cell .rc-big { font-size:16px; }

/* Sync chain icon */
#syncChainWrap {
    position:absolute; top:18px; left:50%; transform:translateX(-50%); z-index:10;
}
.sync-chain-btn {
    width:32px; height:32px; border-radius:50%; border:2px solid #d0d5dd; background:#fff;
    color:#adb5bd; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center;
    transition:all .2s; box-shadow:0 1px 4px rgba(0,0,0,.08); padding:0; line-height:1;
}
.sync-chain-btn:hover { border-color:#635bff; color:#635bff; }
.sync-chain-btn.active { background:#635bff; border-color:#635bff; color:#fff; box-shadow:0 2px 8px rgba(99,91,255,.3); }
</style>

<script>
window.addEventListener('load', function(){
    var DATA = null;
    var CON_DATA = null;
    var SMS_DATA = {}; // keyed by account: {au:{}, us:{}, uk:{}}
    var STRIPE_DATA = {}; // keyed by account: {au:{}, us:{}, th:{}}
    var smsActiveAccount = null;
    var activeTab = 'stripe_us';
    var stripeAcctMap = { 'stripe_au': 'au', 'stripe_us': 'us', 'stripe_th': 'th' };

    // Currency symbols per type
    var currencyMap = {
        'stripe_au': 'A$', 'stripe_us': '$', 'stripe_th': '฿',
        'sms': '$', 'sms_au': 'A$', 'sms_us': '$', 'sms_uk': '£',
        'yelp': '$', 'summary': '$'
    };

    function getCur(key) { return currencyMap[key] || '$'; }

    function fmt(v, sym) {
        if (v === null || v === undefined) return (sym || '') + '0.00';
        var n = Number(v);
        var neg = n < 0;
        var str = Math.abs(n).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        return (neg ? '-' : '') + (sym || '') + str;
    }
    function fmtInt(v) { return Number(v || 0).toLocaleString(); }

    function changeBadge(pct) {
        if (pct === null || pct === undefined) return '<span class="change-neutral">N/A</span>';
        var cls = pct >= 0 ? 'change-up' : 'change-down';
        var arrow = pct >= 0 ? '&#9650;' : '&#9660;';
        return '<span class="' + cls + '">' + arrow + ' ' + Math.abs(pct) + '%</span>';
    }

    function calcPct(cur, prev) {
        if (!prev || prev === 0) return null;
        return Math.round((cur - prev) / Math.abs(prev) * 1000) / 10;
    }

    function typeLabel(key) {
        var map = {
            'stripe_au': '<span class="type-label type-stripe-au">Stripe AU</span>',
            'stripe_us': '<span class="type-label type-stripe-us">Stripe US</span>',
            'stripe_th': '<span class="type-label type-stripe-th">Stripe TH</span>',
            'sms': '<span class="type-label type-sms">SMS</span>',
            'sms_au': '<span class="type-label type-sms">SMS AU</span>',
            'sms_us': '<span class="type-label type-sms">SMS US</span>',
            'sms_uk': '<span class="type-label type-sms">SMS UK</span>',
            'yelp': '<span class="type-label type-yelp">Yelp Ads</span>'
        };
        return map[key] || key;
    }

    function typeName(key) {
        var map = { 'stripe_au':'Stripe AU', 'stripe_us':'Stripe US', 'stripe_th':'Stripe TH', 'sms':'SMS Marketing', 'sms_au':'SMS AU', 'sms_us':'SMS US', 'sms_uk':'SMS UK', 'yelp':'Yelp Ads' };
        return map[key] || key;
    }

    function acctBadge(key) {
        var map = { 'stripe_au':'<span class="badge badge-au">AU</span>', 'stripe_us':'<span class="badge badge-us">US</span>', 'stripe_th':'<span class="badge badge-th">TH</span>' };
        return map[key] || key;
    }

    // ===== LOAD =====
    var loadStartTime = 0;
    var loadTimerInterval = null;

    function loadSummary(refresh) {
        document.getElementById('revDashboard').style.display = 'none';
        document.getElementById('revError').style.display = 'none';
        document.getElementById('revLoading').style.display = '';
        
        // Start timer
        loadStartTime = Date.now();
        document.getElementById('loadTimer').textContent = '0.0';
        if (loadTimerInterval) clearInterval(loadTimerInterval);
        loadTimerInterval = setInterval(function() {
            var elapsed = ((Date.now() - loadStartTime) / 1000).toFixed(1);
            document.getElementById('loadTimer').textContent = elapsed;
        }, 100);
        
        var params = {};
        if (refresh) params.refresh = '1';

        $.ajax({ url:'api/revenue/getSummary.php', data:params, dataType:'json', timeout:600000 })
        .done(function(d) {
            if (loadTimerInterval) clearInterval(loadTimerInterval);
            var finalTime = ((Date.now() - loadStartTime) / 1000).toFixed(1);
            document.getElementById('loadTimer').textContent = finalTime;
            document.getElementById('revLoading').style.display = 'none';
            if (d.error) {
                document.getElementById('revError').textContent = d.error;
                document.getElementById('revError').style.display = '';
                return;
            }
            DATA = d;
            document.getElementById('revDashboard').style.display = '';
            var srcText = d.source === 'cache' ? 'Cache (' + Math.round((d.cache_age||0)/60) + 'min ago)' : 'Fresh (' + (d.elapsed_seconds||0) + 's)';
            document.getElementById('fetchInfo').textContent = (d.fetched_at || '') + ' | ' + srcText;
            switchTab(activeTab);
        })
        .fail(function(xhr, st, err) {
            if (loadTimerInterval) clearInterval(loadTimerInterval);
            var finalTime = ((Date.now() - loadStartTime) / 1000).toFixed(1);
            document.getElementById('loadTimer').textContent = finalTime;
            document.getElementById('revLoading').style.display = 'none';
            var errMsg = 'Failed: ' + (err || st);
            if (xhr.responseText && xhr.responseText.substring(0, 5) === '<' + '?php') {
                errMsg += ' (API returned PHP code - check syntax error)';
            } else if (xhr.responseText && xhr.responseText.charAt(0) === '<') {
                errMsg += ' (API returned HTML instead of JSON - check error_log)';
            }
            document.getElementById('revError').textContent = errMsg;
            document.getElementById('revError').style.display = '';
            console.error('API Error:', xhr.responseText);
        });
    }

    // SMS tab → account mapping
    var smsTabMap = { 'sms_au': 'au', 'sms_us': 'us', 'sms_uk': 'uk' };
    var smsLabelMap = { 'au': 'SMS AU', 'us': 'SMS US', 'uk': 'SMS UK' };

    // ===== SWITCH TAB =====
    function switchTab(tab) {
        activeTab = tab;
        document.getElementById('tabSingle').style.display = 'none';
        document.getElementById('tabSummary').style.display = 'none';
        document.getElementById('tabConnect').style.display = 'none';
        document.getElementById('tabSmsDetail').style.display = 'none';

        if (tab === 'summary') {
            if (!DATA) return;
            document.getElementById('tabSummary').style.display = '';
            renderSummaryTab();
        } else if (tab === 'connect') {
            document.getElementById('tabConnect').style.display = '';
            if (!CON_DATA) loadConnect();
        } else if (smsTabMap[tab]) {
            var acct = smsTabMap[tab];
            smsActiveAccount = acct;
            document.getElementById('tabSmsDetail').style.display = '';
            document.getElementById('smsTabTitle').innerHTML = '<i class="bi bi-chat-dots mr-1"></i> ' + smsLabelMap[acct];
            if (!SMS_DATA[acct]) {
                loadSmsData(acct);
            } else {
                renderSmsTab(acct);
            }
        } else {
            if (!DATA) return;
            document.getElementById('tabSingle').style.display = '';
            renderSingleTab(tab);
        }
    }

    // ===== RENDER SINGLE TAB (stripe_us, stripe_au, stripe_th, sms, yelp) =====
    function renderSingleTab(key) {
        var rev = DATA.revenue || {};
        var r = rev[key] || {};
        var isStripe = key.indexOf('stripe') === 0;
        var cs = getCur(key);

        // Title + date range
        document.getElementById('tabTitle').textContent = typeName(key);
        var months = Object.keys(r.by_month || {}).sort();
        if (months.length > 0) {
            var first = months[0], last = months[months.length - 1];
            document.getElementById('tabDateRange').textContent = 'Data: ' + monthLabel(first) + ' — ' + monthLabel(last) + '  (' + months.length + ' months)';
        } else {
            document.getElementById('tabDateRange').textContent = 'No data available';
        }

        // Reset mode selector to current state
        var mode = document.getElementById('tabMode').value;
        if (mode === 'compare') {
            document.getElementById('stripeSplitView').style.display = 'none';
            document.getElementById('modeAllTime').style.display = 'none';
            document.getElementById('modeCompare').style.display = '';
            initCompareDefaults(key);
            renderCompareMode(key);
            return;
        }

        document.getElementById('modeCompare').style.display = 'none';

        // Stripe tabs: split view (Monday.com left, Stripe API right)
        var isYelp = (key === 'yelp');
        if (isStripe) {
            document.getElementById('modeAllTime').style.display = 'none';
            document.getElementById('stripeSplitView').style.display = '';
            document.getElementById('yelpSplitView').style.display = 'none';
            var acct = stripeAcctMap[key];
            if (acct) {
                initSplitStripeDates();
                var params = getSplitStripeParams(acct);
                var cacheKey = params._cacheKey;
                if (STRIPE_DATA[cacheKey]) {
                    renderStripeApiPanel(STRIPE_DATA[cacheKey], cs);
                } else {
                    loadStripeApiData(acct, params, cs);
                }
            }
        } else if (isYelp) {
            document.getElementById('modeAllTime').style.display = 'none';
            document.getElementById('stripeSplitView').style.display = 'none';
            document.getElementById('yelpSplitView').style.display = '';
            initYelpMondayDatepicker(r);
            renderYelpMonday(r, cs);
            initYelpFileList();
        } else {
            document.getElementById('stripeSplitView').style.display = 'none';
            document.getElementById('yelpSplitView').style.display = 'none';
            document.getElementById('modeAllTime').style.display = '';
        }

        // Overview numbers (for non-Stripe / modeAllTime)
        document.getElementById('tabAmount').textContent = fmt(r.amount, cs);
        document.getElementById('tabRevenue').textContent = fmt(r.revenue, cs);
        document.getElementById('tabFee').textContent = fmt(r.fee, cs);
        document.getElementById('tabNet').textContent = fmt(r.net, cs);
        document.getElementById('tabCharges').textContent = fmtInt(r.charge_count);
        document.getElementById('tabItems').textContent = fmtInt(r.count);

        // MoM for this type
        var mom = DATA.mom || {};
        var momType = (mom.by_type || {})[key] || {};
        document.getElementById('tabMomPrev').textContent = mom.previous_month || 'Prev';
        document.getElementById('tabMomCur').textContent = mom.current_month || 'Current';

        var momPrev = momType.previous || {};
        var momCur = momType.current || {};
        var momHtml = '';
        if (isStripe) {
            var metrics = [
                {label:'Amount', prev: momPrev.amount||0, cur: momCur.amount||0},
                {label:'Fee', prev: momPrev.fee||0, cur: momCur.fee||0},
                {label:'Net', prev: momPrev.net||0, cur: momCur.net||0},
                {label:'Revenue', prev: momPrev.revenue||0, cur: momCur.revenue||0},
                {label:'Charges', prev: momPrev.charge_count||0, cur: momCur.charge_count||0}
            ];
        } else {
            var metrics = [
                {label:'Revenue', prev: momPrev.revenue||0, cur: momCur.revenue||0},
                {label:'Items', prev: momPrev.count||0, cur: momCur.count||0}
            ];
        }
        metrics.forEach(function(m) {
            var isMoney = m.label !== 'Charges' && m.label !== 'Items';
            momHtml += '<tr><td>' + m.label + '</td>'
                + '<td class="text-right">' + (isMoney ? fmt(m.prev, cs) : fmtInt(m.prev)) + '</td>'
                + '<td class="text-right">' + (isMoney ? fmt(m.cur, cs) : fmtInt(m.cur)) + '</td>'
                + '<td class="text-center">' + changeBadge(calcPct(m.cur, m.prev)) + '</td></tr>';
        });
        document.getElementById('tabMomBody').innerHTML = momHtml;

        // YoY for this type
        var yoy = DATA.yoy || {};
        var yoyType = (yoy.by_type || {})[key] || {};
        document.getElementById('tabYoyPrev').textContent = yoy.previous_year || 'Prev';
        document.getElementById('tabYoyCur').textContent = yoy.current_year || 'Current';

        var yoyPrev = yoyType.previous || {};
        var yoyCur = yoyType.current || {};
        var yoyHtml = '';
        if (isStripe) {
            var yMetrics = [
                {label:'Amount', prev: yoyPrev.amount||0, cur: yoyCur.amount||0},
                {label:'Fee', prev: yoyPrev.fee||0, cur: yoyCur.fee||0},
                {label:'Net', prev: yoyPrev.net||0, cur: yoyCur.net||0},
                {label:'Revenue', prev: yoyPrev.revenue||0, cur: yoyCur.revenue||0},
                {label:'Charges', prev: yoyPrev.charge_count||0, cur: yoyCur.charge_count||0}
            ];
        } else {
            var yMetrics = [
                {label:'Revenue', prev: yoyPrev.revenue||0, cur: yoyCur.revenue||0},
                {label:'Items', prev: yoyPrev.count||0, cur: yoyCur.count||0}
            ];
        }
        yMetrics.forEach(function(m) {
            var isMoney = m.label !== 'Charges' && m.label !== 'Items';
            yoyHtml += '<tr><td>' + m.label + '</td>'
                + '<td class="text-right">' + (isMoney ? fmt(m.prev, cs) : fmtInt(m.prev)) + '</td>'
                + '<td class="text-right">' + (isMoney ? fmt(m.cur, cs) : fmtInt(m.cur)) + '</td>'
                + '<td class="text-center">' + changeBadge(calcPct(m.cur, m.prev)) + '</td></tr>';
        });
        document.getElementById('tabYoyBody').innerHTML = yoyHtml;

        // Monthly breakdown for this type (non-Stripe only uses this)
        if (!isStripe) renderTabMonthly(r.by_month || {}, 'month');
    }

    // ===== Monday.com Datepicker =====
    var monFilterStart = null; // e.g. "2025-01"
    var monFilterEnd = null;

    function initMondayDatepicker(key, r) {
        var months = Object.keys(r.by_month || {}).sort();
        var elS = document.getElementById('monDateStart');
        var elE = document.getElementById('monDateEnd');
        if (months.length > 0) {
            elS.min = months[0];
            elS.max = months[months.length - 1];
            elE.min = months[0];
            elE.max = months[months.length - 1];
            if (!monFilterStart) { elS.value = months[0]; elE.value = months[months.length - 1]; }
            else { elS.value = monFilterStart; elE.value = monFilterEnd; }
        }
    }

    // ===== SPLIT VIEW: Monday.com left column =====
    function renderSplitMonday(key, r, cs) {
        var byMonth = r.by_month || {};

        // Apply date filter
        var filteredByMonth = {};
        var filterStart = monFilterStart || '';
        var filterEnd = monFilterEnd || '';
        Object.keys(byMonth).forEach(function(mk) {
            if (filterStart && mk < filterStart) return;
            if (filterEnd && mk > filterEnd) return;
            filteredByMonth[mk] = byMonth[mk];
        });

        // Calculate filtered totals
        var totals = { amount:0, revenue:0, fee:0, net:0, charge_count:0, count:0 };
        Object.keys(filteredByMonth).forEach(function(mk) {
            var m = filteredByMonth[mk];
            totals.amount += m.amount || 0;
            totals.revenue += m.revenue || 0;
            totals.fee += m.fee || 0;
            totals.net += m.net || 0;
            totals.charge_count += m.charge_count || 0;
            totals.count += m.count || 0;
        });

        // Overview metrics
        var metricsHtml = '';
        var mDefs = [
            {label:'Amount', val: totals.amount},
            {label:'Revenue', val: totals.revenue},
            {label:'Fee', val: totals.fee},
            {label:'Net', val: totals.net},
            {label:'Charges', val: totals.charge_count, isInt:true},
            {label:'Items', val: totals.count, isInt:true}
        ];
        mDefs.forEach(function(d) {
            metricsHtml += '<div class="col-4 split-mon-cell">'
                + '<div class="rc-label">' + d.label + '</div>'
                + '<div class="rc-big">' + (d.isInt ? fmtInt(d.val) : fmt(d.val, cs)) + '</div>'
                + '</div>';
        });
        document.getElementById('splitMondayMetrics').innerHTML = metricsHtml;

        // Monthly table
        var mKeys = Object.keys(filteredByMonth).sort().reverse();
        var html = '';
        mKeys.forEach(function(mk) {
            var m = filteredByMonth[mk];
            html += '<tr>'
                + '<td>' + monthLabel(mk) + '</td>'
                + '<td class="text-right">' + fmt(m.amount, cs) + '</td>'
                + '<td class="text-right">' + fmt(m.fee, cs) + '</td>'
                + '<td class="text-right">' + fmt(m.revenue, cs) + '</td>'
                + '<td class="text-right">' + fmtInt(m.charge_count) + '</td>'
                + '</tr>';
        });
        if (!html) html = '<tr><td colspan="5" class="text-center text-muted">No data</td></tr>';
        document.getElementById('splitMondayMonthly').innerHTML = html;
    }

    // ===== SPLIT VIEW: Date helpers =====
    var spActiveDays = 28; // track which preset is active (0 = custom)

    function initSplitStripeDates() {
        var elS = document.getElementById('spDateStart');
        var elE = document.getElementById('spDateEnd');
        if (elS.value && elE.value) return; // already set
        var now = new Date();
        elE.value = now.toISOString().slice(0, 10);
        var from = new Date(now);
        from.setDate(from.getDate() - (spActiveDays >= 9999 ? 365*5 : spActiveDays));
        elS.value = from.toISOString().slice(0, 10);
    }

    function getSplitStripeParams(acct) {
        var elS = document.getElementById('spDateStart');
        var elE = document.getElementById('spDateEnd');
        if (spActiveDays > 0) {
            return { account: acct, days: spActiveDays, _cacheKey: acct + '_' + spActiveDays };
        }
        return { account: acct, start: elS.value, end: elE.value, _cacheKey: acct + '_' + elS.value + '_' + elE.value };
    }

    function setSplitStripeDatesFromPreset(days) {
        spActiveDays = days;
        var now = new Date();
        document.getElementById('spDateEnd').value = now.toISOString().slice(0, 10);
        var from = new Date(now);
        from.setDate(from.getDate() - (days >= 9999 ? 365*5 : days));
        document.getElementById('spDateStart').value = from.toISOString().slice(0, 10);
        // Update active button
        document.querySelectorAll('.sp-preset').forEach(function(b) {
            b.classList.toggle('active', parseInt(b.getAttribute('data-days')) === days);
        });
    }

    // ===== SPLIT VIEW: Load Stripe API data (uses getTransactions.php) =====
    var spTimerInterval = null;
    function loadStripeApiData(acct, params, cs) {
        var spStart = Date.now();
        document.getElementById('splitStripeLoading').innerHTML = '<div class="spinner-border spinner-border-sm" style="color:#635bff;" role="status"></div><span class="ml-2 text-muted" style="font-size:12px;">Loading Stripe data... <span id="spTimer">0.0</span>s</span>';
        document.getElementById('splitStripeLoading').style.display = '';
        document.getElementById('splitStripeContent').style.display = 'none';
        if (spTimerInterval) clearInterval(spTimerInterval);
        spTimerInterval = setInterval(function() {
            var el = document.getElementById('spTimer');
            if (el) el.textContent = ((Date.now() - spStart) / 1000).toFixed(1);
        }, 100);

        var ajaxData = { account: params.account };
        if (params.start && params.end) {
            ajaxData.start = params.start;
            ajaxData.end = params.end;
        } else {
            ajaxData.days = params.days;
        }

        $.ajax({ url:'api/stripe/getTransactions.php', data: ajaxData, dataType:'json', timeout:180000 })
        .done(function(d) {
            if (spTimerInterval) clearInterval(spTimerInterval);
            if (d.error) {
                document.getElementById('splitStripeLoading').innerHTML = '<span class="text-danger" style="font-size:12px;">Error: ' + d.error + '</span>';
                return;
            }
            STRIPE_DATA[params._cacheKey] = d;
            renderStripeApiPanel(d, cs);
        })
        .fail(function(xhr, st, err) {
            if (spTimerInterval) clearInterval(spTimerInterval);
            document.getElementById('splitStripeLoading').innerHTML = '<span class="text-danger" style="font-size:12px;">Failed: ' + (err || st) + '</span>';
        });
    }

    // ===== SPLIT VIEW: Render Stripe API right column (Monday-like format) =====
    function renderStripeApiPanel(d, cs) {
        document.getElementById('splitStripeLoading').style.display = 'none';
        document.getElementById('splitStripeContent').style.display = '';

        var sum = d.summary || {};

        // Metrics (same layout as Monday left panel)
        var metricsHtml = '';
        var sDefs = [
            {label:'Amount', val: sum.total_amount},
            {label:'Revenue', val: sum.total_revenue},
            {label:'Fee', val: sum.total_fee},
            {label:'Net', val: sum.total_net},
            {label:'Charges', val: sum.charge_count, isInt:true},
            {label:'Items', val: d.total_count, isInt:true},
            {label:'Refunds', val: sum.refund_count, isInt:true},
            {label:'Refund Total', val: sum.refund_total}
        ];
        sDefs.forEach(function(dd) {
            metricsHtml += '<div class="col-4 split-mon-cell">'
                + '<div class="rc-label">' + dd.label + '</div>'
                + '<div class="rc-big">' + (dd.isInt ? fmtInt(dd.val) : fmt(dd.val, cs)) + '</div>'
                + '</div>';
        });
        document.getElementById('splitStripeMetrics').innerHTML = metricsHtml;

        // Monthly breakdown table
        var byMonth = d.by_month || {};
        var mKeys = Object.keys(byMonth).sort().reverse();
        var html = '';
        mKeys.forEach(function(mk) {
            var m = byMonth[mk];
            html += '<tr>'
                + '<td>' + monthLabel(mk) + '</td>'
                + '<td class="text-right">' + fmt(m.amount, cs) + '</td>'
                + '<td class="text-right">' + fmt(m.fee, cs) + '</td>'
                + '<td class="text-right">' + fmt(m.revenue, cs) + '</td>'
                + '<td class="text-right">' + fmtInt(m.charge_count) + '</td>'
                + '<td class="text-right text-danger">' + (m.refund ? fmt(m.refund, cs) : '-') + '</td>'
                + '</tr>';
        });
        if (!html) html = '<tr><td colspan="6" class="text-center text-muted">No data</td></tr>';
        document.getElementById('splitStripeMonthly').innerHTML = html;

        // Period info
        document.getElementById('spPeriodInfo').textContent = (d.period_start||'') + ' — ' + (d.period_end||'') + ' | ' + (d.total_count||0) + ' txns | Fetched: ' + (d.fetched_at||'');
    }

    // Format month key "2025-03" → "Mar 2025"
    function monthLabel(mk) {
        var parts = mk.split('-');
        var mNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return mNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
    }

    // Set default month picker values when entering compare mode
    function initCompareDefaults(key) {
        var r = (DATA.revenue || {})[key] || {};
        var months = Object.keys(r.by_month || {}).sort();
        if (months.length === 0) return;
        var last = months[months.length - 1];
        var prev = months.length >= 2 ? months[months.length - 2] : last;
        var elA = document.getElementById('cmpMonthA');
        var elB = document.getElementById('cmpMonthB');
        // Set min/max range
        elA.min = months[0]; elA.max = last;
        elB.min = months[0]; elB.max = last;
        // Default: previous month (A) vs latest month (B)
        if (!elA.value || !r.by_month[elA.value]) elA.value = prev;
        if (!elB.value || !r.by_month[elB.value]) elB.value = last;
    }

    // ===== RENDER COMPARE MODE =====
    function renderCompareMode(key) {
        var rev = DATA.revenue || {};
        var r = rev[key] || {};
        var byMonth = r.by_month || {};
        var isStripe = key.indexOf('stripe') === 0;
        var cs = getCur(key);

        var mkA = document.getElementById('cmpMonthA').value;
        var mkB = document.getElementById('cmpMonthB').value;
        var dA = byMonth[mkA] || {amount:0,fee:0,net:0,revenue:0,charge_count:0,count:0};
        var dB = byMonth[mkB] || {amount:0,fee:0,net:0,revenue:0,charge_count:0,count:0};

        // Labels
        var labelA = mkA ? monthLabel(mkA) : '—';
        var labelB = mkB ? monthLabel(mkB) : '—';
        document.getElementById('cmpLabelA').textContent = labelA;
        document.getElementById('cmpLabelB').textContent = labelB;
        document.getElementById('cmpThA').textContent = labelA;
        document.getElementById('cmpThB').textContent = labelB;

        // Build metric list based on type
        var metricDefs;
        if (isStripe) {
            metricDefs = [
                {label:'Amount', k:'amount', money:true},
                {label:'Fee', k:'fee', money:true},
                {label:'Net', k:'net', money:true},
                {label:'Revenue', k:'revenue', money:true},
                {label:'Charges', k:'charge_count', money:false},
                {label:'Items', k:'count', money:false}
            ];
        } else {
            metricDefs = [
                {label:'Revenue', k:'revenue', money:true},
                {label:'Items', k:'count', money:false}
            ];
        }

        // Side A card
        var cardHtml = function(data) {
            var h = '';
            metricDefs.forEach(function(md) {
                h += '<div class="col cmp-metric-cell">'
                    + '<div class="rc-label">' + md.label + '</div>'
                    + '<div class="rc-big">' + (md.money ? fmt(data[md.k], cs) : fmtInt(data[md.k])) + '</div>'
                    + '</div>';
            });
            return h;
        };
        document.getElementById('cmpCardA').innerHTML = cardHtml(dA);
        document.getElementById('cmpCardB').innerHTML = cardHtml(dB);

        // Comparison table
        var html = '';
        metricDefs.forEach(function(md) {
            var vA = dA[md.k] || 0;
            var vB = dB[md.k] || 0;
            var diff = vB - vA;
            var pct = calcPct(vB, vA);
            html += '<tr>'
                + '<td class="font-weight-bold">' + md.label + '</td>'
                + '<td class="text-right">' + (md.money ? fmt(vA, cs) : fmtInt(vA)) + '</td>'
                + '<td class="text-right">' + (md.money ? fmt(vB, cs) : fmtInt(vB)) + '</td>'
                + '<td class="text-right">' + (md.money ? fmt(diff, cs) : fmtInt(diff)) + '</td>'
                + '<td class="text-center">' + changeBadge(pct) + '</td>'
                + '</tr>';
        });
        document.getElementById('cmpTableBody').innerHTML = html;
    }

    function renderTabMonthly(byMonth, view) {
        var rows = {};
        Object.keys(byMonth).forEach(function(mk) {
            var period = view === 'year' ? mk.substring(0,4) : mk;
            if (!rows[period]) rows[period] = {amount:0, fee:0, net:0, revenue:0, charge_count:0, count:0};
            var m = byMonth[mk];
            rows[period].amount += m.amount||0;
            rows[period].fee += m.fee||0;
            rows[period].net += m.net||0;
            rows[period].revenue += m.revenue||0;
            rows[period].charge_count += m.charge_count||0;
            rows[period].count += m.count||0;
        });
        var sorted = Object.keys(rows).sort().reverse();
        var html = '';
        var cs = getCur(activeTab);
        sorted.forEach(function(p) {
            var r = rows[p];
            html += '<tr><td class="font-weight-bold">' + p + '</td>'
                + '<td class="text-right">' + fmt(r.amount, cs) + '</td>'
                + '<td class="text-right">' + fmt(r.fee, cs) + '</td>'
                + '<td class="text-right">' + fmt(r.net, cs) + '</td>'
                + '<td class="text-right">' + fmt(r.revenue, cs) + '</td>'
                + '<td class="text-right">' + fmtInt(r.charge_count) + '</td>'
                + '<td class="text-right">' + fmtInt(r.count) + '</td></tr>';
        });
        document.getElementById('tabMonthlyBody').innerHTML = html;
    }

    // ===== GRAND TOTAL (client-side) =====
    function updateGrandTotal() {
        var rates = DATA.exchange_rates_to_aud || {};
        var rUSD = rates.stripe_us || 1.55;
        var rTHB = rates.stripe_th || 0.045;
        var rGBP = rates.sms_uk || 1.95;

        var rev = DATA.revenue || {};
        var totalAud = 0;
        var totalRevAud = 0;

        // Stripe AU/US/TH — use Stripe API summary if loaded, fallback to Monday
        function stripeAmt(acct) {
            var sd = (STRIPE_SUM_DATA[acct]||{}).summary;
            if (sd) return sd.total_amount || 0;
            return (rev['stripe_' + acct]||{}).amount || 0;
        }
        function stripeRev(acct) {
            var sd = (STRIPE_SUM_DATA[acct]||{}).summary;
            if (sd) return sd.total_revenue || 0;
            return (rev['stripe_' + acct]||{}).revenue || 0;
        }
        // Stripe AU (AUD)
        totalAud    += stripeAmt('au') * 1;
        totalRevAud += stripeRev('au') * 1;
        // Stripe US (USD)
        totalAud    += stripeAmt('us') * rUSD;
        totalRevAud += stripeRev('us') * rUSD;
        // Stripe TH (THB)
        totalAud    += stripeAmt('th') * rTHB;
        totalRevAud += stripeRev('th') * rTHB;

        // Stripe Connect — from Stripe API (currency from API response)
        if (CON_DATA) {
            var conCur = (CON_DATA.currencyCode||'AUD').toUpperCase();
            var conRate = conCur === 'AUD' ? 1 : conCur === 'USD' ? rUSD : conCur === 'THB' ? rTHB : conCur === 'GBP' ? rGBP : 1;
            var conGross = (CON_DATA.grossVolume||{}).total||0;
            var conNet   = (CON_DATA.netVolume||{}).total||0;
            totalAud    += conGross * conRate;
            totalRevAud += conNet * conRate;
        }

        // SMS AU (AUD) — from TransmitSMS API
        if (SMS_DATA.au) {
            totalAud    += ((SMS_DATA.au.totals||{}).revenue||0) * 1;
            totalRevAud += ((SMS_DATA.au.totals||{}).cost||0) * 1;
        }
        // SMS US (USD)
        if (SMS_DATA.us) {
            totalAud    += ((SMS_DATA.us.totals||{}).revenue||0) * rUSD;
            totalRevAud += ((SMS_DATA.us.totals||{}).cost||0) * rUSD;
        }
        // SMS UK (GBP)
        if (SMS_DATA.uk) {
            totalAud    += ((SMS_DATA.uk.totals||{}).revenue||0) * rGBP;
            totalRevAud += ((SMS_DATA.uk.totals||{}).cost||0) * rGBP;
        }

        // Yelp (USD) — uses revenue field
        totalAud    += ((rev.yelp||{}).revenue||0) * rUSD;
        totalRevAud += ((rev.yelp||{}).revenue||0) * rUSD;

        document.getElementById('sum_grand').textContent = fmt(totalAud, 'A$');
        document.getElementById('sum_grand_rev').textContent = fmt(totalRevAud, 'A$');
        document.getElementById('sumMetaGrand').textContent = 'Converted to AUD (1 USD=' + rUSD + ', 1 THB=' + rTHB + ', 1 GBP=' + rGBP + ')';
    }

    // ===== RENDER SUMMARY TAB =====
    var sumTypeOrder = ['stripe_au','stripe_us','stripe_th','sms_au','sms_us','sms_uk','yelp'];
    var smsSumCurMap = { au:'A$', us:'$', uk:'£' };
    var SMS_SUM_LOADED = {}; // track which sms accounts loaded for summary

    // Helper: get revenue for a month from SMS_DATA monthly array
    function smsMonthRevenue(acct, monthKey) {
        var d = SMS_DATA[acct];
        if (!d || !d.monthly) return 0;
        for (var i = 0; i < d.monthly.length; i++) {
            if (d.monthly[i].month === monthKey) return d.monthly[i].revenue || 0;
        }
        return 0;
    }

    // Helper: get year total revenue from SMS_DATA
    function smsYearRevenue(acct, year) {
        var d = SMS_DATA[acct];
        if (!d || !d.monthly) return 0;
        var sum = 0;
        d.monthly.forEach(function(m) { if (m.month && m.month.indexOf(year) === 0) sum += (m.revenue||0); });
        return sum;
    }

    // Helper: date range from SMS_DATA monthly
    function smsDateRange(acct) {
        var d = SMS_DATA[acct];
        if (!d || !d.monthly || !d.monthly.length) return null;
        var months = d.monthly.map(function(m){ return m.month; }).sort();
        return { from: months[0], to: months[months.length - 1] };
    }

    // Summary-scoped Stripe API cache (all-time per account)
    var STRIPE_SUM_DATA = {}; // { au:{}, us:{}, th:{} }
    var STRIPE_SUM_LOADED = {};

    function renderSummaryTab() {
        var rev = DATA.revenue || {};
        var gt = DATA.grand_total || {};
        var mom = DATA.mom || {};
        var yoy = DATA.yoy || {};

        // --- Stripe metadata ---
        document.getElementById('sumMetaStripe').textContent = 'Loading from Stripe API...';

        // Stripe cards: load from Stripe API (all time) per account
        var stripeAccts = ['au','us','th'];
        var stripeSymMap = { au:'A$', us:'$', th:'฿' };
        stripeAccts.forEach(function(a) {
            var el = document.getElementById('sum_stripe_' + a);
            var elRev = document.getElementById('sum_stripe_' + a + '_rev');
            if (STRIPE_SUM_DATA[a]) {
                renderStripeSumCard(a);
                STRIPE_SUM_LOADED[a] = true;
                checkAllStripeSumLoaded();
            } else {
                el.textContent = '...';
                elRev.textContent = '...';
                $.ajax({ url:'api/stripe/getTransactions.php', data:{ account:a, days:9999 }, dataType:'json', timeout:300000 })
                .done(function(d) {
                    if (d.error) { el.textContent = 'Err'; STRIPE_SUM_LOADED[a] = true; checkAllStripeSumLoaded(); return; }
                    STRIPE_SUM_DATA[a] = d;
                    renderStripeSumCard(a);
                    STRIPE_SUM_LOADED[a] = true;
                    checkAllStripeSumLoaded();
                })
                .fail(function() { el.textContent = '—'; STRIPE_SUM_LOADED[a] = true; checkAllStripeSumLoaded(); });
            }
        });

        // Connect card
        if (CON_DATA) {
            renderConnectCard();
        } else {
            document.getElementById('sum_connect').textContent = '...';
            document.getElementById('sum_connect_rev').textContent = '...';
            document.getElementById('sum_connect_fee').textContent = '...';
            document.getElementById('sum_connect_net').textContent = '...';
            document.getElementById('sum_connect_charges').textContent = '...';
            document.getElementById('sum_connect_items').textContent = '...';
            document.getElementById('sum_connect_period').textContent = '';
            $.ajax({ url:'api/stripe/getData.php', data:{ account:'connect', days:28 }, dataType:'json', timeout:120000 })
            .done(function(d) {
                if (d.error) { document.getElementById('sum_connect').textContent = 'Error'; return; }
                CON_DATA = d;
                renderConnectCard();
                updateGrandTotal();
            })
            .fail(function() { document.getElementById('sum_connect').textContent = '—'; });
        }

        // Yelp
        document.getElementById('sum_yelp').textContent = fmt((rev.yelp||{}).revenue, '$');

        // Grand Total — computed client-side after all sources loaded
        updateGrandTotal();

        // Active users — populated from Stripe API via checkAllStripeSumLoaded
        document.getElementById('users_stripe_us').textContent = '...';
        document.getElementById('users_stripe_au').textContent = '...';
        document.getElementById('users_stripe_th').textContent = '...';
        document.getElementById('users_total').textContent = '...';
        document.getElementById('sumMetaUsers').textContent = 'Loading from Stripe API...';

        // Top charges
        var topHtml = '';
        (DATA.top_charges || []).forEach(function(tc, i) {
            topHtml += '<tr><td>' + (i+1) + '</td>'
                + '<td>' + acctBadge(tc.account) + '</td>'
                + '<td>' + (tc.description || '—') + '</td>'
                + '<td class="text-right font-weight-bold">' + fmt(tc.amount, getCur(tc.account)) + '</td>'
                + '<td class="text-right">' + tc.count + '</td>'
                + '<td style="font-size:10px;color:#8792a2;">' + (tc.source || '') + '</td></tr>';
        });
        if (!topHtml) topHtml = '<tr><td colspan="6" class="text-center text-muted">No data</td></tr>';
        document.getElementById('topChargesBody').innerHTML = topHtml;
        document.getElementById('sumMetaTop').textContent = 'Source: Monday.com (all-time aggregated)';

        // --- SMS: auto-load from TransmitSMS API ---
        var smsAccts = ['au','us','uk'];
        document.getElementById('sumMetaSms').textContent = 'Loading from TransmitSMS API...';
        smsAccts.forEach(function(a) {
            var el = document.getElementById('sum_sms_' + a);
            var elRev = document.getElementById('sum_sms_' + a + '_rev');
            if (SMS_DATA[a]) {
                el.textContent = fmt((SMS_DATA[a].totals||{}).revenue, smsSumCurMap[a]);
                elRev.textContent = fmt((SMS_DATA[a].totals||{}).cost, smsSumCurMap[a]);
                SMS_SUM_LOADED[a] = true;
                checkAllSmsLoaded();
            } else {
                el.textContent = '...';
                elRev.textContent = '...';
                $.ajax({ url:'api/sms/getData.php', data:{ account: a }, dataType:'json', timeout:60000 })
                .done(function(d) {
                    if (d.error) { el.textContent = 'Err'; return; }
                    SMS_DATA[a] = d;
                    el.textContent = fmt((d.totals||{}).revenue, smsSumCurMap[a]);
                    elRev.textContent = fmt((d.totals||{}).cost, smsSumCurMap[a]);
                    SMS_SUM_LOADED[a] = true;
                    checkAllSmsLoaded();
                })
                .fail(function() { el.textContent = '—'; SMS_SUM_LOADED[a] = true; checkAllSmsLoaded(); });
            }
        });

        // Render MoM/YoY initially without SMS, will rebuild after SMS loads
        renderMomYoy();
    }

    function renderConnectCard() {
        var d = CON_DATA;
        var cs = d.currencySymbol || '$';
        var gv = d.grossVolume || {};
        var nv = d.netVolume || {};
        var pay = d.payments || {};
        var fee = d.feeTotal || 0;
        var chargeCount = (pay.succeeded_count || 0);
        var itemCount = (pay.succeeded_count || 0) + (pay.failed_count || 0);

        document.getElementById('sum_connect').textContent = fmt(gv.total, cs);
        document.getElementById('sum_connect_rev').textContent = fmt(gv.total, cs);
        document.getElementById('sum_connect_fee').textContent = fmt(fee, cs);
        document.getElementById('sum_connect_net').textContent = fmt(nv.total, cs);
        document.getElementById('sum_connect_charges').textContent = fmtInt(chargeCount);
        document.getElementById('sum_connect_items').textContent = fmtInt(itemCount);

        // Period subtitle
        var p = d.period || {};
        var periodText = '';
        var sel = document.getElementById('conPeriod').value;
        if (sel === 'custom') {
            var s = document.getElementById('conDateStart').value;
            var e = document.getElementById('conDateEnd').value;
            if (s && e) periodText = s + ' — ' + e;
        }
        if (!periodText) {
            var labelMap = {'7':'Last 7 days','28':'Last 28 days','90':'Last 90 days','180':'Last 180 days','365':'Last 365 days','9999':'All time','custom':'Custom'};
            periodText = labelMap[sel] || ((p.start||'') + ' — ' + (p.end||''));
        }
        document.getElementById('sum_connect_period').textContent = periodText;
    }

    // ===== Stripe Summary helpers (data from Stripe API) =====
    function renderStripeSumCard(acct) {
        var d = STRIPE_SUM_DATA[acct];
        if (!d) return;
        var sym = { au:'A$', us:'$', th:'฿' }[acct] || '$';
        var sum = d.summary || {};
        document.getElementById('sum_stripe_' + acct).textContent = fmt(sum.total_amount, sym);
        document.getElementById('sum_stripe_' + acct + '_rev').textContent = fmt(sum.total_revenue, sym);
    }

    function stripeSumMonthRevenue(acct, monthKey) {
        var d = STRIPE_SUM_DATA[acct];
        if (!d || !d.by_month || !d.by_month[monthKey]) return { amount:0, fee:0, net:0, revenue:0, charge_count:0 };
        return d.by_month[monthKey];
    }

    function stripeSumYearAggr(acct, year) {
        var d = STRIPE_SUM_DATA[acct];
        var out = { amount:0, fee:0, net:0, revenue:0, charge_count:0 };
        if (!d || !d.by_month) return out;
        Object.keys(d.by_month).forEach(function(mk) {
            if (mk.indexOf(year) !== 0) return;
            var m = d.by_month[mk];
            out.amount += m.amount || 0;
            out.fee += m.fee || 0;
            out.net += m.net || 0;
            out.revenue += m.revenue || 0;
            out.charge_count += m.charge_count || 0;
        });
        return out;
    }

    function checkAllStripeSumLoaded() {
        if (!STRIPE_SUM_LOADED.au || !STRIPE_SUM_LOADED.us || !STRIPE_SUM_LOADED.th) return;
        // Update metadata
        var fetched = [];
        ['au','us','th'].forEach(function(a) {
            if (STRIPE_SUM_DATA[a] && STRIPE_SUM_DATA[a].fetched_at) fetched.push(a.toUpperCase() + ':' + STRIPE_SUM_DATA[a].fetched_at);
        });
        document.getElementById('sumMetaStripe').textContent = 'Source: Stripe API' + (fetched.length ? ' | ' + fetched.join(' | ') : '');

        // Active users = Stripe charge_count
        var auC = ((STRIPE_SUM_DATA.au||{}).summary||{}).charge_count || 0;
        var usC = ((STRIPE_SUM_DATA.us||{}).summary||{}).charge_count || 0;
        var thC = ((STRIPE_SUM_DATA.th||{}).summary||{}).charge_count || 0;
        document.getElementById('users_stripe_us').textContent = fmtInt(usC);
        document.getElementById('users_stripe_au').textContent = fmtInt(auC);
        document.getElementById('users_stripe_th').textContent = fmtInt(thC);
        document.getElementById('users_total').textContent = fmtInt(auC + usC + thC);
        document.getElementById('sumMetaUsers').textContent = 'Source: Stripe API';

        // Rebuild MoM/YoY with Stripe API data
        renderMomYoy();
        updateGrandTotal();
    }

    function checkAllSmsLoaded() {
        if (!SMS_SUM_LOADED.au || !SMS_SUM_LOADED.us || !SMS_SUM_LOADED.uk) return;
        // Update SMS metadata
        var ranges = [];
        ['au','us','uk'].forEach(function(a) {
            var r = smsDateRange(a);
            if (r) ranges.push(r);
        });
        var metaText = 'Source: TransmitSMS API';
        if (ranges.length) {
            var allFrom = ranges.map(function(r){ return r.from; }).sort();
            var allTo = ranges.map(function(r){ return r.to; }).sort();
            metaText += ' | Data: ' + allFrom[0] + ' — ' + allTo[allTo.length-1];
        }
        ['au','us','uk'].forEach(function(a) {
            if (SMS_DATA[a] && SMS_DATA[a].fetched_at) {
                metaText += ' | ' + a.toUpperCase() + ' fetched: ' + SMS_DATA[a].fetched_at;
            }
        });
        document.getElementById('sumMetaSms').textContent = metaText;
        // Rebuild MoM/YoY with SMS data included
        renderMomYoy();
        // Recalculate Grand Total with SMS data
        updateGrandTotal();
    }

    function renderMomYoy() {
        var mom = DATA.mom || {};
        var yoy = DATA.yoy || {};

        // Filter out Monday.com sms & stripe keys from by_type (we use APIs instead)
        function filterByType(bt) {
            var out = {};
            for (var k in bt) {
                if (k.indexOf('sms') === 0) continue; // skip Monday.com sms_*
                if (k.indexOf('stripe') === 0) continue; // skip Monday.com stripe_* (use Stripe API)
                out[k] = bt[k];
            }
            return out;
        }

        // Build Stripe MoM rows from Stripe API monthly data
        function buildStripeMomRows() {
            var curMonth = mom.current_month || '';
            var prevMonth = mom.previous_month || '';
            var html = '';
            var symMap = { au:'A$', us:'$', th:'฿' };
            ['au','us','th'].forEach(function(a) {
                if (!STRIPE_SUM_DATA[a]) return;
                var cur = stripeSumMonthRevenue(a, curMonth);
                var prev = stripeSumMonthRevenue(a, prevMonth);
                var curA = cur.amount || 0, prevA = prev.amount || 0;
                var pct = prevA > 0 ? Math.round((curA - prevA) / prevA * 1000) / 10 : null;
                var key = 'stripe_' + a;
                html += '<tr><td>' + typeLabel(key) + '</td>'
                    + '<td class="text-right">' + fmt(prevA, symMap[a]) + '</td>'
                    + '<td class="text-right">' + fmt(curA, symMap[a]) + '</td>'
                    + '<td class="text-center">' + changeBadge(pct) + '</td></tr>';
            });
            return html;
        }

        // Build Stripe YoY rows from Stripe API monthly data
        function buildStripeYoyRows() {
            var curYear = yoy.current_year || '';
            var prevYear = yoy.previous_year || '';
            var html = '';
            var symMap = { au:'A$', us:'$', th:'฿' };
            ['au','us','th'].forEach(function(a) {
                if (!STRIPE_SUM_DATA[a]) return;
                var cur = stripeSumYearAggr(a, curYear);
                var prev = stripeSumYearAggr(a, prevYear);
                var pct = prev.amount > 0 ? Math.round((cur.amount - prev.amount) / prev.amount * 1000) / 10 : null;
                var key = 'stripe_' + a;
                html += '<tr><td>' + typeLabel(key) + '</td>'
                    + '<td class="text-right">' + fmt(prev.amount, symMap[a]) + '</td>'
                    + '<td class="text-right">' + fmt(cur.amount, symMap[a]) + '</td>'
                    + '<td class="text-center">' + changeBadge(pct) + '</td></tr>';
            });
            return html;
        }

        // Build SMS MoM/YoY from TransmitSMS data
        function buildSmsMomRows() {
            var curMonth = mom.current_month || '';
            var prevMonth = mom.previous_month || '';
            var html = '';
            ['au','us','uk'].forEach(function(a) {
                if (!SMS_DATA[a]) return;
                var cur = smsMonthRevenue(a, curMonth);
                var prev = smsMonthRevenue(a, prevMonth);
                var pct = prev > 0 ? Math.round((cur - prev) / prev * 1000) / 10 : null;
                var sc = smsSumCurMap[a];
                var key = 'sms_' + a;
                html += '<tr><td>' + typeLabel(key) + '</td>'
                    + '<td class="text-right">' + fmt(prev, sc) + '</td>'
                    + '<td class="text-right">' + fmt(cur, sc) + '</td>'
                    + '<td class="text-center">' + changeBadge(pct) + '</td></tr>';
            });
            return html;
        }

        function buildSmsYoyRows() {
            var curYear = yoy.current_year || '';
            var prevYear = yoy.previous_year || '';
            var html = '';
            ['au','us','uk'].forEach(function(a) {
                if (!SMS_DATA[a]) return;
                var cur = smsYearRevenue(a, curYear);
                var prev = smsYearRevenue(a, prevYear);
                var pct = prev > 0 ? Math.round((cur - prev) / prev * 1000) / 10 : null;
                var sc = smsSumCurMap[a];
                var key = 'sms_' + a;
                html += '<tr><td>' + typeLabel(key) + '</td>'
                    + '<td class="text-right">' + fmt(prev, sc) + '</td>'
                    + '<td class="text-right">' + fmt(cur, sc) + '</td>'
                    + '<td class="text-center">' + changeBadge(pct) + '</td></tr>';
            });
            return html;
        }

        // Sort helper
        function sortedTypeKeys(byType) {
            var keys = Object.keys(byType || {});
            keys.sort(function(a, b) {
                var ia = sumTypeOrder.indexOf(a), ib = sumTypeOrder.indexOf(b);
                if (ia === -1) ia = 999;
                if (ib === -1) ib = 999;
                return ia - ib;
            });
            return keys;
        }

        // Build Monday.com rows (stripe + yelp only)
        function buildMondayRows(byType) {
            var filtered = filterByType(byType);
            var html = '';
            sortedTypeKeys(filtered).forEach(function(key) {
                var t = filtered[key];
                if (!t) return;
                var isStripe = key.indexOf('stripe') === 0;
                var prev = isStripe ? (t.previous||{}).amount : (t.previous||{}).revenue;
                var cur = isStripe ? (t.current||{}).amount : (t.current||{}).revenue;
                var sc = getCur(key);
                html += '<tr><td>' + typeLabel(key) + '</td>'
                    + '<td class="text-right">' + fmt(prev, sc) + '</td>'
                    + '<td class="text-right">' + fmt(cur, sc) + '</td>'
                    + '<td class="text-center">' + changeBadge(t.change_pct) + '</td></tr>';
            });
            return html;
        }

        var hasStripe = STRIPE_SUM_LOADED.au || STRIPE_SUM_LOADED.us || STRIPE_SUM_LOADED.th;
        var hasSms = SMS_SUM_LOADED.au || SMS_SUM_LOADED.us || SMS_SUM_LOADED.uk;

        // MoM
        document.getElementById('sumMomPrev').textContent = mom.previous_month || 'Prev';
        document.getElementById('sumMomCur').textContent = mom.current_month || 'Current';
        var momHtml = buildStripeMomRows();
        momHtml += buildMondayRows(mom.by_type || {}); // now only yelp + others
        momHtml += buildSmsMomRows();
        document.getElementById('sumMomBody').innerHTML = momHtml;
        var momMeta = [];
        if (hasStripe) momMeta.push('Stripe: Stripe API');
        momMeta.push('Yelp: Monday.com');
        if (hasSms) momMeta.push('SMS: TransmitSMS API');
        document.getElementById('sumMetaMom').textContent = momMeta.join(' | ');

        // YoY
        document.getElementById('sumYoyPrev').textContent = yoy.previous_year || 'Prev';
        document.getElementById('sumYoyCur').textContent = yoy.current_year || 'Current';
        var yoyHtml = buildStripeYoyRows();
        yoyHtml += buildMondayRows(yoy.by_type || {});
        yoyHtml += buildSmsYoyRows();
        document.getElementById('sumYoyBody').innerHTML = yoyHtml;
        document.getElementById('sumMetaYoy').textContent = momMeta.join(' | ');
    }

    // ===== SMS (TransmitSMS) =====
    function loadSmsData(acct, forceRefresh) {
        document.getElementById('smsLoading').style.display = '';
        document.getElementById('smsContent').style.display = 'none';
        var params = { account: acct };
        if (forceRefresh) params.refresh = '1';

        $.ajax({ url:'api/sms/getData.php', data: params, dataType:'json', timeout:120000 })
        .done(function(d) {
            if (d.error) { alert('SMS error: ' + d.error); return; }
            SMS_DATA[acct] = d;
            if (smsActiveAccount === acct) renderSmsTab(acct);
        })
        .fail(function(xhr, st, err) { alert('SMS failed: ' + (err||st)); })
        .always(function() { document.getElementById('smsLoading').style.display = 'none'; });
    }

    function renderSmsTab(acct) {
        var d = SMS_DATA[acct];
        if (!d) return;
        var sym = d.symbol || '$';
        document.getElementById('smsContent').style.display = '';

        // Title + balance info
        document.getElementById('smsTabTitle').innerHTML = '<i class="bi bi-chat-dots mr-1"></i> ' + (d.label || 'SMS');
        var bal = d.balance || {};
        document.getElementById('smsBalLabel').textContent = 'Balance: ' + sym + fmtNum(bal.balance) + ' ' + (bal.currency || '');

        // Debug info
        var dbg = d.debug || {};
        document.getElementById('smsPeriodInfo').textContent =
            (dbg.months || 0) + ' months | ' + (dbg.source || '') + (dbg.time_seconds ? ' ' + dbg.time_seconds + 's' : '') +
            ' | ' + (d.fetched_at || '');

        // Monthly table body
        var rows = d.monthly || [];
        var html = '';
        rows.forEach(function(m) {
            html += '<tr>'
                + '<td style="font-weight:500;">' + m.label + '</td>'
                + '<td class="text-right">' + fmtInt(m.sms_sent) + '</td>'
                + '<td class="text-right">' + sym + fmtNum(m.sms_margin) + '</td>'
                + '<td class="text-right">' + sym + fmtNum(m.numbers_sum) + '</td>'
                + '<td class="text-right">' + sym + fmtNum(m.keywords_sum) + '</td>'
                + '<td class="text-right">' + sym + fmtNum(m.deposit) + '</td>'
                + '<td class="text-right">' + sym + fmtNum(m.cost) + '</td>'
                + '<td class="text-right font-weight-bold" style="color:' + (m.revenue >= 0 ? '#0e6245' : '#cd3d64') + ';">' + sym + fmtNum(m.revenue) + '</td>'
                + '</tr>';
        });
        if (!html) html = '<tr><td colspan="8" class="text-center text-muted py-4">No data available</td></tr>';
        document.getElementById('smsMonthlyBody').innerHTML = html;

        // Footer totals
        var tot = d.totals || {};
        document.getElementById('smsMonthlyFoot').innerHTML = '<tr>'
            + '<td style="font-weight:700;">Total</td>'
            + '<td class="text-right">' + fmtInt(tot.sms_sent) + '</td>'
            + '<td class="text-right">' + sym + fmtNum(tot.sms_margin) + '</td>'
            + '<td class="text-right">' + sym + fmtNum(tot.numbers_sum) + '</td>'
            + '<td class="text-right">' + sym + fmtNum(tot.keywords_sum) + '</td>'
            + '<td class="text-right">' + sym + fmtNum(tot.deposit) + '</td>'
            + '<td class="text-right">' + sym + fmtNum(tot.cost) + '</td>'
            + '<td class="text-right" style="color:' + ((tot.revenue||0) >= 0 ? '#0e6245' : '#cd3d64') + ';">' + sym + fmtNum(tot.revenue) + '</td>'
            + '</tr>';
    }

    function fmtNum(v) {
        if (v === null || v === undefined) return '0.00';
        var n = Number(v);
        return Math.abs(n).toLocaleString(undefined, {minimumFractionDigits:3, maximumFractionDigits:3});
    }

    // ===== STRIPE CONNECT =====
    var conTimerInterval = null;
    function loadConnect(forceRefresh) {
        document.getElementById('conLoading').style.display = '';
        document.getElementById('conContent').style.display = 'none';
        var conStart = Date.now();
        var conTimerEl = document.getElementById('conTimer');
        if (conTimerEl) conTimerEl.textContent = '0.0';
        if (conTimerInterval) clearInterval(conTimerInterval);
        conTimerInterval = setInterval(function() {
            var el = document.getElementById('conTimer');
            if (el) el.textContent = ((Date.now() - conStart) / 1000).toFixed(1);
        }, 100);
        var sel = document.getElementById('conPeriod').value;
        var params = { account: 'connect' };
        if (sel === 'custom') {
            var s = document.getElementById('conDateStart').value;
            var e = document.getElementById('conDateEnd').value;
            if (!s || !e) { alert('Please select start and end dates'); document.getElementById('conLoading').style.display = 'none'; return; }
            params.start = s;
            params.end = e;
        } else {
            params.days = sel;
        }
        var days = sel;
        if (forceRefresh) {
            // Use refreshCache endpoint for force refresh
            $.ajax({ url:'api/stripe/refreshCache.php', data:{ account:'connect', period: conPeriodMap(days) }, dataType:'json', timeout:300000 })
            .done(function(d) {
                if (d.error) { alert('Connect refresh error: ' + d.error); return; }
                CON_DATA = d;
                renderConnect();
            })
            .fail(function(xhr,st,err) { alert('Connect refresh failed: ' + (err||st)); })
            .always(function() { if (conTimerInterval) clearInterval(conTimerInterval); document.getElementById('conLoading').style.display = 'none'; });
        } else {
            $.ajax({ url:'api/stripe/getData.php', data: params, dataType:'json', timeout:120000 })
            .done(function(d) {
                if (d.error) { alert('Connect error: ' + d.error); return; }
                CON_DATA = d;
                renderConnect();
            })
            .fail(function(xhr,st,err) { alert('Connect failed: ' + (err||st)); })
            .always(function() { if (conTimerInterval) clearInterval(conTimerInterval); document.getElementById('conLoading').style.display = 'none'; });
        }
    }

    function conPeriodMap(days) {
        var map = {'7':'7days','28':'28days','90':'90days','180':'180days','365':'365days','9999':'alltime'};
        return map[days] || '28days';
    }

    var CON_TOP_CUST = null; // cached all-time top customers

    function renderConnect() {
        if (!CON_DATA) return;
        var d = CON_DATA;
        var cs = d.currencySymbol || '$';
        var curCodeMap = {'USD':'$','AUD':'A$','THB':'฿','GBP':'£','EUR':'€','NZD':'NZ$'};
        document.getElementById('conContent').style.display = '';

        // Period info — show selected label + date range + debug
        var p = d.period || {};
        var dbg = d.debug || {};
        var sel = document.getElementById('conPeriod');
        var selLabel = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
        var periodStr = (p.start||'') + ' — ' + (p.end||'') + ' (' + (p.days||'') + ' days)';
        if (sel.value === 'custom') {
            var cs2 = document.getElementById('conDateStart').value;
            var ce2 = document.getElementById('conDateEnd').value;
            if (cs2 && ce2) periodStr = cs2 + ' — ' + ce2 + ' (' + (p.days||'') + ' days)';
        }
        document.getElementById('conPeriodInfo').textContent = periodStr + ' | ' + (dbg.source||'') + (dbg.time_seconds ? ' ' + dbg.time_seconds + 's' : '');

        // Balance (multi-currency breakdown)
        var bal = d.balance || {};
        var byCur = bal.by_currency || {};
        var balParts = [];
        var curOrder = ['AUD','GBP','NZD','USD'];
        curOrder.forEach(function(c) {
            var b = byCur[c];
            if (b !== undefined) balParts.push(fmt(b.available, curCodeMap[c] || c));
        });
        for (var c in byCur) {
            if (curOrder.indexOf(c) === -1) balParts.push(fmt(byCur[c].available, curCodeMap[c] || c));
        }
        document.getElementById('conBalAvail').innerHTML = balParts.length ? balParts.join(' &nbsp;|&nbsp; ') : fmt(bal.available, cs);
        
        var pendParts = [];
        curOrder.forEach(function(c) {
            var b = byCur[c];
            if (b && b.pending) pendParts.push(fmt(b.pending, curCodeMap[c] || c));
        });
        document.getElementById('conBalPending').innerHTML = pendParts.length ? pendParts.join(' &nbsp;|&nbsp; ') : fmt(bal.pending, cs);

        // Payout — show most recent payout from payouts list
        var latestPo = (d.payouts || [])[0];
        if (latestPo) {
            var poSym = curCodeMap[latestPo.currency] || cs;
            document.getElementById('conNextPayout').textContent = fmt(latestPo.amount, poSym);
            document.getElementById('conPayoutDate').textContent = latestPo.arrival_date + ' · ' + latestPo.status;
        } else {
            document.getElementById('conNextPayout').textContent = '—';
            document.getElementById('conPayoutDate').textContent = 'No payouts';
        }

        // Net Volume
        var nv = d.netVolume || {};
        document.getElementById('conNet').textContent = fmt(nv.total, cs);
        document.getElementById('conNetPrev').textContent = fmt(nv.previous, cs);
        document.getElementById('conNetChg').innerHTML = changeBadge(nv.change);

        // New connected accounts
        var nc = d.newCustomers || {};
        document.getElementById('conNewCust').textContent = fmtInt(nc.count);
        document.getElementById('conNewCustPrev').textContent = fmtInt(nc.previous);
        document.getElementById('conNewCustChg').innerHTML = changeBadge(nc.change);

        // Payouts table — use each payout's own currency
        var poHtml = '';
        (d.payouts || []).forEach(function(po) {
            var stCls = 'payout-' + po.status;
            var poSym = curCodeMap[po.currency] || cs;
            poHtml += '<tr>'
                + '<td>' + po.created + '</td>'
                + '<td class="text-right font-weight-bold">' + fmt(po.amount, poSym) + '</td>'
                + '<td><span class="payout-status ' + stCls + '">' + po.status + '</span></td>'
                + '<td>' + po.arrival_date + '</td></tr>';
        });
        if (!poHtml) poHtml = '<tr><td colspan="4" class="text-center text-muted">No payouts</td></tr>';
        document.getElementById('conPayoutsBody').innerHTML = poHtml;

        // Top customers — load all-time data separately
        if (CON_TOP_CUST) {
            renderConTopCust(cs);
        } else {
            document.getElementById('conTopCustBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted"><div class="spinner-border spinner-border-sm" style="color:#635bff;" role="status"></div> Loading all-time data...</td></tr>';
            $.ajax({ url:'api/stripe/getData.php', data:{ account:'connect', days:9999 }, dataType:'json', timeout:300000 })
            .done(function(allD) {
                if (allD.error) {
                    document.getElementById('conTopCustBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error: ' + allD.error + '</td></tr>';
                    return;
                }
                CON_TOP_CUST = allD.topCustomers || [];
                renderConTopCust(allD.currencySymbol || cs);
            })
            .fail(function() {
                document.getElementById('conTopCustBody').innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load</td></tr>';
            });
        }

        // Update Summary Connect card + Grand Total when Connect data changes
        renderConnectCard();
        updateGrandTotal();
    }

    function renderConTopCust(cs) {
        var tcHtml = '';
        (CON_TOP_CUST || []).forEach(function(tc, i) {
            tcHtml += '<tr><td>' + (i+1) + '</td>'
                + '<td>' + (tc.name || '—') + '</td>'
                + '<td style="font-size:10px;">' + (tc.email || '') + '</td>'
                + '<td class="text-right font-weight-bold">' + fmt(tc.amount, cs) + '</td></tr>';
        });
        if (!tcHtml) tcHtml = '<tr><td colspan="4" class="text-center text-muted">No customer data</td></tr>';
        document.getElementById('conTopCustBody').innerHTML = tcHtml;
    }

    // ===== TAB CLICKS (supports dropdowns) =====
    // Stripe sub-label map
    var stripeSubMap = { 'stripe_au':'AU', 'stripe_us':'US', 'stripe_th':'TH', 'connect':'Connect' };
    var smsSubMap = { 'sms_au':'AU', 'sms_us':'US', 'sms_uk':'UK' };

    // Regular nav-link tabs (Yelp, Summary)
    document.querySelectorAll('#revMainTabs > .nav-item:not(.dropdown) > .nav-link').forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveNav(this.getAttribute('data-tab'));
        });
    });
    // Dropdown items
    document.querySelectorAll('#revMainTabs .dropdown-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            setActiveNav(this.getAttribute('data-tab'));
        });
    });

    function setActiveNav(tab) {
        // Clear all active states
        document.querySelectorAll('#revMainTabs .nav-link').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('#revMainTabs .dropdown-item').forEach(function(t) { t.classList.remove('active'); });

        // Set active on dropdown-item
        var item = document.querySelector('#revMainTabs .dropdown-item[data-tab="' + tab + '"]');
        if (item) item.classList.add('active');

        // Set parent dropdown toggle active + update sub-label
        if (stripeSubMap[tab]) {
            document.getElementById('ddStripe').classList.add('active');
            document.getElementById('ddStripeSub').textContent = stripeSubMap[tab];
        } else if (smsSubMap[tab]) {
            document.getElementById('ddSms').classList.add('active');
            document.getElementById('ddSmsSub').textContent = smsSubMap[tab];
        } else {
            // Regular tab (yelp, summary)
            var navLink = document.querySelector('#revMainTabs .nav-link[data-tab="' + tab + '"]');
            if (navLink) navLink.classList.add('active');
        }
        switchTab(tab);
    }

    // Month/Year toggle for single tab
    document.getElementById('tabMonthView').addEventListener('change', function() {
        if (!DATA || activeTab === 'summary') return;
        var r = (DATA.revenue || {})[activeTab] || {};
        renderTabMonthly(r.by_month || {}, this.value);
    });

    // Mode selector (All Time / Compare) — sync both dropdowns
    document.getElementById('tabMode').addEventListener('change', function() {
        document.getElementById('tabModeAlt').value = this.value;
        if (!DATA || activeTab === 'summary') return;
        renderSingleTab(activeTab);
    });
    document.getElementById('tabModeAlt').addEventListener('change', function() {
        document.getElementById('tabMode').value = this.value;
        if (!DATA || activeTab === 'summary') return;
        renderSingleTab(activeTab);
    });

    // Compare month pickers
    document.getElementById('cmpMonthA').addEventListener('change', function() {
        if (!DATA || activeTab === 'summary') return;
        renderCompareMode(activeTab);
    });
    document.getElementById('cmpMonthB').addEventListener('change', function() {
        if (!DATA || activeTab === 'summary') return;
        renderCompareMode(activeTab);
    });

    // SMS refresh button
    document.getElementById('btnSmsRefresh').addEventListener('click', function() {
        if (!smsActiveAccount) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spinning"></i> Refreshing...';
        SMS_DATA[smsActiveAccount] = null;
        loadSmsData(smsActiveAccount, true);
        var checkDone = setInterval(function() {
            if (document.getElementById('smsLoading').style.display === 'none') {
                clearInterval(checkDone);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise" id="smsRefreshIcon"></i> Refresh';
            }
        }, 500);
    });

    // Split view: Stripe API preset buttons
    document.querySelectorAll('.sp-preset').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!activeTab || !stripeAcctMap[activeTab]) return;
            var days = parseInt(this.getAttribute('data-days'));
            setSplitStripeDatesFromPreset(days);
            var acct = stripeAcctMap[activeTab];
            var cs = getCur(activeTab);
            var params = getSplitStripeParams(acct);
            loadStripeApiData(acct, params, cs);
        });
    });

    // Split view: Custom date range Go button
    document.getElementById('spDateGo').addEventListener('click', function() {
        if (!activeTab || !stripeAcctMap[activeTab]) return;
        spActiveDays = 0; // custom range
        document.querySelectorAll('.sp-preset').forEach(function(b) { b.classList.remove('active'); });
        var acct = stripeAcctMap[activeTab];
        var cs = getCur(activeTab);
        var params = getSplitStripeParams(acct);
        loadStripeApiData(acct, params, cs);
    });

    // Connect period change — show/hide datepicker
    document.getElementById('conPeriod').addEventListener('change', function() {
        var isCustom = this.value === 'custom';
        document.getElementById('conDateRange').style.display = isCustom ? 'flex' : 'none';
        if (!isCustom) {
            var labelMap = {'7':'Last 7 days','28':'Last 28 days','90':'Last 90 days','180':'Last 180 days','365':'Last 365 days','9999':'All time'};
            document.getElementById('conPeriodInfo').textContent = (labelMap[this.value] || '') + ' — loading...';
            CON_DATA = null;
            loadConnect();
        }
    });

    // Connect custom date Go button
    document.getElementById('conDateGo').addEventListener('click', function() {
        var s = document.getElementById('conDateStart').value;
        var e = document.getElementById('conDateEnd').value;
        if (s && e) {
            document.getElementById('conPeriodInfo').textContent = s + ' — ' + e + ' — loading...';
        }
        CON_DATA = null;
        loadConnect();
    });

    // Connect refresh button
    document.getElementById('btnConRefresh').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spinning"></i> Refreshing...';
        loadConnect(true);
        // Re-enable after load completes (loadConnect is async)
        var checkDone = setInterval(function() {
            if (document.getElementById('conLoading').style.display === 'none') {
                clearInterval(checkDone);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise" id="conRefreshIcon"></i> Refresh';
            }
        }, 500);
    });

    // Refresh button (Monday.com data)
    document.getElementById('btnRefresh').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise spinning"></i> Refreshing...';
        $.ajax({ url:'api/revenue/getSummary.php', data:{refresh:'1'}, dataType:'json', timeout:600000 })
        .done(function(d) {
            if (d.error) { alert('Refresh error: ' + d.error); }
            else { DATA = d; switchTab(activeTab); }
        })
        .fail(function(xhr,st,err) { alert('Refresh failed: ' + (err || st)); })
        .always(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise" id="refreshIcon"></i> Refresh';
        });
    });

    // ===== SYNC CHAIN (removed with Monday panel) =====
    var dateSynced = false;

    // Helper: month "2025-03" → first day "2025-03-01"
    function monthToDateStart(m) { return m ? m + '-01' : ''; }
    // Helper: month "2025-03" → last day "2025-03-31"
    function monthToDateEnd(m) {
        if (!m) return '';
        var parts = m.split('-');
        var y = parseInt(parts[0]), mo = parseInt(parts[1]);
        var last = new Date(y, mo, 0).getDate();
        return m + '-' + (last < 10 ? '0' : '') + last;
    }
    // Helper: date "2025-03-15" → month "2025-03"
    function dateToMonth(d) { return d ? d.substring(0, 7) : ''; }

    function syncMonToStripe() {
        var ms = document.getElementById('monDateStart').value;
        var me = document.getElementById('monDateEnd').value;
        if (!ms || !me) return;
        document.getElementById('spDateStart').value = monthToDateStart(ms);
        document.getElementById('spDateEnd').value = monthToDateEnd(me);
        spActiveDays = 0;
        document.querySelectorAll('.sp-preset').forEach(function(b) { b.classList.remove('active'); });
        if (!activeTab || !stripeAcctMap[activeTab]) return;
        var acct = stripeAcctMap[activeTab];
        var cs = getCur(activeTab);
        var params = getSplitStripeParams(acct);
        loadStripeApiData(acct, params, cs);
    }

    function syncStripeToMon() {
        var ss = document.getElementById('spDateStart').value;
        var se = document.getElementById('spDateEnd').value;
        if (!ss || !se) return;
        document.getElementById('monDateStart').value = dateToMonth(ss);
        document.getElementById('monDateEnd').value = dateToMonth(se);
        monFilterStart = dateToMonth(ss);
        monFilterEnd = dateToMonth(se);
        if (!DATA || !activeTab) return;
        var rev = DATA.revenue || {};
        var r = rev[activeTab] || {};
        var cs = getCur(activeTab);
        renderSplitMonday(activeTab, r, cs);
    }

    // ===== MONDAY DATEPICKER EVENTS (removed with Monday panel) =====

    // ===== YELP SPLIT VIEW =====
    var YELP_BILLING = null;
    var yelpMonFilterStart = null;
    var yelpMonFilterEnd = null;
    var yelpDateSynced = false;

    // --- Yelp Monday.com left panel ---
    function initYelpMondayDatepicker(r) {
        var months = Object.keys(r.by_month || {}).sort();
        var elS = document.getElementById('yelpMonDateStart');
        var elE = document.getElementById('yelpMonDateEnd');
        if (months.length > 0) {
            elS.min = months[0]; elS.max = months[months.length - 1];
            elE.min = months[0]; elE.max = months[months.length - 1];
            if (!yelpMonFilterStart) { elS.value = months[0]; elE.value = months[months.length - 1]; }
            else { elS.value = yelpMonFilterStart; elE.value = yelpMonFilterEnd; }
        }
    }

    function renderYelpMonday(r, cs) {
        var byMonth = r.by_month || {};

        // Apply date filter
        var filtered = {};
        var fS = yelpMonFilterStart || '';
        var fE = yelpMonFilterEnd || '';
        Object.keys(byMonth).forEach(function(mk) {
            if (fS && mk < fS) return;
            if (fE && mk > fE) return;
            filtered[mk] = byMonth[mk];
        });

        var totals = { revenue:0, count:0 };
        Object.keys(filtered).forEach(function(mk) {
            totals.revenue += filtered[mk].revenue || 0;
            totals.count += filtered[mk].count || 0;
        });

        var metricsHtml = '';
        [{label:'Revenue', val: totals.revenue}, {label:'Items', val: totals.count, isInt:true}].forEach(function(dd) {
            metricsHtml += '<div class="col-6 split-mon-cell">'
                + '<div class="rc-label">' + dd.label + '</div>'
                + '<div class="rc-big">' + (dd.isInt ? fmtInt(dd.val) : fmt(dd.val, cs)) + '</div>'
                + '</div>';
        });
        document.getElementById('yelpMondayMetrics').innerHTML = metricsHtml;

        var mKeys = Object.keys(filtered).sort().reverse();
        var html = '';
        mKeys.forEach(function(mk) {
            var m = filtered[mk];
            html += '<tr><td>' + monthLabel(mk) + '</td>'
                + '<td class="text-right">' + fmt(m.revenue || 0, cs) + '</td>'
                + '<td class="text-right">' + fmtInt(m.count || 0) + '</td></tr>';
        });
        if (!html) html = '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';
        document.getElementById('yelpMondayMonthly').innerHTML = html;
    }

    // --- Yelp Monday datepicker events ---
    document.getElementById('yelpMonDateGo').addEventListener('click', function() {
        yelpMonFilterStart = document.getElementById('yelpMonDateStart').value;
        yelpMonFilterEnd = document.getElementById('yelpMonDateEnd').value;
        if (!DATA || !activeTab) return;
        var r = (DATA.revenue || {})[activeTab] || {};
        renderYelpMonday(r, getCur(activeTab));
    });
    document.getElementById('yelpMonDateReset').addEventListener('click', function() {
        yelpMonFilterStart = null;
        yelpMonFilterEnd = null;
        if (!DATA || !activeTab) return;
        var r = (DATA.revenue || {})[activeTab] || {};
        initYelpMondayDatepicker(r);
        renderYelpMonday(r, getCur(activeTab));
    });

    // --- Yelp Billing right panel (Excel mode) ---
    function initYelpFileList() {
        $.ajax({ url:'api/yelp/getAdsData.php', data:{action:'excel_list'}, dataType:'json' })
        .done(function(d) {
            var sel = document.getElementById('yelpFileSelect');
            sel.innerHTML = '<option value="">-- Select billing file --</option>';
            (d.files || []).forEach(function(f) {
                var opt = document.createElement('option');
                opt.value = f.filename;
                opt.textContent = f.filename + ' (' + f.modified + ')';
                sel.appendChild(opt);
            });
            if (d.files && d.files.length > 0) {
                sel.value = d.files[0].filename;
            }
        });
    }

    function loadYelpBilling(filename) {
        if (!filename) return;
        document.getElementById('yelpBillingLoading').style.display = '';
        document.getElementById('yelpBillingContent').style.display = 'none';

        $.ajax({ url:'api/yelp/getAdsData.php', data:{action:'excel_data', file:filename}, dataType:'json', timeout:60000 })
        .done(function(d) {
            if (d.error) {
                document.getElementById('yelpBillingLoading').innerHTML = '<span class="text-danger" style="font-size:12px;">Error: ' + d.error + '</span>';
                return;
            }
            YELP_BILLING = d;
            renderYelpBilling(d);
        })
        .fail(function(xhr, st, err) {
            document.getElementById('yelpBillingLoading').innerHTML = '<span class="text-danger" style="font-size:12px;">Failed: ' + (err || st) + '</span>';
        });
    }

    function renderYelpBilling(d) {
        document.getElementById('yelpBillingLoading').style.display = 'none';
        document.getElementById('yelpBillingContent').style.display = '';

        var sum = d.summary || {};
        var cs = 'USD';

        // Metrics
        var metricsHtml = '';
        [
            {label:'Total', val: sum.total_revenue},
            {label:'Rebate', val: sum.rebate},
            {label:'Grand Total', val: sum.grand_total},
            {label:'Businesses', val: sum.business_count, isInt:true},
            {label:'Features', val: sum.feature_count, isInt:true},
            {label:'Items', val: sum.item_count, isInt:true}
        ].forEach(function(dd) {
            metricsHtml += '<div class="col-4 split-mon-cell">'
                + '<div class="rc-label">' + dd.label + '</div>'
                + '<div class="rc-big">' + (dd.isInt ? fmtInt(dd.val) : fmt(dd.val, cs)) + '</div>'
                + '</div>';
        });
        document.getElementById('yelpBillingMetrics').innerHTML = metricsHtml;

        // By Business
        var byBiz = d.by_business || {};
        var bizHtml = '';
        Object.keys(byBiz).forEach(function(biz) {
            var b = byBiz[biz];
            var cpc = (b.features || {})['Clicks Advertiser'] || 0;
            bizHtml += '<tr><td title="' + (b.address || '') + '">' + biz + '</td>'
                + '<td class="text-right">' + fmt(b.revenue, cs) + '</td>'
                + '<td class="text-right">' + fmt(cpc, cs) + '</td></tr>';
        });
        if (!bizHtml) bizHtml = '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';
        document.getElementById('yelpBillingBusiness').innerHTML = bizHtml;

        // By Feature
        var byFeat = d.by_feature || {};
        var featHtml = '';
        Object.keys(byFeat).forEach(function(feat) {
            var f = byFeat[feat];
            featHtml += '<tr><td>' + feat + '</td>'
                + '<td class="text-right">' + fmt(f.revenue, cs) + '</td>'
                + '<td class="text-right">' + fmtInt(f.count) + '</td></tr>';
        });
        if (!featHtml) featHtml = '<tr><td colspan="3" class="text-center text-muted">No data</td></tr>';
        document.getElementById('yelpBillingFeature').innerHTML = featHtml;

        // Period info
        document.getElementById('ypPeriodInfo').textContent = (d.filename || '') + ' | ' + (sum.business_count||0) + ' businesses | ' + (sum.item_count||0) + ' items';
    }

    // --- Yelp file Load button ---
    document.getElementById('yelpFileLoad').addEventListener('click', function() {
        var filename = document.getElementById('yelpFileSelect').value;
        if (filename) loadYelpBilling(filename);
    });

    // --- Yelp file delete ---
    document.getElementById('yelpFileDelete').addEventListener('click', function() {
        var filename = document.getElementById('yelpFileSelect').value;
        if (!filename) { alert('Please select a file to delete'); return; }
        if (!confirm('Delete file "' + filename + '"?\nThis cannot be undone.')) return;
        var btn = this;
        btn.disabled = true;
        $.ajax({ url:'api/yelp/getAdsData.php', data:{ action:'excel_delete', file: filename }, dataType:'json' })
        .done(function(d) {
            if (d.error) { alert('Delete error: ' + d.error); return; }
            initYelpFileList();
            document.getElementById('yelpBillingContent').style.display = 'none';
        })
        .fail(function(xhr, st, err) { alert('Delete failed: ' + (err || st)); })
        .always(function() { btn.disabled = false; });
    });

    // --- Yelp file upload ---
    document.getElementById('yelpFileUpload').addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var fd = new FormData();
        fd.append('file', file);
        fd.append('action', 'excel_upload');
        $.ajax({
            url: 'api/yelp/getAdsData.php',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function(d) {
            if (d.error) { alert('Upload error: ' + d.error); return; }
            initYelpFileList();
            setTimeout(function() {
                document.getElementById('yelpFileSelect').value = d.filename;
                loadYelpBilling(d.filename);
            }, 500);
        }).fail(function(xhr, st, err) {
            alert('Upload failed: ' + (err || st));
        });
        this.value = '';
    });

    // --- Yelp chain sync (sync Monday filter → auto-select matching billing file) ---
    document.getElementById('btnYelpSyncChain').addEventListener('click', function() {
        yelpDateSynced = !yelpDateSynced;
        this.classList.toggle('active', yelpDateSynced);
    });

    // Auto-load
    loadSummary(false);
});
</script>
