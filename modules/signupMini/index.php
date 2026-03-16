<?php
date_default_timezone_set("Asia/Bangkok");
$currentDate = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up Form Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        ::placeholder { color: #cbd5e1 !important; }
    </style>
    <!-- Meta Pixel Code - LocalForYou Pixel -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '1002238327127461');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1002238327127461&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <meta name="facebook-domain-verification" content="7j4nrd8dg5lfwukkoron5135ohpwol" />
</head>

<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50">

    <div class="max-w-2xl mx-auto px-1 py-10">
        <form action="#" method="POST" id="miniForm">

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">

                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-8 py-8 text-white">
                    <h1 class="text-3xl font-bold tracking-tight">Free Thai Demo</h1>
                    <p class="mt-2 text-emerald-100 text-sm leading-relaxed">Enter your details below and we'll show you how our Online Ordering, Booking &amp; Marketing will work in your Thai Restaurant &amp; Massage and Spa!</p>
                </div>

                <!-- Form Body -->
                <div class="px-8 py-8 space-y-6">

                    <!-- Sign Up Date -->
                    <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 rounded-lg px-4 py-2.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="font-medium">Sign Up Date : <?php echo $currentDate; ?></span>
                    </div>

                    <!-- First Name / Last Name -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" name="first_name" maxlength="40" placeholder="First name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" name="last_name" maxlength="80" placeholder="Last name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>
                    </div>

                    <!-- Email / Mobile -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="text" id="email" name="email" placeholder="email@website.com"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>
                        <div class="form-group">
                            <label for="mobile" class="block text-sm font-semibold text-gray-700 mb-1.5">Mobile <span class="text-red-500">*</span></label>
                            <input type="text" id="mobile" name="mobile" placeholder="012345678"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>
                    </div>

                    <!-- Best time to Contact -->
                    <div class="form-group">
                        <label for="contactTime" class="block text-sm font-semibold text-gray-700 mb-1.5">Best time to Contact</label>
                        <input type="text" id="contactTime" name="contactTime" placeholder="10:00am"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                    </div>

                    <!-- Shop name -->
                    

                    <div class="form-group">
                            <label for="shopName" class="block text-sm font-semibold text-gray-700 mb-1.5">Shop Name <span class="text-red-500">*</span></label>
                            <input type="text" id="shopName" name="shopName" placeholder="your shop name"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>

                    <!-- Trading Name / Shop Type -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        

                        <div class="form-group">
                        <label for="company" class="block text-sm font-semibold text-gray-700 mb-1.5">Trading name</label>
                        <input type="text" id="company" name="company"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />

                            
                    </div>
                        <div class="form-group">
                            <label for="shopType" class="block text-sm font-semibold text-gray-700 mb-1.5">Shop Type <span class="text-red-500">*</span></label>
                            <select id="shopType" name="shopType" title="Customer_Type"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition appearance-none">
                                <option value="" selected>-- Please select --</option>
                                <option value="Thai Restaurants &amp; Takeaways">Thai Restaurants &amp; Takeaways</option>
                                <option value="Thai Massage">Thai Massage</option>
                                <option value="Restaurants &amp; Takeaways">Restaurants &amp; Takeaways</option>
                            </select>
                        </div>
                    </div>

                    <!-- Website or Social media -->
                    <div class="form-group">
                        <label for="url" class="block text-sm font-semibold text-gray-700 mb-1.5">Website or Social media</label>
                        <input type="text" id="url" name="url" placeholder="www.localforyou.com"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                    </div>

                    <!-- City / Country -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="country" class="block text-sm font-semibold text-gray-700 mb-1.5">Country <span class="text-red-500">*</span></label>
                            <select id="country" name="country" onchange="setMoney();"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition appearance-none">
                                <option value="" selected>-- Please select --</option>
                                <option value="Australia">Australia</option>
                                <option value="Canada">Canada</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Thailand">Thailand</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city" class="block text-sm font-semibold text-gray-700 mb-1.5">City</label>
                            <input type="text" id="city" name="city" placeholder="Queensland"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" />
                        </div>
                        
                    </div>

                    <!-- Currency (hidden) -->
                    <div class="hidden">
                        <div class="form-group">
                            <label for="currency" class="block text-sm font-semibold text-gray-700 mb-1.5">Currency <span class="text-red-500">*</span></label>
                            <select id="currency" name="currency"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                                <option value="" selected>-- Please select --</option>
                                <option value="AUD">AUD - Australian Dollar</option>
                                <option value="CAD">CAD - Canadian Dollar</option>
                                <option value="NZD">NZD - New Zealand Dollar</option>
                                <option value="GBP">GBP - British Pound</option>
                                <option value="USD">USD - U.S. Dollar</option>
                                <option value="THB">THB - Thai Baht</option>
                            </select>
                        </div>
                    </div>

                    <!-- Interesting in -->
                    <div class="form-group">
                        <label for="interest" class="block text-sm font-semibold text-gray-700 mb-1.5">Interesting in <span class="text-red-500">*</span></label>
                        <select id="interest" name="interest" title="Lead Interesting in"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition appearance-none">
                            <option value="" selected>-- Please select --</option>
                            <option value="Online Ordering System">Online Ordering System</option>
                            <option value="Booking System">Booking System</option>
                            <option value="Massage &amp; Spa">Massage &amp; Spa</option>
                            <option value="Pro Shopping Cart">Pro Shopping Cart</option>
                            <option value="Social Media Marketing">Social Media Marketing</option>
                            <option value="Social Media Bundle">Social Media Bundle</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Comments -->
                    <div class="form-group">
                        <label for="comments" class="block text-sm font-semibold text-gray-700 mb-1.5">Comments</label>
                        <textarea id="comments" name="comments" rows="3" wrap="soft"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="pt-2">
                        <input id="cmdSubmit" type="submit" value="Get Started"
                            class="w-full cursor-pointer rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200" />
                    </div>

                    <!-- Success Message -->
                    <div id="successMessage" class="hidden rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 text-center font-medium">
                        Your form has been submitted successfully!
                    </div>

                    <!-- Hidden fields -->
                    <input type="hidden" id="RestaurantMarketingAgent" name="agent" value="Other">
                    <input type="hidden" id="SignupFormVersion" name="version" value="L4U Website 1.0" />

                </div>
            </div>

            <input type="hidden" id="formType" name="formType" value="mini" />
            <input type="hidden" id="leadSource" name="leadSource" value="Landing Page" />
            <input type="hidden" id="leadRecordType" name="leadRecordType" value="Ads" />
        </form>
    </div>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<!-- <script src="https://report.localforyou.com/modules/signupMini/assets/js/index.js?v=2.0.2"></script> -->
