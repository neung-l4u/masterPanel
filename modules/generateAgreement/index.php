<?php
date_default_timezone_set("Asia/Bangkok");
$currentDate = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LGKDYHL23T"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-LGKDYHL23T');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Agreement</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery-3.7.1.min.js"></script>
    
</head>

<body>
    <div class="container">
        <form class="p-5" id="agreementForm" action="https://report.localforyou.com/modules/signup/assets/docs/contract_2024_V02.php?" method="GET">

            <div class="card">
                <div class="card-header">
                    <h1>Generate Agreement</h1>
                </div>


                <div class="card-body p-4">

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="agreementType" class="font-weight-bold">
                                    Agreement Type
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="agreementType" class="form-control" onchange="switchAgreementType();">
                                    <option value="marketing" selected="selected">Marketing Service Agreement</option>
                                    <option value="pushpos">Push POS Customer Agreement</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="Country" class="font-weight-bold">
                                    County
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="Country" class="form-control" name="Country" onchange="txt();">
                                    <option value="AU" selected="selected">Australia</option>
                                    <option value="NZ">New Zealand</option>
                                    <option value="UK">United Kingdom</option>
                                    <option value="US">United States</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="State" class="font-weight-bold">State
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="State" class="form-control" name="State" placeholder="Queenland">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="registrationNumber" class="font-weight-bold" id="registrationNumber">ABN
                                    <span class="text-danger">*</span>
                                </label>
                                <input id="registrationNumber" class="form-control" name="registrationNumber" type="text" placeholder="12345678">
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="customerFullName" class="font-weight-bold">Customer FullName<span class="text-danger">*</span> </label>
                                <input type="text" id="customerFullName" class="form-control" maxlength="40" name="customerFullName" placeholder="John Doe">
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="ShopName" class="font-weight-bold">Shop Name<span class="text-danger">*</span> </label>
                                <input type="text" id="ShopName" class="form-control" name="ShopName" placeholder="Good Restaurant">
                            </div>
                        </div>

                    </div>

                    <div class="row" id="legalEntityRow" style="display: none;">
                        <div class="col">
                            <div class="form-group">
                                <label for="legalEntity" class="font-weight-bold">Legal Entity</label>
                                <input type="text" id="legalEntity" class="form-control" name="legalEntity" placeholder="Good Restaurant Pty Ltd">
                            </div>
                        </div>
                    </div>

                    <div class="row" id="contractPeriodRow">
                        <div class="col">
                            <div class="form-group">
                                <label for="contractPeriod" class="font-weight-bold">
                                    Period
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="contractPeriod" class="form-control" name="contractPeriod">
                                    <option value="0" selected="selected">No Contract</option>
                                    <option value="3">3 months</option>
                                    <!-- <option value="6">6 months</option> -->
                                    <option value="12">12 months</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="deliveryMode" class="font-weight-bold">
                                    Delivery
                                    <span class="text-danger">*</span>
                                </label>
                                <select id="deliveryMode" class="form-control" onchange="switchDeliveryMode();">
                                    <option value="preview" selected="selected">Preview only (open in browser)</option>
                                    <option value="email">DocuSign — email to customer</option>
                                    <option value="embedded">DocuSign — sign here now</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="signerEmailRow" style="display: none;">
                        <div class="col">
                            <div class="form-group">
                                <label for="signerEmail" class="font-weight-bold">
                                    Customer Email
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" id="signerEmail" class="form-control" name="signerEmail" placeholder="customer@example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <button type="submit" class="btn btn-primary mb-1" name="submit">Submit</button>
                            <!-- <input type="submit" name="submit"> -->
                            <span id="docusignSpinner" class="ml-2 text-muted" style="display: none;">Sending to DocuSign…</span>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <div id="docusignResult" class="alert" style="display: none;"></div>
                        </div>
                    </div>

                    <input type="hidden" id="RestaurantMarketingAgent" name="00N2u000000mNZG" value="Other">
                    <input type="hidden" id="SignupFormVersion" name="00N9s000000VWbf" value="L4U Website 1.0" />

                </div>
            </div>
        </form>
    </div>
    <script>
        const AGREEMENT_ACTIONS = {
            marketing: "https://report.localforyou.com/modules/signup/assets/docs/contract_2024_V02.php",
            pushpos: "https://report.localforyou.com/modules/signup/assets/docs/pushpos_agreement_V02.php"
        };

        function switchAgreementType() {
            let type = $("#agreementType").val();

            $("#agreementForm").attr("action", AGREEMENT_ACTIONS[type]);

            if (type === "pushpos") {
                $("#contractPeriodRow").hide();
                $("#contractPeriod").prop("disabled", true);
                $("#legalEntityRow").show();
                $("#legalEntity").prop("disabled", false);
            } else {
                $("#contractPeriodRow").show();
                $("#contractPeriod").prop("disabled", false);
                $("#legalEntityRow").hide();
                $("#legalEntity").prop("disabled", true);
            }
        }

        const DOCUSIGN_ENDPOINT = "https://report.localforyou.com/api/docusign/sendAgreement.php";

        function switchDeliveryMode() {
            let mode = $("#deliveryMode").val();
            let needsEmail = mode === "email" || mode === "embedded";

            $("#signerEmailRow").toggle(needsEmail);
            $("#signerEmail").prop("required", needsEmail);
            $("#docusignResult").hide();
        }

        function showResult(cssClass, html) {
            $("#docusignResult")
                .attr("class", "alert " + cssClass)
                .html(html)
                .show();
        }

        function sendToDocuSign(mode) {
            let payload = {
                agreementType: $("#agreementType").val(),
                deliveryMode: mode,
                signerEmail: $("#signerEmail").val().trim(),
                customerFullName: $("#customerFullName").val().trim(),
                ShopName: $("#ShopName").val().trim(),
                legalEntity: $("#legalEntity").val().trim(),
                registrationNumber: $("#registrationNumber").val().trim(),
                State: $("#State").val().trim(),
                Country: $("#Country").val(),
                contractPeriod: $("#contractPeriod").val()
            };

            $("#docusignSpinner").show();
            $("#docusignResult").hide();

            $.ajax({
                url: DOCUSIGN_ENDPOINT,
                method: "POST",
                contentType: "application/json",
                data: JSON.stringify(payload)
            }).done(function (res) {
                if (!res.success) {
                    showResult("alert-danger", "Failed: " + (res.error || "unknown error"));
                    return;
                }

                if (res.deliveryMode === "embedded" && res.signingUrl) {
                    // Single-use URL that expires quickly — go straight there.
                    window.location.href = res.signingUrl;
                    return;
                }

                showResult(
                    "alert-success",
                    "Agreement sent to <strong>" + payload.signerEmail + "</strong>.<br>" +
                    "Envelope ID: <code>" + res.envelopeId + "</code>"
                );
            }).fail(function (xhr) {
                let msg = "Request failed (HTTP " + xhr.status + ")";
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                showResult("alert-danger", "Failed: " + msg);
            }).always(function () {
                $("#docusignSpinner").hide();
            });
        }

        $(function () {
            switchAgreementType();
            switchDeliveryMode();

            $("#agreementForm").on("submit", function (e) {
                let mode = $("#deliveryMode").val();

                // "preview" keeps the original behaviour: GET straight to the template.
                if (mode === "preview") {
                    return true;
                }

                e.preventDefault();

                if (!$("#signerEmail").val().trim()) {
                    showResult("alert-warning", "Customer email is required to send via DocuSign.");
                    return false;
                }

                sendToDocuSign(mode);
                return false;
            });
        });

        function txt() {

            let ct = $("#Country").val();

            if (ct === "NZ") {
                $("#registrationNumber").html('NZBN <span class="text-danger">*</span>')
            } else if (ct === "AU") {
                $("#registrationNumber").html('ABN <span class="text-danger">*</span>')
            } else if (ct === "UK") {
                $("#registrationNumber").html('CRN <span class="text-danger">*</span>')
            } else if (ct === "US") {
                $("#registrationNumber").html('EIN <span class="text-danger">*</span>')
            }

        }
    </script>
</body>

</html>