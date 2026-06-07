<?php
session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
global $db;
$currentPage = basename($_SERVER['PHP_SELF']);
$tomorrow = date("Y-m-d H:i:s", strtotime("now"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Appointment Booking - L4U</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../../../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/libs/select2/css/select2.min.css" rel="stylesheet"/>
    <link href="../assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .sidebar-icon {
            transition: all 0.2s ease;
        }
        .sidebar-icon:hover {
            transform: scale(1.05);
        }
        .sidebar-icon.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .step-content {
            display: none;
            animation: fadeIn 0.3s ease-in;
        }
        .step-content.active {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.2s ease;
        }
        .form-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .input-field {
            transition: all 0.2s ease;
        }
        .input-field:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }
        .btn-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.2s ease;
        }
        .btn-primary-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .progress-ring {
            transform: rotate(-90deg);
        }
        .progress-ring-circle {
            transition: stroke-dashoffset 0.35s;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-6 fixed h-full z-10">
            <div class="mb-8">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                    L4U
                </div>
            </div>
            
            <!-- Step Icons -->
            <nav class="flex-1 flex flex-col gap-4">
                <button onclick="goToStep(1)" id="nav-step-1" class="sidebar-icon active w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-shop text-xl"></i>
                </button>
                <button onclick="goToStep(3)" id="nav-step-3" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-calendar-event text-xl"></i>
                </button>
                <button onclick="goToStep(8)" id="nav-step-8" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-person-vcard text-xl"></i>
                </button>
                <button onclick="goToStep(9)" id="nav-step-9" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-check-circle text-xl"></i>
                </button>
            </nav>
            
            <!-- Progress Ring -->
            <div class="mt-auto relative">
                <svg class="progress-ring w-14 h-14">
                    <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="4" fill="none"/>
                    <circle id="progress-circle" cx="28" cy="28" r="24" stroke="url(#gradient)" stroke-width="4" fill="none" 
                            stroke-dasharray="150.796" stroke-dashoffset="150.796" stroke-linecap="round" class="progress-ring-circle"/>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#667eea"/>
                            <stop offset="100%" stop-color="#764ba2"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span id="progress-text" class="absolute inset-0 flex items-center justify-center text-xs font-semibold text-gray-600">25%</span>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-20">
            <!-- Top Header -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">Sale Appointment Booking</h1>
                        <p class="text-sm text-gray-500 mt-1">Local For You - #1 Marketing Agency for Thai</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span id="current-step-label" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium">
                            Step 1 of 4: Business Info
                        </span>
                    </div>
                </div>
            </header>

            <!-- Form Content -->
            <div class="p-8 max-w-6xl mx-auto">
                <form id="bookingForm">
                    
                    <!-- Step 1: Business Type & Country -->
                    <div id="step-1" class="step-content active">
                        <div class="form-card p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-shop text-indigo-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Business Information</h2>
                                    <p class="text-sm text-gray-500">Select your business type and location</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <!-- Shop Type -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Shop Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="shop_type" name="shop_type" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Shop Type --</option>
                                            <?php
                                            $shopType = $db->query('SELECT * FROM `tb_shopType` WHERE status = ?',1)->fetchAll();
                                            foreach ($shopType as $row) {
                                            ?>
                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertShopType" class="text-red-500 text-sm mt-1 hidden">Please Select Shop Type.</p>
                                </div>

                                <!-- Country -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Country <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="country" name="country" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Country --</option>
                                            <option value="AU">🇦🇺 Australia</option>
                                            <option value="NZ">🇳🇿 New Zealand</option>
                                            <option value="US">🇺🇸 United States</option>
                                            <option value="UK">🇬🇧 United Kingdom</option>
                                            <option value="CA">🇨🇦 Canada</option>
                                            <option value="TH">🇹🇭 Thailand</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertCountry" class="text-red-500 text-sm mt-1 hidden">Please Select Country.</p>
                                </div>

                                <!-- State -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        State <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="state" name="state" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select State --</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertState" class="text-red-500 text-sm mt-1 hidden">Please Select State.</p>
                                </div>

                                <!-- Timezone -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Timezone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="timezone" name="timezone" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Timezone --</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Current timezone: <span id="timeZone" class="text-indigo-600 font-medium">-</span></p>
                                </div>

                                <!-- City -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City (Optional)</label>
                                    <input type="text" id="city" name="city" placeholder="Enter city name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="button" onclick="validateStep1()" 
                                        class="btn-primary-gradient px-6 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    Continue <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Sales & Schedule -->
                    <div id="step-3" class="step-content">
                        <div class="form-card p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-calendar-event text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Schedule Appointment</h2>
                                    <p class="text-sm text-gray-500">Select salesperson and appointment time</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <!-- Salesperson -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Salesperson <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="sales" name="sales" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Salesperson --</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertSale" class="text-red-500 text-sm mt-1 hidden">Please Select Sale.</p>
                                </div>

                                <!-- Booked By -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Booked By <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="bookBy" name="bookBy" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Booked By --</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertBooking" class="text-red-500 text-sm mt-1 hidden">Please Select Booking.</p>
                                </div>

                                <!-- Presentation Language -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Presentation Language <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select id="presentation" name="presentation" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Language --</option>
                                            <option value="English">🇬🇧 English</option>
                                            <option value="Thai">🇹🇭 Thai</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="alertLanguage" class="text-red-500 text-sm mt-1 hidden">Please Select Language.</p>
                                </div>

                                <!-- Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Appointment Date</label>
                                    <input type="text" id="date" name="date" value="<?php echo $tomorrow; ?>" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <!-- Time -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Available Time <span class="text-gray-500 font-normal">(<span id="timeZoneDisplay">-</span>)</span>
                                    </label>
                                    <div class="relative">
                                        <select id="time" name="time" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent appearance-none bg-white">
                                            <option value="">-- Select Time --</option>
                                        </select>
                                        <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                    </div>
                                    <p id="thTimePreview" class="text-sm text-gray-500 mt-2"></p>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" onclick="prevStep(1)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 flex items-center gap-2">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="validateStep3()" 
                                        class="btn-primary-gradient px-6 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    Continue <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 8: Customer Info -->
                    <div id="step-8" class="step-content">
                        <div class="form-card p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-pink-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-person-vcard text-pink-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Customer Information</h2>
                                    <p class="text-sm text-gray-500">Fill in customer details</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <!-- Shop Name -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Shop Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="shop_name" name="shop_name" placeholder="Enter shop name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertShopName" class="text-red-500 text-sm mt-1 hidden">Please enter the shop name</p>
                                </div>

                                <!-- Customer Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Customer Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="customer_name" name="customer_name" placeholder="Enter customer name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertCustomerName" class="text-red-500 text-sm mt-1 hidden">Please enter Customer name</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="contact_email" name="contact_email" placeholder="email@example.com" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertCustomerEmail" class="text-red-500 text-sm mt-1 hidden">Please enter Customer email</p>
                                    <p id="alertCustomerEmailValid" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address.</p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Phone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex">
                                        <span id="contact_phone_prefix" class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-600 font-medium">
                                            +
                                        </span>
                                        <input type="text" id="contact_phone" name="contact_phone" placeholder="Number only e.g. 0930396203" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatPhoneNumber(this.value, 'contact_phone');"
                                               class="input-field flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">without country's code</p>
                                    <p id="contact_phone_formatted" class="text-sm text-indigo-600 mt-1 font-medium"></p>
                                    <p id="alertCustomerPhone" class="text-red-500 text-sm mt-1 hidden">Please enter Phone number</p>
                                    <p id="alertCustomerPhoneComplete" class="text-red-500 text-sm mt-1 hidden">Please enter a complete number (min 10 digits).</p>
                                </div>

                                <!-- Mobile -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Mobile</label>
                                    <div class="flex">
                                        <span id="contact_mobile_prefix" class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-600 font-medium">
                                            +
                                        </span>
                                        <input type="text" id="contact_mobile" name="contact_mobile" placeholder="Number only e.g. 0930396203" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); formatPhoneNumber(this.value, 'contact_mobile');"
                                               class="input-field flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">without country's code</p>
                                    <p id="contact_mobile_formatted" class="text-sm text-indigo-600 mt-1 font-medium"></p>
                                </div>

                                <!-- Line ID -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Line ID</label>
                                    <input type="text" id="line_id" name="line_id" placeholder="LINE ID" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <!-- WhatsApp -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                                    <input type="text" id="whatsapp" name="whatsapp" placeholder="WhatsApp number" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <!-- Address -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <textarea id="address" name="address" rows="3" placeholder="Enter address" 
                                              class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                                </div>

                                <!-- Comment -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
                                    <textarea id="comment" name="comment" rows="3" placeholder="Additional comments" 
                                              class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" onclick="prevStep(3)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 flex items-center gap-2">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="showReview()" 
                                        class="btn-primary-gradient px-6 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    Review <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 9: Review -->
                    <div id="step-9" class="step-content">
                        <div class="form-card p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-check-circle text-green-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Review Booking</h2>
                                    <p class="text-sm text-gray-500">Please verify all information before confirming</p>
                                </div>
                            </div>

                            <div id="reviewSection" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                                <!-- Review cards will be inserted here -->
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="prevStep(8)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 flex items-center gap-2">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="submitBooking()" 
                                        class="btn-primary-gradient px-8 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    <i class="bi bi-check-lg"></i> Confirm & Book
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" value="<?php echo $tomorrow; ?>" name="timeToDayNow" id="timeToDayNow">
                </form>
            </div>
        </main>
    </div>

<script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
<script src="../assets/libs/select2/js/select2.min.js"></script>
<script src="../assets/libs/flatpickr/flatpickr.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
<script>
<script>
    //ประกาศตัวแปรผูกกับ id กับ class
    const shop_type = $('#shop_type');
    const country = $('#country');
    const city = $('#city');
    const timeZone = $('.timeZone');
    const sales = $('#sales');
    const bookBy = $('#bookBy');
    const presentation = $('#presentation');
    const date = $('#date');
    const time = $('#time');
    const shop_name = $('#shop_name');
    const customer_name = $('#customer_name');
    const contact_email = $('#contact_email');
    const contact_phone = $('#contact_phone');
    const contact_mobile = $('#contact_mobile');
    const line_id = $('#line_id');
    const whatsapp = $('#whatsapp');
    const address = $('#address');
    const comment = $('#comment');
    const state = $('#state');
    const timeToDayNow = $('#timeToDayNow');

    //ตัวแปรเปล่า
    let appointmentDetail = {};
    let sendEmailPayload = {};
    let thaiTimePreview = "";
    let thaiDayPreview = "";

    //ตัวแปร array
    const timeZoneMap = {
        "AU": "Australia/Sydney",
        "NZ": "Pacific/Auckland",
        "US": "America/New_York",
        "UK": "Europe/London",
        "CA": "America/Toronto",
        "TH": "Asia/Bangkok"
    };

    // Country dial codes mapping
    const countryDialCodes = {
        "AU": "61",
        "NZ": "64",
        "US": "1",
        "UK": "44",
        "CA": "1",
        "TH": "66"
    };

    //เมื่อเริ่มให้ทำอะไร
    $(document).ready(function () {
        //$('#shop_type').select2({placeholder: 'Select your store type',theme: 'bootstrap-5'});
        //country.select2({placeholder: 'Select country',theme: 'bootstrap-5'});
        $('#sales').select2({placeholder: 'Select salesperson',theme: 'bootstrap-5'});
        $('#time').select2({placeholder: 'Select appointment time',theme: 'bootstrap-5'});
        $('#bookBy').select2({placeholder: 'Select booked person',theme: 'bootstrap-5'});

        $('#alertShopType').hide();
        $('#alertCountry').hide();
        $('#alertState').hide();
        $('#alertSale').hide();
        $('#alertBooking').hide();
        $('#alertLanguage').hide();
        $('#alertShopName').hide();
        $('#alertCustomerName').hide();
        $('#alertCustomerEmail').hide();
        $('#alertCustomerPhone').hide();
        $('#alertCustomerEmailValid').hide();
        $('#alertCustomerPhoneComplete').hide();

        date.flatpickr({
            minDate: new Date().fp_incr(0),
            maxDate: new Date().fp_incr(7),
            dateFormat: 'Y-m-d',
            disableMobile: true
        })

        time.empty().trigger('change');
        const times = [];
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 15) {
                const hh = h.toString().padStart(2, '0');
                const mm = m.toString().padStart(2, '0');
                const label = `${hh}:${mm}`;
                times.push(new Option(label, `${hh}:${mm}:00`, false, false));
            }
        }
        time.append(times).trigger('change');

        //ตัวแปร array เกี่ยวกับ timezone
        const timezones = {
            AU: "Australia/Sydney",
            NZ: "Pacific/Auckland",
            US: "America/New_York",
            UK: "Europe/London",
            CA: "America/Toronto",
            TH: "Asia/Bangkok"
        };


        //เมื่อตัวแปร country จะเปลี่ยน class timeZone ทั้งจะกลายเป็นชื่อของแต่ล่ะ country ตัวอย่างเมื่อเลือก AU ตัวแปร timeZone จะเท่ากับ Australia/Sydney
        country.on('change', function () {
            const tz = timezones[country.val()] || '';
            $('#timeZone').text(tz);
            $('#timeZoneDisplay').text(tz);
            updatePhonePrefixes();
        });

        // Function to update phone input prefixes based on selected country
        function updatePhonePrefixes() {
            const selectedCountry = country.val();
            const dialCode = countryDialCodes[selectedCountry] || '';
            $('#contact_phone_prefix').text('+' + dialCode);
            $('#contact_mobile_prefix').text('+' + dialCode);
            // Reformat existing phone numbers with new country code
            formatPhoneNumber($('#contact_phone').val(), 'contact_phone');
            formatPhoneNumber($('#contact_mobile').val(), 'contact_mobile');
        }

        // Function to format phone number with country code
        function formatPhoneNumber(value, fieldId) {
            const selectedCountry = country.val();
            const dialCode = countryDialCodes[selectedCountry] || '';
            if (value && dialCode) {
                const formattedNumber = '+' + dialCode + value.replace(/^0+/, '');
                $('#' + fieldId + '_formatted').text('Formatted: ' + formattedNumber);
                // Update the actual input value for submission
                $('#' + fieldId).data('formatted', formattedNumber);
            } else {
                $('#' + fieldId + '_formatted').text('');
                $('#' + fieldId).data('formatted', '');
            }
        }


        // ✅ Step 3: โหลดเซลทั้งหมดในทีม
        $.get('../models/load_all_sales.php', function (res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                $('#sales').append(options).trigger('change');
            }
        });

        $.get('../models/load_all_sales.php', function (res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                $('#bookBy').append(options).trigger('change');
            }
        });

        // ✅ Step 5: แสดงเวลาทุกช่วงแบบ 15 นาที (00:00 - 23:45)
        date.on('change', function () {
            time.empty().trigger('change');
            const times = [];
            for (let h = 0; h < 24; h++) {
                for (let m = 0; m < 60; m += 15) {
                    const hh = h.toString().padStart(2, '0');
                    const mm = m.toString().padStart(2, '0');
                    const label = `${hh}:${mm}`;
                    times.push(new Option(label, `${hh}:${mm}:00`, false, false));
                }
            }
            time.append(times).trigger('change');
        });

        $('#bookingForm').on('submit', function (e) {

                e.preventDefault();
                $.post('../models/save_appointment.php', $(this).serialize(), function (res) {
                    if (res.status === 'ok') {
                        sendEmail();
                        bookCalendar();
                        alert('✅ Appointment has been booked.');

                        location.href = 'booking_success.php';
                    } else {
                        alert('❌ ' + res.message);
                    }
                });


        });//form submit

        country.on('change', updateThaiTimePreview);
        date.on('change', updateThaiTimePreview);
        time.on('change', updateThaiTimePreview);

        const countryToTimezones = {
            AU: ["Australia/Sydney", "Australia/Brisbane", "Australia/Perth", "Australia/Melbourne", "Australia/Adelaide", "Australia/Hobart", "Australia/Darwin"],
            US: ["America/New_York", "America/Chicago", "America/Denver", "America/Los_Angeles", "America/Phoenix", "America/Anchorage", "America/Indiana/Indianapolis", "America/Detroit", "America/Indiana/Knox" ,"Pacific/Honolulu"],
            CA: ["America/Toronto", "America/Vancouver", "America/Edmonton", "America/Winnipeg", "America/Halifax", "America/St_Johns", "America/Moncton", "America/Montreal","America/Regina"],
            UK: ["Europe/London"],
            NZ: ["Pacific/Auckland", "Pacific/Chatham"],
            TH: ["Asia/Bangkok"]
        };

        //เมื่อตัวแปร country ถูก'เปลี่ยน'
        country.on('change', function () {
            const selectedCountry = country.val();
            console.log('Country changed to:', selectedCountry);
            
            const zones = countryToTimezones[selectedCountry] || [];
            console.log('Available timezones:', zones);
            const timezoneSelect = $('#timezone');
            timezoneSelect.empty().append(`<option value="">-- Please Select --</option>`);
            zones.forEach(zone => {
                timezoneSelect.append(`<option value="${zone}">${zone}</option>`);
            });
            $('#timeZone').text(zones[0] || '-');
            $('#timeZoneDisplay').text(zones[0] || '-');

            console.log('countryToState:', countryToState);
            console.log('States for selected country:', countryToState[selectedCountry]);
            const states = countryToState[selectedCountry] || [];
            console.log('States array:', states);
            const stateSelect = $('#state');
            stateSelect.empty().append(`<option value="">-- Please Select --</option>`);
            states.forEach(state => {
                stateSelect.append(`<option value="${state.code}">${state.code} : ${state.name}</option>`);
            });
        });

        $('#state').on('change', function() {
            const selectedCode = $(this).val();
            const states = countryToState[country.val()] || [];
            const selectedState = states.find(s => s.code === selectedCode);
            if (selectedState) {
                $('#timezone').val(selectedState.timezone).trigger('change');
                $('#timeZone').text(selectedState.timezone);
                $('#timeZoneDisplay').text(selectedState.timezone);
            } else {
                $('#timezone').val('').trigger('change');
                $('#timeZone').text('');
                $('#timeZoneDisplay').text('');
            }
        });

        const countryToState = {
                AU: [
                    { code: "NSW", name: "New South Wales", timezone: "Australia/Sydney" },
                    { code: "VIC", name: "Victoria", timezone: "Australia/Melbourne" },
                    { code: "QLD", name: "Queensland", timezone: "Australia/Brisbane" },
                    { code: "SA", name: "South Australia", timezone: "Australia/Adelaide" },
                    { code: "WA", name: "Western Australia", timezone: "Australia/Perth" },
                    { code: "TAS", name: "Tasmania", timezone: "Australia/Hobart" },
                    { code: "NT", name: "Northern Territory", timezone: "Australia/Darwin" },
                    { code: "ACT", name: "Australian Capital Territory", timezone: "Australia/Sydney" } // ACT ใช้ timezone Sydney
                ],
                US: [
                    { code: "AL", name: "Alabama", timezone: "America/Chicago" },
                    { code: "AK", name: "Alaska", timezone: "America/Anchorage" },
                    { code: "AZ", name: "Arizona", timezone: "America/Phoenix" },
                    { code: "AR", name: "Arkansas", timezone: "America/Chicago" },
                    { code: "CA", name: "California", timezone: "America/Los_Angeles" },
                    { code: "CO", name: "Colorado", timezone: "America/Denver" },
                    { code: "CT", name: "Connecticut", timezone: "America/New_York" },
                    { code: "DE", name: "Delaware", timezone: "America/New_York" },
                    { code: "FL", name: "Florida", timezone: "America/New_York" },
                    { code: "GA", name: "Georgia", timezone: "America/New_York" },
                    { code: "HI", name: "Hawaii", timezone: "Pacific/Honolulu" },
                    { code: "ID", name: "Idaho", timezone: "America/Denver" },
                    { code: "IL", name: "Illinois", timezone: "America/Chicago" },
                    { code: "IN", name: "Indiana", timezone: "America/Indiana/Indianapolis" },
                    { code: "IA", name: "Iowa", timezone: "America/Chicago" },
                    { code: "KS", name: "Kansas", timezone: "America/Chicago" },
                    { code: "KY", name: "Kentucky", timezone: "America/New_York" },
                    { code: "LA", name: "Louisiana", timezone: "America/Chicago" },
                    { code: "ME", name: "Maine", timezone: "America/New_York" },
                    { code: "MD", name: "Maryland", timezone: "America/New_York" },
                    { code: "MA", name: "Massachusetts", timezone: "America/New_York" },
                    { code: "MI", name: "Michigan", timezone: "America/Detroit" },
                    { code: "MN", name: "Minnesota", timezone: "America/Chicago" },
                    { code: "MS", name: "Mississippi", timezone: "America/Chicago" },
                    { code: "MO", name: "Missouri", timezone: "America/Chicago" },
                    { code: "MT", name: "Montana", timezone: "America/Denver" },
                    { code: "NE", name: "Nebraska", timezone: "America/Chicago" },
                    { code: "NV", name: "Nevada", timezone: "America/Los_Angeles" },
                    { code: "NH", name: "New Hampshire", timezone: "America/New_York" },
                    { code: "NJ", name: "New Jersey", timezone: "America/New_York" },
                    { code: "NM", name: "New Mexico", timezone: "America/Denver" },
                    { code: "NY", name: "New York", timezone: "America/New_York" },
                    { code: "NC", name: "North Carolina", timezone: "America/New_York" },
                    { code: "ND", name: "North Dakota", timezone: "America/Chicago" },
                    { code: "OH", name: "Ohio", timezone: "America/New_York" },
                    { code: "OK", name: "Oklahoma", timezone: "America/Chicago" },
                    { code: "OR", name: "Oregon", timezone: "America/Los_Angeles" },
                    { code: "PA", name: "Pennsylvania", timezone: "America/New_York" },
                    { code: "RI", name: "Rhode Island", timezone: "America/New_York" },
                    { code: "SC", name: "South Carolina", timezone: "America/New_York" },
                    { code: "SD", name: "South Dakota", timezone: "America/Chicago" },
                    { code: "TN", name: "Tennessee", timezone: "America/Chicago" },
                    { code: "TX", name: "Texas", timezone: "America/Chicago" },
                    { code: "UT", name: "Utah", timezone: "America/Denver" },
                    { code: "VT", name: "Vermont", timezone: "America/New_York" },
                    { code: "VA", name: "Virginia", timezone: "America/New_York" },
                    { code: "WA", name: "Washington", timezone: "America/Los_Angeles" },
                    { code: "WV", name: "West Virginia", timezone: "America/New_York" },
                    { code: "WI", name: "Wisconsin", timezone: "America/Chicago" },
                    { code: "WY", name: "Wyoming", timezone: "America/Denver" }
                ],
                CA: [
                    { code: "AB", name: "Alberta", timezone: "America/Edmonton" },
                    { code: "BC", name: "British Columbia", timezone: "America/Vancouver" },
                    { code: "MB", name: "Manitoba", timezone: "America/Winnipeg" },
                    { code: "NB", name: "New Brunswick", timezone: "America/Moncton" },
                    { code: "NL", name: "Newfoundland and Labrador", timezone: "America/St_Johns" },
                    { code: "NS", name: "Nova Scotia", timezone: "America/Halifax" },
                    { code: "ON", name: "Ontario", timezone: "America/Toronto" },
                    { code: "PE", name: "Prince Edward Island", timezone: "America/Halifax" },
                    { code: "QC", name: "Quebec", timezone: "America/Montreal" },
                    { code: "SK", name: "Saskatchewan", timezone: "America/Regina" },
                    { code: "NT", name: "Northwest Territories", timezone: "America/Yellowknife" },
                    { code: "NU", name: "Nunavut", timezone: "America/Iqaluit" },
                    { code: "YT", name: "Yukon", timezone: "America/Whitehorse" }
                ],
                UK: [
                    { code: "ENG", name: "England", timezone: "Europe/London" },
                    { code: "SCT", name: "Scotland", timezone: "Europe/London" },
                    { code: "WLS", name: "Wales", timezone: "Europe/London" },
                    { code: "NIR", name: "Northern Ireland", timezone: "Europe/London" }
                ],
                NZ: [
                    { code: "AUK", name: "Auckland", timezone: "Pacific/Auckland" },
                    { code: "BOP", name: "Bay of Plenty", timezone: "Pacific/Auckland" },
                    { code: "CAN", name: "Canterbury", timezone: "Pacific/Auckland" },
                    { code: "CIT", name: "Chatham Islands Territory", timezone: "Pacific/Chatham" },
                    { code: "GIS", name: "Gisborne", timezone: "Pacific/Auckland" },
                    { code: "HKB", name: "Hawke's Bay", timezone: "Pacific/Auckland" },
                    { code: "MBH", name: "Marlborough", timezone: "Pacific/Auckland" },
                    { code: "MWT", name: "Manawatu-Wanganui", timezone: "Pacific/Auckland" },
                    { code: "NSN", name: "Nelson", timezone: "Pacific/Auckland" },
                    { code: "NTL", name: "Northland", timezone: "Pacific/Auckland" },
                    { code: "OTA", name: "Otago", timezone: "Pacific/Auckland" },
                    { code: "STL", name: "Southland", timezone: "Pacific/Auckland" },
                    { code: "TAS", name: "Tasman", timezone: "Pacific/Auckland" },
                    { code: "TKI", name: "Taranaki", timezone: "Pacific/Auckland" },
                    { code: "WGN", name: "Wellington", timezone: "Pacific/Auckland" },
                    { code: "WKO", name: "Waikato", timezone: "Pacific/Auckland" }
                ],
                TH: [
                    { code: "BKK", name: "Bangkok", timezone: "Asia/Bangkok" }
                ]
            };





    });//ready

    function nextStep(step) {
        $('.step-content').removeClass('active');
        $('#step-' + step).addClass('active');
        updateNavigation(step);
    }

    function prevStep(step) {
        $('.step-content').removeClass('active');
        $('#step-' + step).addClass('active');
        updateNavigation(step);
    }

    function goToStep(step) {
        // Validate current step before allowing navigation
        const currentStep = $('.step-content.active').attr('id').replace('step-', '');
        
        // Only allow going to completed steps or next step
        if (parseInt(step) < parseInt(currentStep) || canProceedToStep(step)) {
            $('.step-content').removeClass('active');
            $('#step-' + step).addClass('active');
            updateNavigation(step);
        }
    }

    function canProceedToStep(targetStep) {
        // Simple check - in production, track completed steps
        return true;
    }

    function updateNavigation(step) {
        // Update sidebar icons
        $('.sidebar-icon').removeClass('active');
        $('#nav-step-' + step).addClass('active');
        
        // Update progress ring
        const stepProgress = { 1: 25, 3: 50, 8: 75, 9: 100 };
        const progress = stepProgress[step] || 25;
        const circle = document.getElementById('progress-circle');
        const radius = circle.r.baseVal.value;
        const circumference = radius * 2 * Math.PI;
        const offset = circumference - (progress / 100) * circumference;
        circle.style.strokeDashoffset = offset;
        $('#progress-text').text(progress + '%');
        
        // Update step label
        const stepLabels = {
            1: 'Step 1 of 4: Business Info',
            3: 'Step 2 of 4: Schedule',
            8: 'Step 3 of 4: Customer Info',
            9: 'Step 4 of 4: Review'
        };
        $('#current-step-label').text(stepLabels[step] || '');
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep1() {
        if (shop_type.val() === '') {
            $('#alertShopType').show();
            shop_type.focus();
        } else if (country.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').show();
            country.focus();
        } else if (state.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').show();
            state.focus();
        } else if ($('#timezone').val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            alert('Please Select Timezone.');
            $('#timezone').focus();
        } else {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            nextStep(3);
        }
    }

    function validateStep3() {
        if (sales.val() === '') {
            $('#alertSale').show();
            sales.focus();
        } else if (bookBy.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').show();
            bookBy.focus();
        } else if (presentation.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').show();
            presentation.focus();
        } else if (date.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            alert('Please Select appointment date.');
            date.focus();
        } else if (time.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            alert('Please Select appointment time.');
            time.focus();
        } else {
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            nextStep(8);
        }
    }

    function sendEmail() {



        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        sendEmailPayload = {
            "staff_id": sales.val(),
            "staff_email": getStaffEmail(sales.val()),
            "book_by": getStaffEmail(bookBy.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": getNickname(selectedSale.text()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": getNickname(selectedBookBy.text()),
            "created_by": "1",
            "shop_type_id": shop_type.val(),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "timezone": $('#timezone').val(),
            "city": city.val(),
            "daythaionly" : thaiDayPreview,
            "timethaionly": thaiTimePreview,
            "end_timethaionly": addMinutes(thaiTimePreview,15),
            "state": state.val(),
            "date": date.val(),
            "time": time.val(),
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || contact_mobile.val(),
            "presentation": presentation.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "comment": comment.val(),
            "timetodaynow": timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("we call webhook UCanBookMe - email alert");
        console.log(sendEmailPayload);
        console.table(sendEmailPayload);

         const sendEmail = $.ajax({
             type: "POST",
             crossDomain: true,
             dataType: 'json',
             url: "https://hook.us1.make.com/yk8yef5sm9m5y4gr8qfj71ynmsorhz7d",
             data: sendEmailPayload
         });

         sendEmail.done(function (res) {
             console.log("send Email done");
             console.log("return = ",res);
         });

         sendEmail.fail(function(xhr, status, error) {
             console.log("ajax webhook fail!!");
             console.log(status + ': ' + error);
             //alert("Send fail!!");
         });
    }//sendEmail

    //ส่งจองลงปฏิทิน
    function bookCalendar() {

        const dtStamp = toUTCFormat(date.val(), time.val(),0);
        const dtStart = toUTCFormat(date.val(), time.val(),0);
        const dtEnd = toUTCFormat(date.val(), addMinutes(time.val(),15),0);
        const timezone = $('#timezone').val();

        const dtStartNoZ = dtStart.replace(/Z$/,'');
        const dtEndNoZ = dtEnd.replace(/Z$/,'');

        const dtStartReal = timezone + ":" + dtStartNoZ;
        const dtEndReal = timezone + ":" + dtEndNoZ;

        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        appointmentDetail = {
            "staff_email": getStaffEmail(sales.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": getNickname(selectedSale.text()),
            "book_by": getStaffEmail(bookBy.val()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": getNickname(selectedBookBy.text()),
            "shop_type": $("#shop_type option:selected").text(),
            "country": country.val(),
            "timezone": $('#timezone').val(),
            "daythaionly" : thaiDayPreview,
            "timethaionly": thaiTimePreview,
            "end_timethaionly": addMinutes(thaiTimePreview,15),
            "city": city.val(),
            "startDate": date.val(),
            "startTime": time.val(),
            "endDate": date.val(),
            "endTime": addMinutes(time.val(),15),
            "dtStamp": toUTCFormat(date.val(), time.val(),0),
            "dtStart": dtStartReal,
            "dtEnd": dtEndReal,
            "customer_name": customer_name.val(),
            "shop_name": shop_name.val(),
            "contact_email": contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || contact_mobile.val(),
            "presentation": presentation.val(),
            "line_id": line_id.val(),
            "whatsapp": whatsapp.val(),
            "address": address.val(),
            "comment": comment.val(),
            "timetodaynow": timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("dtStamp = ",dtStamp);
        console.log("dtStart = ",dtStart);
        console.log("dtEnd = ",dtEnd);

        console.log("we call webhook UCanBookMe - Appointment created");
        console.table(appointmentDetail);
        const makeAppointment = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'json',
            url: "https://hook.us1.make.com/hg8rqnmfuxry86lq4ylr967j9uifhlt1",
            data: appointmentDetail
        });

        makeAppointment.done(function (res) {
            console.log("make Appointment done");
            console.log("return = ",res);
        });

        makeAppointment.fail(function(xhr, status, error) {
            console.log("make Appointment webhook fail!!");
            console.log(status + ': ' + error);
            //alert("Send fail!!");
        });
    }//bookCalendar


    function getStaffEmail(id) {
        const map = {
            1: 'neung@localforyou.com',
            17: 'boom@localforyou.com',
            24: 'honey@localforyou.com',
            35: 'pluem@localforyou.com',
            38: 'pruek@localforyou.com',
            62: 'ball@localforyou.com',
            79: 'gun@localforyou.com',
            84: 'aon@localforyou.com',
            85: 'mild.th@localforyou.com',
            86: 'jiw@localforyou.com',
            90: 'foo.si@localforyou.com'
        };

        return map[id] || 'administrator@localforyou.com';
    }

    function getNickname(fullName) {
        if (!fullName) return '';
        return fullName.trim().split(' ')[0];
    }

    function toUTCFormat(dateStr, timeStr, offsetHours = 0) {
        const [year, month, day] = dateStr.split('-').map(Number);
        const [hour, minute, second] = timeStr.split(':').map(Number);

        const utcMillis = Date.UTC(year, month - 1, day, hour - offsetHours, minute, second || 0);
        const utcDate = new Date(utcMillis);

        const y = utcDate.getUTCFullYear();
        const m = String(utcDate.getUTCMonth() + 1).padStart(2, '0');
        const d = String(utcDate.getUTCDate()).padStart(2, '0');
        const h = String(utcDate.getUTCHours()).padStart(2, '0');
        const min = String(utcDate.getUTCMinutes()).padStart(2, '0');
        const s = String(utcDate.getUTCSeconds()).padStart(2, '0');

        return `${y}${m}${d}T${h}${min}${s}Z`;
    }

    function addMinutes(timeStr, minutesToAdd) {
        const [hour, minute, second] = timeStr.split(':').map(Number);
        const date = new Date(0, 0, 0, hour, minute, second || 0);
        date.setMinutes(date.getMinutes() + minutesToAdd);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        const s = String(date.getSeconds()).padStart(2, '0');

        return `${h}:${m}:${s}`;
    }

    const { DateTime } = luxon;

    const countryToTimezone = {
/*        AU: "Australia/Sydney",
        NZ: "Pacific/Auckland",
        US: "America/New_York",
        UK: "Europe/London",
        CA: "America/Toronto",
        TH: "Asia/Bangkok"*/
        AU: [
            { code: "NSW", name: "New South Wales", timezone: "Australia/Sydney" },
            { code: "VIC", name: "Victoria", timezone: "Australia/Melbourne" },
            { code: "QLD", name: "Queensland", timezone: "Australia/Brisbane" },
            { code: "SA", name: "South Australia", timezone: "Australia/Adelaide" },
            { code: "WA", name: "Western Australia", timezone: "Australia/Perth" },
            { code: "TAS", name: "Tasmania", timezone: "Australia/Hobart" },
            { code: "NT", name: "Northern Territory", timezone: "Australia/Darwin" },
            { code: "ACT", name: "Australian Capital Territory", timezone: "Australia/Sydney" } // ACT ใช้ timezone Sydney
        ],
        US: [
            { code: "AL", name: "Alabama", timezone: "America/Chicago" },
            { code: "AK", name: "Alaska", timezone: "America/Anchorage" },
            { code: "AZ", name: "Arizona", timezone: "America/Phoenix" },
            { code: "AR", name: "Arkansas", timezone: "America/Chicago" },
            { code: "CA", name: "California", timezone: "America/Los_Angeles" },
            { code: "CO", name: "Colorado", timezone: "America/Denver" },
            { code: "CT", name: "Connecticut", timezone: "America/New_York" },
            { code: "DE", name: "Delaware", timezone: "America/New_York" },
            { code: "FL", name: "Florida", timezone: "America/New_York" },
            { code: "GA", name: "Georgia", timezone: "America/New_York" },
            { code: "HI", name: "Hawaii", timezone: "Pacific/Honolulu" },
            { code: "ID", name: "Idaho", timezone: "America/Denver" },
            { code: "IL", name: "Illinois", timezone: "America/Chicago" },
            { code: "IN", name: "Indiana", timezone: "America/Indiana/Indianapolis" },
            { code: "IA", name: "Iowa", timezone: "America/Chicago" },
            { code: "KS", name: "Kansas", timezone: "America/Chicago" },
            { code: "KY", name: "Kentucky", timezone: "America/New_York" },
            { code: "LA", name: "Louisiana", timezone: "America/Chicago" },
            { code: "ME", name: "Maine", timezone: "America/New_York" },
            { code: "MD", name: "Maryland", timezone: "America/New_York" },
            { code: "MA", name: "Massachusetts", timezone: "America/New_York" },
            { code: "MI", name: "Michigan", timezone: "America/Detroit" },
            { code: "MN", name: "Minnesota", timezone: "America/Chicago" },
            { code: "MS", name: "Mississippi", timezone: "America/Chicago" },
            { code: "MO", name: "Missouri", timezone: "America/Chicago" },
            { code: "MT", name: "Montana", timezone: "America/Denver" },
            { code: "NE", name: "Nebraska", timezone: "America/Chicago" },
            { code: "NV", name: "Nevada", timezone: "America/Los_Angeles" },
            { code: "NH", name: "New Hampshire", timezone: "America/New_York" },
            { code: "NJ", name: "New Jersey", timezone: "America/New_York" },
            { code: "NM", name: "New Mexico", timezone: "America/Denver" },
            { code: "NY", name: "New York", timezone: "America/New_York" },
            { code: "NC", name: "North Carolina", timezone: "America/New_York" },
            { code: "ND", name: "North Dakota", timezone: "America/Chicago" },
            { code: "OH", name: "Ohio", timezone: "America/New_York" },
            { code: "OK", name: "Oklahoma", timezone: "America/Chicago" },
            { code: "OR", name: "Oregon", timezone: "America/Los_Angeles" },
            { code: "PA", name: "Pennsylvania", timezone: "America/New_York" },
            { code: "RI", name: "Rhode Island", timezone: "America/New_York" },
            { code: "SC", name: "South Carolina", timezone: "America/New_York" },
            { code: "SD", name: "South Dakota", timezone: "America/Chicago" },
            { code: "TN", name: "Tennessee", timezone: "America/Chicago" },
            { code: "TX", name: "Texas", timezone: "America/Chicago" },
            { code: "UT", name: "Utah", timezone: "America/Denver" },
            { code: "VT", name: "Vermont", timezone: "America/New_York" },
            { code: "VA", name: "Virginia", timezone: "America/New_York" },
            { code: "WA", name: "Washington", timezone: "America/Los_Angeles" },
            { code: "WV", name: "West Virginia", timezone: "America/New_York" },
            { code: "WI", name: "Wisconsin", timezone: "America/Chicago" },
            { code: "WY", name: "Wyoming", timezone: "America/Denver" }
        ],
        CA: [
            { code: "AB", name: "Alberta", timezone: "America/Edmonton" },
            { code: "BC", name: "British Columbia", timezone: "America/Vancouver" },
            { code: "MB", name: "Manitoba", timezone: "America/Winnipeg" },
            { code: "NB", name: "New Brunswick", timezone: "America/Moncton" },
            { code: "NL", name: "Newfoundland and Labrador", timezone: "America/St_Johns" },
            { code: "NS", name: "Nova Scotia", timezone: "America/Halifax" },
            { code: "ON", name: "Ontario", timezone: "America/Toronto" },
            { code: "PE", name: "Prince Edward Island", timezone: "America/Halifax" },
            { code: "QC", name: "Quebec", timezone: "America/Montreal" },
            { code: "SK", name: "Saskatchewan", timezone: "America/Regina" },
            { code: "NT", name: "Northwest Territories", timezone: "America/Yellowknife" },
            { code: "NU", name: "Nunavut", timezone: "America/Iqaluit" },
            { code: "YT", name: "Yukon", timezone: "America/Whitehorse" }
        ],
        UK: [
            { code: "ENG", name: "England", timezone: "Europe/London" },
            { code: "SCT", name: "Scotland", timezone: "Europe/London" },
            { code: "WLS", name: "Wales", timezone: "Europe/London" },
            { code: "NIR", name: "Northern Ireland", timezone: "Europe/London" }
        ],
        NZ: [
            { code: "AUK", name: "Auckland", timezone: "Pacific/Auckland" },
            { code: "BOP", name: "Bay of Plenty", timezone: "Pacific/Auckland" },
            { code: "CAN", name: "Canterbury", timezone: "Pacific/Auckland" },
            { code: "CIT", name: "Chatham Islands Territory", timezone: "Pacific/Chatham" },
            { code: "GIS", name: "Gisborne", timezone: "Pacific/Auckland" },
            { code: "HKB", name: "Hawke's Bay", timezone: "Pacific/Auckland" },
            { code: "MBH", name: "Marlborough", timezone: "Pacific/Auckland" },
            { code: "MWT", name: "Manawatu-Wanganui", timezone: "Pacific/Auckland" },
            { code: "NSN", name: "Nelson", timezone: "Pacific/Auckland" },
            { code: "NTL", name: "Northland", timezone: "Pacific/Auckland" },
            { code: "OTA", name: "Otago", timezone: "Pacific/Auckland" },
            { code: "STL", name: "Southland", timezone: "Pacific/Auckland" },
            { code: "TAS", name: "Tasman", timezone: "Pacific/Auckland" },
            { code: "TKI", name: "Taranaki", timezone: "Pacific/Auckland" },
            { code: "WGN", name: "Wellington", timezone: "Pacific/Auckland" },
            { code: "WKO", name: "Waikato", timezone: "Pacific/Auckland" }
        ],
        TH: [
            { code: "BKK", name: "Bangkok", timezone: "Asia/Bangkok" }
        ]
    };

/*    function updateThaiTimePreview() {
        const selectedCountry = country.val();
        const selectedState = state.val();
        const selectedDate = date.val();
        const selectedTime = time.val();
        const thTimePreview = $('#thTimePreview');

        if (!selectedCountry || !selectedDate || !selectedTime) {
            thTimePreview.text('');
            return;
        }

        /!*const timezone = countryToTimezone[selectedCountry] || 'Asia/Bangkok';*!/
        const timezone = countryToTimezone[selectedState] || 'Asia/Bangkok';
        console.log("selectedCountry = ", selectedCountry);
        console.log("selectedState = ", selectedState);
        const dateTimeInCustomerTZ = DateTime.fromISO(`${selectedDate}T${selectedTime}`, { zone: timezone });
        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');

        const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");

        thTimePreview.text(`⏰ BKK: ${thaiFormatted}`);
    }*/

    function updateThaiTimePreview() {
        const selectedCountry = country.val();
        const selectedStateCode = state.val();
        const selectedDate = date.val();
        const selectedTime = time.val();
        const thTimePreview = $('#thTimePreview');

        if (!selectedCountry || !selectedStateCode || !selectedDate || !selectedTime) {
            thTimePreview.text('');
            return;
        }

        let timezone = 'Asia/Bangkok';

        if (countryToTimezone[selectedCountry]) {
            const selectedState = countryToTimezone[selectedCountry].find(s => s.code === selectedStateCode);
            if (selectedState && selectedState.timezone) {
                timezone = selectedState.timezone;
            }
        }

        const dateTimeInCustomerTZ = DateTime.fromISO(`${selectedDate}T${selectedTime}`, { zone: timezone });

        // บางครั้ง dateTimeInCustomerTZ อาจ Invalid ถ้า timezone ผิด
        if (!dateTimeInCustomerTZ.isValid) {
            thTimePreview.text('⚠️ Invalid Date/Time');
            return;
        }

        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');
        const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");
        const onlyNumberTimeThai = dateTimeInThaiTZ.toFormat("HH:mm:ss");
        const onlyNumberDayThai = dateTimeInThaiTZ.toFormat("yyyy-MM-dd");

        thaiTimePreview = onlyNumberTimeThai;
        thaiDayPreview = onlyNumberDayThai;



        thTimePreview.text(`⏰ BKK: ${thaiFormatted}`);
        console.log('TimePreview' , thaiTimePreview);
    }


    function showReview() {

        let emailRegex = /^([a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?)$/;

        if (shop_type.val() === '') {
            $('#alertShopType').show();
            shop_type.focus();
            nextStep(1);
        }else if (country.val() === ''){
            $('#alertCountry').show();
            country.focus();
            nextStep(1);
            $('#alertShopType').hide();
        }else if (state.val() === ''){
            $('#alertState').show();
            state.focus();
            nextStep(1);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
        }else if (sales.val() === ''){
            $('#alertSale').show();
            sales.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
        }else if (bookBy.val() === ''){
            $('#alertBooking').show();
            bookBy.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
        }else if (presentation.val() === ''){
            $('#alertLanguage').show();
            presentation.focus();
            nextStep(3);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
        }else if (shop_name.val() === ''){
            $('#alertShopName').show();
            presentation.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
        }else if (customer_name.val() === ''){
            $('#alertCustomerName').show();
            customer_name.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
        }else if (contact_email.val() === ''){
            $('#alertCustomerEmail').show();
            contact_email.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
        }else if (!emailRegex.test(contact_email.val())){
            $('#alertCustomerEmailValid').show();
            contact_email.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
        }else if (contact_phone.val() === ''){
            $('#alertCustomerPhone').show();
            contact_phone.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
            $('#alertCustomerEmailValid').hide();
        }else if (contact_phone.val().length < 10){
            $('#alertCustomerPhoneComplete').show();
            contact_phone.focus();
            nextStep(8);
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            $('#alertSale').hide();
            $('#alertBooking').hide();
            $('#alertLanguage').hide();
            $('#alertShopName').hide();
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').hide();
            $('#alertCustomerEmailValid').hide();
            $('#alertCustomerPhone').hide();
        }else {

            const data = {
                "Shop Type": $("#shop_type option:selected").text(),
                "Country": $("#country option:selected").text(),
                "City": city.val() || '-',
                "State": state.val() || '-',
                "Salesperson": $("#sales option:selected").text() || '-',
                "Booking By": $("#bookBy option:selected").text() || '-',
                "Presentation": $("#presentation option:selected").text(),
                "Date": date.val(),
                "Time": `(${country.val()}) ${time.val().substring(0, 5)} = ${getThaiTimeText(date.val(), time.val())}`,
                "Shop Name": shop_name.val() || '-',
                "Customer Name": customer_name.val() || '-',
                "Email": contact_email.val() || '-',
                "Phone": contact_phone.val() || '-',
                "Mobile": contact_mobile.val() || '-',
                "Line ID": line_id.val() || '-',
                "WhatsApp": whatsapp.val() || '-',
                "Address": address.val() || '-',
                "Comment": comment.val() || '-'
            };

            const iconMap = {
                "Shop Type": "bi-shop",
                "Country": "bi-flag",
                "City": "bi-geo",
                "State": "bi-geo-alt-fill",
                "Salesperson": "bi-person",
                "Booking By": "bi bi-journal-bookmark-fill",
                "Presentation": "bi bi-translate",
                "Date": "bi-calendar",
                "Time": "bi-clock",
                "Shop Name": "bi-building",
                "Customer Name": "bi-person-circle",
                "Email": "bi-envelope",
                "Phone": "bi-telephone",
                "Mobile": "bi-phone",
                "Line ID": "bi-chat-dots",
                "WhatsApp": "bi-whatsapp",
                "Address": "bi-geo-alt-fill",
                "Comment": "bi bi-chat-right-text"
            };

            let html = '';
            for (const key in data) {
                html += `
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="bi ${iconMap[key]} me-2"></i>${key}
                        </h6>
                        <p class="card-text mb-0">${data[key]}</p>
                    </div>
                </div>
            </div>`;
            }

            $('#reviewSection').html(html);

            nextStep(9);
        }


    }

    function submitBooking() {

        $('#bookingForm').submit();

    }

    function getThaiTimeText(dateStr, timeStr) {
        try {
            const selectedZone = $('#timezone').val() || 'Asia/Bangkok';
            const local = luxon.DateTime.fromISO(`${dateStr}T${timeStr}`, { zone: selectedZone });
            const thai = local.setZone('Asia/Bangkok');
            return `(TH) : ${thai.toFormat('HH:mm')}`;
        } catch (e) {
            return '-';
        }
    }
</script>
</body>
</html>