<script src="assets/js/index.js?v=2.0.3"></script>
<script>

    const miniForm = $("#miniForm");

    miniForm.submit(function (e) {
        //$("#cmdSubmit").prop("disabled", true);
        //$("#cmdSubmit").attr("value", "Submitting...");
        e.preventDefault();
        const isValid = validateForm(miniForm);
        if (!isValid) return false;

        setMoney();

        const payload = getPayload(miniForm);
        const utm = getUTMParams();
        Object.assign(payload, utm);

        // ส่ง payload แล้วค่อยยิง pixel ใน success callback
        $.ajax({
            url: "https://hook.us1.make.com/47ue45ij7fhm7sol8rldp6dxpag2ldjl",
            method: "POST",
            dataType: "json",
            data: payload,
            success: function (response) {
            console.log("✅ Webhook Success:", response);

            if (response.result === "Leads to Monday successfully") {
                fbq('track', 'submit_form', {
                    content_name: 'Free Thai Demo Signup',
                    ...utm
                });
                $("#successMessage").show();
                setTimeout(() => {
                window.location.href = "https://localforyou.com/thank-you/";
                }, 1500);
            }
            },
            error: function (xhr, status, error) {
            console.error("❌ Webhook Failed:", status, error);
            }
        });
    });

</script>
</body>

</html>