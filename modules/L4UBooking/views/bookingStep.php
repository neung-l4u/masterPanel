<?php
/**
 * Booking Step View (Clean Version)
 * Separated JavaScript to controllers/bookingController.js
 * Separated PHP logic to models/ShopTypeModel.php
 */

session_start();
require_once '../assets/db/db.php';
require_once '../assets/db/initDB.php';
require_once '../models/ShopTypeModel.php';

global $db;
$currentPage = basename($_SERVER['PHP_SELF']);
$tomorrow = date("Y-m-d H:i:s", strtotime("now"));

// Load shop types via model
$shopTypeModel = new ShopTypeModel($db);
$shopTypeOptions = $shopTypeModel->getOptionsHtml();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Appointment Booking - L4U</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Bootstrap Icons -->
    <link href="../../../assets/libs/bootstrap-5.3.3-dist/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="../assets/libs/select2/css/select2.min.css" rel="stylesheet"/>
    
    <!-- Vanilla Calendar Pro -->
    <link href="https://cdn.jsdelivr.net/npm/@uvarov.frontend/vanilla-calendar/build/vanilla-calendar.min.css" rel="stylesheet"/>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        .sidebar-icon {
            transition: all 0.2s ease;
        }
        .sidebar-icon:hover:not(:disabled) {
            transform: scale(1.05);
        }
        .sidebar-icon.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .sidebar-icon:disabled {
            cursor: not-allowed;
            opacity: 0.4;
            color: #d1d5db !important;
            background: transparent !important;
            transform: none !important;
        }
        .sidebar-icon.completed {
            color: #6b7280;
            background: transparent;
        }
        .sidebar-icon.completed:hover {
            background: #f3f4f6;
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
        
        /* Select2 Custom Styles for Staff Images */
        .select2-container {
            width: 100% !important;
        }
        
        .select2-container--default .select2-selection--single {
            height: auto;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            width: 100%;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            line-height: 1.5;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            right: 0.75rem;
        }
        .select2-dropdown {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .select2-results__option {
            padding: 0.5rem 1rem;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: #f3f4f6;
            color: #111827;
        }
        
        /* Vanilla Calendar Pro - Light Theme Styles */
        #calendar-container {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        #calendar-container .vanilla-calendar {
            width: 100% !important;
            max-width: none !important;
            min-width: 100% !important;
            background: white !important;
        }
        
        #calendar-container .vanilla-calendar-month,
        #calendar-container .vanilla-calendar-month__wrapper {
            width: 100% !important;
            max-width: none !important;
        }
        
        #calendar-container .vanilla-calendar-grid {
            width: 100% !important;
            max-width: none !important;
            display: grid !important;
            grid-template-columns: repeat(7, 1fr) !important;
        }
        
        #calendar-container .vanilla-calendar-grid__item {
            width: 100% !important;
        }
        
        #calendar-container .vanilla-calendar-header {
            color: #111827 !important;
            background: white !important;
        }
        
        #calendar-container .vanilla-calendar-arrow {
            color: #6b7280 !important;
            background: transparent !important;
        }
        
        #calendar-container .vanilla-calendar-arrow:hover {
            color: #667eea !important;
        }
        
        #calendar-container .vanilla-calendar-week {
            background: white !important;
        }
        
        #calendar-container .vanilla-calendar-week__day {
            color: #6b7280 !important;
        }
        
        #calendar-container .vanilla-calendar-week__day_weekend {
            color: #ef4444 !important;
        }
        
        #calendar-container .vanilla-calendar-days {
            background: white !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn {
            border-radius: 0.5rem !important;
            font-size: 1.1rem !important;
            height: 3rem !important;
            line-height: 3rem !important;
            width: 100% !important;
            max-width: none !important;
            color: #374151 !important;
            background: white !important;
            margin: 2px !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn:hover {
            background-color: #f3f4f6 !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn_selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn_today {
            border: 2px solid #667eea !important;
            color: #667eea !important;
            background: white !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn_disabled {
            color: #9ca3af !important;
            opacity: 0.5 !important;
            cursor: not-allowed !important;
        }
        
        #calendar-container .vanilla-calendar-day__btn_prev,
        #calendar-container .vanilla-calendar-day__btn_next {
            color: #9ca3af !important;
            background: transparent !important;
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
            
            <nav class="flex-1 flex flex-col gap-4">
                <button onclick="BookingController.goToStep(1)" id="nav-step-1" class="sidebar-icon active w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-shop text-xl"></i>
                </button>
                <button onclick="BookingController.goToStep(3)" id="nav-step-3" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-calendar-event text-xl"></i>
                </button>
                <button onclick="BookingController.goToStep(8)" id="nav-step-8" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
                    <i class="bi bi-person-vcard text-xl"></i>
                </button>
                <button onclick="BookingController.goToStep(9)" id="nav-step-9" class="sidebar-icon w-12 h-12 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100">
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
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-800">Sale Appointment Booking</h1>
                        <p class="text-sm text-gray-500 mt-1">Local For You - #1 Marketing Agency for Thai</p>
                    </div>
                    <span id="current-step-label" class="px-4 py-2 bg-indigo-50 text-indigo-600 rounded-full text-sm font-medium">
                        Step 1 of 4: Business Info
                    </span>
                </div>
            </header>

            <!-- Form -->
            <div class="p-8 max-w-6xl mx-auto">
                <form id="bookingForm" action="models/save_appointment.php" method="POST">
                    
                    <!-- Step 1: Business Info -->
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
                                    <select id="shop_type" name="shop_type" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                        <?php echo $shopTypeOptions; ?>
                                    </select>
                                    <p id="alertShopType" class="text-red-500 text-sm mt-1 hidden">Please Select Shop Type.</p>
                                </div>

                                <!-- Country -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Country <span class="text-red-500">*</span>
                                    </label>
                                    <select id="country" name="country" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                        <option value="">-- Select Country --</option>
                                        <option value="AU">🇦🇺 Australia</option>
                                        <option value="NZ">🇳🇿 New Zealand</option>
                                        <option value="US">🇺🇸 United States</option>
                                        <option value="UK">🇬🇧 United Kingdom</option>
                                        <option value="CA">🇨🇦 Canada</option>
                                        <option value="TH">🇹🇭 Thailand</option>
                                    </select>
                                    <p id="alertCountry" class="text-red-500 text-sm mt-1 hidden">Please Select Country.</p>
                                </div>

                                <!-- State -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        State <span class="text-red-500">*</span>
                                    </label>
                                    <select id="state" name="state" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                        <option value="">-- Select State --</option>
                                    </select>
                                    <p id="alertState" class="text-red-500 text-sm mt-1 hidden">Please Select State.</p>
                                </div>

                                <!-- Timezone -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Timezone <span class="text-red-500">*</span>
                                    </label>
                                    <select id="timezone" name="timezone" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                        <option value="">-- Select Timezone --</option>
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">Current: <span id="timeZone" class="text-indigo-600 font-medium">-</span></p>
                                </div>

                                <!-- City -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">City (Optional)</label>
                                    <input type="text" id="city" name="city" placeholder="Enter city name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="button" onclick="BookingController.validateStep1()" 
                                        class="btn-primary-gradient px-6 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    Continue <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Schedule -->
                    <div id="step-3" class="step-content">
                        <div class="form-card p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="bi bi-calendar-event text-purple-600 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">Schedule Appointment</h2>
                                    <p class="text-sm text-gray-500">Select date, time and salesperson</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-12 gap-8">
                                <!-- Left: Large Calendar (8 columns) -->
                                <div class="col-span-8">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Appointment Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="hidden" id="date" name="date" value="<?php echo htmlspecialchars($tomorrow); ?>">
                                    <div id="calendar-container" class="bg-white rounded-xl border border-gray-200 p-4 w-full"></div>
                                </div>

                                <!-- Right: Inputs (4 columns) -->
                                <div class="col-span-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Salesperson <span class="text-red-500">*</span>
                                        </label>
                                        <select id="sales" name="sales" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                            <option value="">-- Select Salesperson --</option>
                                        </select>
                                        <p id="alertSale" class="text-red-500 text-sm mt-1 hidden">Please Select Sale.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Booked By <span class="text-red-500">*</span>
                                        </label>
                                        <select id="bookBy" name="bookBy" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                            <option value="">-- Select Booked By --</option>
                                        </select>
                                        <p id="alertBooking" class="text-red-500 text-sm mt-1 hidden">Please Select Booking.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Presentation Language <span class="text-red-500">*</span>
                                        </label>
                                        <select id="presentation" name="presentation" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                            <option value="">-- Select Language --</option>
                                            <option value="English">🇬🇧 English</option>
                                            <option value="Thai">🇹🇭 Thai</option>
                                        </select>
                                        <p id="alertLanguage" class="text-red-500 text-sm mt-1 hidden">Please Select Language.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Available Time <span class="text-gray-500 font-normal">(<span id="timeZoneDisplay">-</span>)</span>
                                        </label>
                                        <select id="time" name="time" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                                            <option value="">-- Select Time --</option>
                                        </select>
                                        <p class="text-sm text-gray-500 mt-2">
                                            <i class="bi bi-clock text-indigo-500 mr-1"></i>
                                            <span id="thTimePreview"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8 pt-6 border-t border-gray-100">
                                <button type="button" onclick="BookingController.prevStep(1)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="BookingController.validateStep3()" 
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
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Shop Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="shop_name" name="shop_name" placeholder="Enter shop name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertShopName" class="text-red-500 text-sm mt-1 hidden">Please enter the shop name</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Customer Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="customer_name" name="customer_name" placeholder="Enter customer name" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertCustomerName" class="text-red-500 text-sm mt-1 hidden">Please enter Customer name</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" id="contact_email" name="contact_email" placeholder="email@example.com" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <p id="alertCustomerEmail" class="text-red-500 text-sm mt-1 hidden">Please enter Customer email</p>
                                    <p id="alertCustomerEmailValid" class="text-red-500 text-sm mt-1 hidden">Please enter a valid email address.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Contact Phone <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex">
                                        <span id="contact_phone_prefix" class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-600 font-medium">+</span>
                                        <input type="text" id="contact_phone" name="contact_phone" placeholder="Number only e.g. 0930396203" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); BookingController.formatPhoneNumber(this.value, 'contact_phone');"
                                               class="input-field flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">without country's code</p>
                                    <p id="contact_phone_formatted" class="text-sm text-indigo-600 mt-1 font-medium"></p>
                                    <p id="alertCustomerPhone" class="text-red-500 text-sm mt-1 hidden">Please enter Phone number</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Mobile</label>
                                    <div class="flex">
                                        <span id="contact_mobile_prefix" class="inline-flex items-center px-4 py-3 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-600 font-medium">+</span>
                                        <input type="text" id="contact_mobile" name="contact_mobile" placeholder="Number only e.g. 0930396203" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); BookingController.formatPhoneNumber(this.value, 'contact_mobile');"
                                               class="input-field flex-1 px-4 py-3 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">without country's code</p>
                                    <p id="contact_mobile_formatted" class="text-sm text-indigo-600 mt-1 font-medium"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Line ID</label>
                                    <input type="text" id="line_id" name="line_id" placeholder="LINE ID" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                                    <input type="text" id="whatsapp" name="whatsapp" placeholder="WhatsApp number" 
                                           class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                    <textarea id="address" name="address" rows="3" placeholder="Enter address" 
                                              class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
                                    <textarea id="comment" name="comment" rows="3" placeholder="Additional comments" 
                                              class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"></textarea>
                                </div>
                            </div>

                            <div class="flex justify-between mt-8">
                                <button type="button" onclick="BookingController.prevStep(3)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="BookingController.showReview()" 
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

                            <div id="reviewSection" class="flex flex-col gap-4 mb-8">
                                <!-- Review sections inserted here -->
                            </div>

                            <div class="flex justify-between">
                                <button type="button" onclick="BookingController.prevStep(8)" 
                                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                                <button type="button" onclick="BookingController.submitBooking()" 
                                        class="btn-primary-gradient px-8 py-3 text-white rounded-lg font-medium flex items-center gap-2">
                                    <i class="bi bi-check-lg"></i> Confirm & Book
                                </button>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" value="<?php echo htmlspecialchars($tomorrow); ?>" name="timeToDayNow" id="timeToDayNow">
                </form>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../assets/libs/jQuery-v3.7.1/jquery-3.7.1.min.js"></script>
    <script src="../assets/libs/select2/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@uvarov.frontend/vanilla-calendar/build/vanilla-calendar.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>
    <script src="../controllers/bookingController.js"></script>
</body>
</html>
