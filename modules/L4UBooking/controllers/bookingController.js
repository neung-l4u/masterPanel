/**
 * L4U Booking Controller
 * Handles all frontend logic for the booking form
 */

// Global variables
const BookingController = {
    // DOM Elements
    elements: {},
    
    // Data variables
    appointmentDetail: {},
    sendEmailPayload: {},
    thaiTimePreview: "",
    thaiDayPreview: "",
    
    // Constants
    timeZoneMap: {
        "AU": "Australia/Sydney",
        "NZ": "Pacific/Auckland",
        "US": "America/New_York",
        "UK": "Europe/London",
        "CA": "America/Toronto",
        "TH": "Asia/Bangkok"
    },
    
    countryDialCodes: {
        "AU": "61",
        "NZ": "64",
        "US": "1",
        "UK": "44",
        "CA": "1",
        "TH": "66"
    },
    
    countryToTimezones: {
        AU: ["Australia/Sydney", "Australia/Brisbane", "Australia/Perth", "Australia/Melbourne", "Australia/Adelaide", "Australia/Hobart", "Australia/Darwin"],
        US: ["America/New_York", "America/Chicago", "America/Denver", "America/Los_Angeles", "America/Phoenix", "America/Anchorage", "America/Indiana/Indianapolis", "America/Detroit", "America/Indiana/Knox", "Pacific/Honolulu"],
        CA: ["America/Toronto", "America/Vancouver", "America/Edmonton", "America/Winnipeg", "America/Halifax", "America/St_Johns", "America/Moncton", "America/Montreal", "America/Regina"],
        UK: ["Europe/London"],
        NZ: ["Pacific/Auckland", "Pacific/Chatham"],
        TH: ["Asia/Bangkok"]
    },

    countryToState: {
        AU: [
            { code: "NSW", name: "New South Wales", timezone: "Australia/Sydney" },
            { code: "VIC", name: "Victoria", timezone: "Australia/Melbourne" },
            { code: "QLD", name: "Queensland", timezone: "Australia/Brisbane" },
            { code: "SA", name: "South Australia", timezone: "Australia/Adelaide" },
            { code: "WA", name: "Western Australia", timezone: "Australia/Perth" },
            { code: "TAS", name: "Tasmania", timezone: "Australia/Hobart" },
            { code: "NT", name: "Northern Territory", timezone: "Australia/Darwin" },
            { code: "ACT", name: "Australian Capital Territory", timezone: "Australia/Sydney" }
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
    },

    /**
     * Initialize the controller
     */
    init: function() {
        this.cacheElements();
        this.bindEvents();
        this.loadInitialData();
        this.hideAllAlerts();
        this.setupDatePicker();
        this.setupTimeOptions();
        
        // Set initial sidebar navigation state
        this.updateNavigation(1);
    },

    /**
     * Cache DOM elements
     */
    cacheElements: function() {
        this.elements = {
            shop_type: $('#shop_type'),
            country: $('#country'),
            city: $('#city'),
            timeZone: $('#timeZone'),
            timeZoneDisplay: $('#timeZoneDisplay'),
            sales: $('#sales'),
            bookBy: $('#bookBy'),
            presentation: $('#presentation'),
            date: $('#date'),
            time: $('#time'),
            shop_name: $('#shop_name'),
            customer_name: $('#customer_name'),
            contact_email: $('#contact_email'),
            contact_phone: $('#contact_phone'),
            contact_mobile: $('#contact_mobile'),
            line_id: $('#line_id'),
            whatsapp: $('#whatsapp'),
            address: $('#address'),
            comment: $('#comment'),
            state: $('#state'),
            timezone: $('#timezone'),
            timeToDayNow: $('#timeToDayNow'),
            thTimePreview: $('#thTimePreview')
        };
    },

    /**
     * Bind all event listeners
     */
    bindEvents: function() {
        const self = this;
        
        // Country change handler
        this.elements.country.on('change', function() {
            self.handleCountryChange();
        });

        // State change handler
        this.elements.state.on('change', function() {
            self.handleStateChange();
        });

        // Time change handler for Thai time preview
        this.elements.time.on('change', function() {
            self.updateThaiTimePreview();
        });

        // Form submit handler - prevent default, actual submission handled by Confirm & Book button
        $('#bookingForm').on('submit', function(e) {
            e.preventDefault();
            // Form submission is handled directly by submitBooking() called from Confirm & Book button
        });
    },

    /**
     * Load initial data (sales, bookBy)
     */
    loadInitialData: function() {
        const self = this;
        
        // Store staff data for template rendering
        this.staffData = {};
        
        // Load sales
        $.get('../models/load_all_sales.php', function(res) {
            if (res.status === 'ok') {
                // Store staff data with pictures
                res.data.forEach(user => {
                    self.staffData[user.id] = user;
                });
                
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                self.elements.sales.append(options);
                
                // Initialize Select2 with custom template
                self.elements.sales.select2({
                    placeholder: 'Select salesperson',
                    templateResult: function(data) {
                        return self.formatStaffOption(data);
                    },
                    templateSelection: function(data) {
                        return self.formatStaffSelection(data);
                    }
                });
            }
        });

        // Load bookBy
        $.get('../models/load_all_sales.php', function(res) {
            if (res.status === 'ok') {
                let options = res.data.map(user => new Option(user.text, user.id, false, false));
                self.elements.bookBy.append(options);
                
                // Initialize Select2 with custom template
                self.elements.bookBy.select2({
                    placeholder: 'Select booked by',
                    templateResult: function(data) {
                        return self.formatStaffOption(data);
                    },
                    templateSelection: function(data) {
                        return self.formatStaffSelection(data);
                    }
                });
            }
        });
    },

    /**
     * Format staff option with image for dropdown
     */
    formatStaffOption: function(data) {
        if (!data.id) return data.text;
        
        const staff = this.staffData[data.id];
        const picUrl = staff && staff.pic ? staff.pic : null;
        const defaultAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.text) + '&background=667eea&color=fff&size=32';
        
        const imgUrl = picUrl || defaultAvatar;
        
        return $(`
            <div class="flex items-center gap-3 py-2">
                <img src="${imgUrl}" 
                     class="w-8 h-8 rounded-full object-cover border border-gray-200"
                     onerror="this.src='${defaultAvatar}'">
                <span class="text-gray-800">${data.text}</span>
            </div>
        `);
    },

    /**
     * Format staff selection with image
     */
    formatStaffSelection: function(data) {
        if (!data.id) return data.text;
        
        const staff = this.staffData[data.id];
        const picUrl = staff && staff.pic ? staff.pic : null;
        const defaultAvatar = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(data.text) + '&background=667eea&color=fff&size=24';
        
        const imgUrl = picUrl || defaultAvatar;
        
        return $(`
            <div class="flex items-center gap-2">
                <img src="${imgUrl}" 
                     class="w-6 h-6 rounded-full object-cover border border-gray-200"
                     onerror="this.src='${defaultAvatar}'">
                <span>${data.text}</span>
            </div>
        `);
    },

    /**
     * Hide all alert messages
     */
    hideAllAlerts: function() {
        $('#alertShopType, #alertCountry, #alertState, #alertSale, #alertBooking, #alertLanguage, #alertShopName, #alertCustomerName, #alertCustomerEmail, #alertCustomerPhone, #alertCustomerEmailValid').hide();
    },

    /**
     * Setup date picker with Vanilla Calendar Pro (inline, stable)
     */
    setupDatePicker: function() {
        const self = this;
        const today = new Date();
        const minDate = today.toISOString().split('T')[0];
        const maxDate = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        
        // Get initial date from hidden input, default to tomorrow
        const initialDate = this.elements.date.val() || minDate;
        
        this.vanillaCalendar = new VanillaCalendar('#calendar-container', {
            type: 'default',
            settings: {
                range: {
                    min: minDate,
                    max: maxDate,
                },
                selection: {
                    day: 'single',
                },
                selected: {
                    dates: [initialDate],
                },
                visibility: {
                    theme: 'light',
                },
            },
            actions: {
                clickDay(event, self) {
                    console.log('clickDay fired!', event, self);
                    
                    // Try to get date from self.selectedDates
                    let selectedDate = null;
                    
                    if (self && self.selectedDates && self.selectedDates.length > 0) {
                        selectedDate = self.selectedDates[0];
                    }
                    
                    // Fallback: try to get from event target
                    if (!selectedDate && event && event.target) {
                        const dayBtn = event.target.closest('.vanilla-calendar-day__btn');
                        if (dayBtn) {
                            selectedDate = dayBtn.dataset.calendarDay;
                        }
                    }
                    
                    // Fallback: get currently selected from calendar
                    if (!selectedDate && self && self.settings && self.settings.selected && self.settings.selected.dates) {
                        selectedDate = self.settings.selected.dates[0];
                    }
                    
                    console.log('Selected date:', selectedDate);
                    
                    if (selectedDate) {
                        $('#date').val(selectedDate);
                        console.log('Set #date to:', selectedDate);
                        console.log('#date value now:', $('#date').val());
                        BookingController.updateThaiTimePreview();
                        BookingController.updateTimeOptions();
                    }
                },
            },
        });
        
        this.vanillaCalendar.init();
    },

    /**
     * Update time options based on selected date
     */
    updateTimeOptions: function() {
        const times = [];
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 15) {
                const hh = h.toString().padStart(2, '0');
                const mm = m.toString().padStart(2, '0');
                const label = `${hh}:${mm}`;
                times.push(new Option(label, `${hh}:${mm}:00`, false, false));
            }
        }
        this.elements.time.empty().append(times).trigger('change');
    },

    /**
     * Setup time options (15 min intervals)
     */
    setupTimeOptions: function() {
        const times = [];
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 15) {
                const hh = h.toString().padStart(2, '0');
                const mm = m.toString().padStart(2, '0');
                const label = `${hh}:${mm}`;
                times.push(new Option(label, `${hh}:${mm}:00`, false, false));
            }
        }
        this.elements.time.append(times).trigger('change');
    },

    /**
     * Handle country change
     */
    handleCountryChange: function() {
        const selectedCountry = this.elements.country.val();
        console.log('Country changed to:', selectedCountry);

        // Update timezone options
        const zones = this.countryToTimezones[selectedCountry] || [];
        const timezoneSelect = this.elements.timezone;
        timezoneSelect.empty().append(`<option value="">-- Please Select --</option>`);
        zones.forEach(zone => {
            timezoneSelect.append(`<option value="${zone}">${zone}</option>`);
        });
        
        this.elements.timeZone.text(zones[0] || '-');
        this.elements.timeZoneDisplay.text(zones[0] || '-');

        // Update state options
        console.log('countryToState:', this.countryToState);
        console.log('States for selected country:', this.countryToState[selectedCountry]);
        const states = this.countryToState[selectedCountry] || [];
        console.log('States array:', states);
        
        const stateSelect = this.elements.state;
        stateSelect.empty().append(`<option value="">-- Please Select --</option>`);
        states.forEach(state => {
            stateSelect.append(`<option value="${state.code}">${state.code} : ${state.name}</option>`);
        });

        // Update phone prefixes
        this.updatePhonePrefixes();
    },

    /**
     * Handle state change
     */
    handleStateChange: function() {
        const selectedCode = this.elements.state.val();
        const selectedCountry = this.elements.country.val();
        const states = this.countryToState[selectedCountry] || [];
        const selectedState = states.find(s => s.code === selectedCode);
        
        if (selectedState) {
            this.elements.timezone.val(selectedState.timezone).trigger('change');
            this.elements.timeZone.text(selectedState.timezone);
            this.elements.timeZoneDisplay.text(selectedState.timezone);
        } else {
            this.elements.timezone.val('').trigger('change');
            this.elements.timeZone.text('');
            this.elements.timeZoneDisplay.text('');
        }
        
        this.updateThaiTimePreview();
    },

    /**
     * Update phone input prefixes
     */
    updatePhonePrefixes: function() {
        const selectedCountry = this.elements.country.val();
        const dialCode = this.countryDialCodes[selectedCountry] || '';
        $('#contact_phone_prefix').text('+' + dialCode);
        $('#contact_mobile_prefix').text('+' + dialCode);
        
        this.formatPhoneNumber(this.elements.contact_phone.val(), 'contact_phone');
        this.formatPhoneNumber(this.elements.contact_mobile.val(), 'contact_mobile');
    },

    /**
     * Format phone number with country code
     */
    formatPhoneNumber: function(value, fieldId) {
        const selectedCountry = this.elements.country.val();
        const dialCode = this.countryDialCodes[selectedCountry] || '';
        
        if (value && dialCode) {
            const formattedNumber = '+' + dialCode + value.replace(/^0+/, '');
            $('#' + fieldId + '_formatted').text('Formatted: ' + formattedNumber);
            $('#' + fieldId).data('formatted', formattedNumber);
        } else {
            $('#' + fieldId + '_formatted').text('');
            $('#' + fieldId).data('formatted', '');
        }
    },

    /**
     * Update Thai time preview
     */
    updateThaiTimePreview: function() {
        const { DateTime } = luxon;
        const selectedCountry = this.elements.country.val();
        const selectedStateCode = this.elements.state.val();
        const selectedDate = this.elements.date.val();
        const selectedTime = this.elements.time.val();
        const thTimePreview = this.elements.thTimePreview;

        if (!selectedCountry || !selectedStateCode || !selectedDate) {
            thTimePreview.text('');
            return;
        }

        let timezone = 'Asia/Bangkok';

        if (this.countryToState[selectedCountry]) {
            const selectedState = this.countryToState[selectedCountry].find(s => s.code === selectedStateCode);
            if (selectedState && selectedState.timezone) {
                timezone = selectedState.timezone;
            }
        }

        // Calculate day in Thai timezone (use default time if not selected)
        // Extract only date part (in case it has time attached)
        const datePart = selectedDate ? selectedDate.split(' ')[0] : '';
        // Ensure time is in HH:mm:ss format
        let timeForCalculation = selectedTime || '00:00:00';
        if (timeForCalculation.length === 5) timeForCalculation += ':00';
        
        const isoString = `${datePart}T${timeForCalculation}`;
        console.log('ISO String:', isoString, 'Timezone:', timezone);
        
        const dateTimeInCustomerTZ = DateTime.fromISO(isoString, { zone: timezone });

        if (!dateTimeInCustomerTZ.isValid) {
            console.log('Invalid DateTime:', dateTimeInCustomerTZ.invalidReason);
            thTimePreview.text('⚠️ Invalid Date/Time');
            return;
        }

        const dateTimeInThaiTZ = dateTimeInCustomerTZ.setZone('Asia/Bangkok');
        const onlyNumberDayThai = dateTimeInThaiTZ.toFormat("yyyy-MM-dd");
        this.thaiDayPreview = onlyNumberDayThai;

        // Only calculate time if selected
        if (selectedTime) {
            const thaiFormatted = dateTimeInThaiTZ.toFormat("HH:mm (ccc dd MMM)");
            const onlyNumberTimeThai = dateTimeInThaiTZ.toFormat("HH:mm:ss");
            this.thaiTimePreview = onlyNumberTimeThai;
            thTimePreview.text(`⏰ BKK: ${thaiFormatted}`);
            console.log('TimePreview', this.thaiTimePreview);
        } else {
            this.thaiTimePreview = '';
            thTimePreview.text('');
        }
    },

    /**
     * Navigation functions
     */
    // Track completed steps (persist in session)
    completedSteps: [], // Start empty - no steps completed initially
    
    nextStep: function(step) {
        const currentStep = $('.step-content.active').attr('id').replace('step-', '');
        
        // Add current step to completed steps before moving
        if (!this.completedSteps.includes(parseInt(currentStep))) {
            this.completedSteps.push(parseInt(currentStep));
        }
        
        $('.step-content').removeClass('active');
        $('#step-' + step).addClass('active');
        this.updateNavigation(step);
    },

    prevStep: function(step) {
        $('.step-content').removeClass('active');
        $('#step-' + step).addClass('active');
        this.updateNavigation(step);
    },

    goToStep: function(step) {
        const currentStep = parseInt($('.step-content.active').attr('id').replace('step-', ''));
        const targetStep = parseInt(step);
        const stepOrder = [1, 3, 8, 9];
        
        // Get step positions
        const currentIndex = stepOrder.indexOf(currentStep);
        const targetIndex = stepOrder.indexOf(targetStep);
        
        // Check if target is step 1 (always accessible as starting point)
        const isStep1 = targetStep === 1;
        
        // Allow navigation if:
        // 1. Target is step 1 (starting point)
        // 2. Going backward (to any previous step in the sequence)
        // 3. Step is already completed
        // 4. Is the next immediate step AND current step is completed
        const isBackward = targetIndex < currentIndex;
        const isCompleted = this.completedSteps.includes(targetStep);
        const isNextStep = targetStep === this.getNextStep(currentStep);
        const currentIsCompleted = this.completedSteps.includes(currentStep);
        
        // Only allow to next step if current step is completed
        const canGoNext = isNextStep && currentIsCompleted;
        
        const isAccessible = isStep1 || isBackward || isCompleted || canGoNext;
        
        if (isAccessible) {
            $('.step-content').removeClass('active');
            $('#step-' + step).addClass('active');
            this.updateNavigation(step);
        }
    },
    
    getNextStep: function(currentStep) {
        const stepOrder = [1, 3, 8, 9];
        const currentIndex = stepOrder.indexOf(currentStep);
        return currentIndex < stepOrder.length - 1 ? stepOrder[currentIndex + 1] : null;
    },

    canProceedToStep: function(targetStep) {
        return this.completedSteps.includes(targetStep) || targetStep === this.getNextStep(Math.max(...this.completedSteps));
    },

    updateNavigation: function(step) {
        const stepOrder = [1, 3, 8, 9];
        const maxCompletedStep = this.completedSteps.length > 0 ? Math.max(...this.completedSteps) : 0;
        const currentStepNum = parseInt(step);
        
        // Update all sidebar icons
        stepOrder.forEach(s => {
            const $icon = $('#nav-step-' + s);
            $icon.removeClass('active');
            
            // Determine icon state
            if (s === currentStepNum) {
                // Current step - active gradient
                $icon.addClass('active');
                $icon.prop('disabled', false);
                $icon.css('cursor', 'pointer');
            } else if (this.completedSteps.includes(s)) {
                // Completed step - clickable but not active
                $icon.prop('disabled', false);
                $icon.css({
                    'cursor': 'pointer',
                    'opacity': '1',
                    'background': 'transparent',
                    'color': '#6b7280'
                });
            } else if (s === 1) {
                // Step 1 is always accessible as starting point
                $icon.prop('disabled', false);
                $icon.css({
                    'cursor': 'pointer',
                    'opacity': '1',
                    'background': 'transparent',
                    'color': '#6b7280'
                });
            } else if (s === this.getNextStep(maxCompletedStep)) {
                // Next immediate step after max completed - check if accessible
                // Only enable if we're on the step before it
                const isAccessible = currentStepNum === maxCompletedStep || this.completedSteps.includes(maxCompletedStep);
                if (isAccessible) {
                    $icon.prop('disabled', false);
                    $icon.css({
                        'cursor': 'pointer',
                        'opacity': '1',
                        'background': '#f3f4f6',
                        'color': '#9ca3af'
                    });
                } else {
                    // Not accessible yet - disabled
                    $icon.prop('disabled', true);
                    $icon.css({
                        'cursor': 'not-allowed',
                        'opacity': '0.4',
                        'background': 'transparent',
                        'color': '#d1d5db'
                    });
                }
            } else {
                // Future step - disabled and grayed out
                $icon.prop('disabled', true);
                $icon.css({
                    'cursor': 'not-allowed',
                    'opacity': '0.4',
                    'background': 'transparent',
                    'color': '#d1d5db'
                });
            }
        });
        
        $('#nav-step-' + step).addClass('active');
        
        const stepProgress = { 1: 25, 3: 50, 8: 75, 9: 100 };
        const progress = stepProgress[step] || 25;
        const circle = document.getElementById('progress-circle');
        if (circle) {
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            const offset = circumference - (progress / 100) * circumference;
            circle.style.strokeDashoffset = offset;
        }
        $('#progress-text').text(progress + '%');
        
        const stepLabels = {
            1: 'Step 1 of 4: Business Info',
            3: 'Step 2 of 4: Schedule',
            8: 'Step 3 of 4: Customer Info',
            9: 'Step 4 of 4: Review'
        };
        $('#current-step-label').text(stepLabels[step] || '');
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    /**
     * Mark a step as completed
     */
    markStepCompleted: function(step) {
        if (!this.completedSteps.includes(step)) {
            this.completedSteps.push(step);
        }
    },

    /**
     * Validation functions
     */
    validateStep1: function() {
        const e = this.elements;
        
        if (e.shop_type.val() === '') {
            $('#alertShopType').show();
            e.shop_type.focus();
        } else if (e.country.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').show();
            e.country.focus();
        } else if (e.state.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').show();
            e.state.focus();
        } else if (e.timezone.val() === '') {
            $('#alertShopType').hide();
            $('#alertCountry').hide();
            $('#alertState').hide();
            alert('Please Select Timezone.');
            e.timezone.focus();
        } else {
            $('#alertShopType, #alertCountry, #alertState').hide();
            this.markStepCompleted(1); // Mark Step 1 as completed
            this.nextStep(3);
        }
    },

    validateStep3: function() {
        const e = this.elements;
        
        if (e.sales.val() === '') {
            $('#alertSale').show();
            e.sales.focus();
        } else if (e.bookBy.val() === '') {
            $('#alertSale').hide();
            $('#alertBooking').show();
            e.bookBy.focus();
        } else if (e.presentation.val() === '') {
            $('#alertSale, #alertBooking').hide();
            $('#alertLanguage').show();
            e.presentation.focus();
        } else if (e.date.val() === '') {
            $('#alertSale, #alertBooking, #alertLanguage').hide();
            alert('Please Select appointment date.');
            e.date.focus();
        } else if (e.time.val() === '') {
            $('#alertSale, #alertBooking, #alertLanguage').hide();
            alert('Please Select appointment time.');
            e.time.focus();
        } else {
            $('#alertSale, #alertBooking, #alertLanguage').hide();
            this.markStepCompleted(3); // Mark Step 3 as completed
            this.nextStep(8);
        }
    },

    showReview: function() {
        const e = this.elements;
        const emailRegex = /^([a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?)$/;

        // Check all required fields
        if (e.shop_type.val() === '') {
            $('#alertShopType').show();
            e.shop_type.focus();
            this.prevStep(1);
            return;
        } else if (e.country.val() === '') {
            $('#alertCountry').show();
            e.country.focus();
            this.prevStep(1);
            return;
        } else if (e.state.val() === '') {
            $('#alertState').show();
            e.state.focus();
            this.prevStep(1);
            return;
        } else if (e.sales.val() === '') {
            $('#alertSale').show();
            e.sales.focus();
            this.prevStep(3);
            return;
        } else if (e.bookBy.val() === '') {
            $('#alertBooking').show();
            e.bookBy.focus();
            this.prevStep(3);
            return;
        } else if (e.presentation.val() === '') {
            $('#alertLanguage').show();
            e.presentation.focus();
            this.prevStep(3);
            return;
        } else if (e.shop_name.val() === '') {
            $('#alertShopName').show();
            e.shop_name.focus();
            this.prevStep(8);
            return;
        } else if (e.customer_name.val() === '') {
            $('#alertShopName').hide();
            $('#alertCustomerName').show();
            e.customer_name.focus();
            this.prevStep(8);
            return;
        } else if (e.contact_email.val() === '') {
            $('#alertCustomerName').hide();
            $('#alertCustomerEmail').show();
            e.contact_email.focus();
            this.prevStep(8);
            return;
        } else if (!emailRegex.test(e.contact_email.val())) {
            $('#alertCustomerEmail').hide();
            $('#alertCustomerEmailValid').show();
            e.contact_email.focus();
            this.prevStep(8);
            return;
        } else if (e.contact_phone.val() === '') {
            $('#alertCustomerEmailValid').hide();
            $('#alertCustomerPhone').show();
            e.contact_phone.focus();
            this.prevStep(8);
            return;
        }

        // Hide all alerts and generate review
        $('#alertShopType, #alertCountry, #alertState, #alertSale, #alertBooking, #alertLanguage, #alertShopName, #alertCustomerName, #alertCustomerEmail, #alertCustomerEmailValid, #alertCustomerPhone').hide();
        
        this.markStepCompleted(8); // Mark Step 8 as completed
        this.generateReview();
        this.nextStep(9);
    },

    generateReview: function() {
        const e = this.elements;
        
        // Get formatted phone numbers with country code
        const phoneFormatted = $('#contact_phone').data('formatted') || e.contact_phone.val() || '-';
        const mobileFormatted = $('#contact_mobile').data('formatted') || e.contact_mobile.val() || '-';
        
        // Group data by category
        const businessInfo = [
            { label: 'Shop Type', value: $("#shop_type option:selected").text(), icon: 'bi-shop' },
            { label: 'Country', value: $("#country option:selected").text(), icon: 'bi-flag' },
            { label: 'State', value: e.state.find(':selected').text() || '-', icon: 'bi-geo-alt-fill' },
            { label: 'City', value: e.city.val() || '-', icon: 'bi-geo' }
        ];
        
        const appointmentInfo = [
            { label: 'Salesperson', value: $("#sales option:selected").text() || '-', icon: 'bi-person' },
            { label: 'Booked By', value: $("#bookBy option:selected").text() || '-', icon: 'bi-journal-bookmark-fill' },
            { label: 'Presentation', value: $("#presentation option:selected").text(), icon: 'bi-translate' },
            { label: 'Date', value: e.date.val(), icon: 'bi-calendar' },
            { label: 'Time', value: `(${e.country.val()}) ${e.time.val().substring(0, 5)} = ${this.getThaiTimeText(e.date.val(), e.time.val())}`, icon: 'bi-clock' }
        ];
        
        const customerInfo = [
            // Col 1
            { label: 'Shop Name', value: e.shop_name.val() || '-', icon: 'bi-building' },
            { label: 'Phone', value: phoneFormatted, icon: 'bi-telephone' },
            { label: 'Line ID', value: e.line_id.val() || '-', icon: 'bi-chat-dots' },
            // Col 2
            { label: 'Customer Name', value: e.customer_name.val() || '-', icon: 'bi-person-circle' },
            { label: 'Mobile', value: mobileFormatted, icon: 'bi-phone' },
            { label: 'WhatsApp', value: e.whatsapp.val() || '-', icon: 'bi-whatsapp' },
            // Col 3
            { label: 'Email', value: e.contact_email.val() || '-', icon: 'bi-envelope' },
            { label: 'Address', value: e.address.val() || '-', icon: 'bi-geo-alt-fill' },
            // Col 4
            { label: 'Comment', value: e.comment.val() || '-', icon: 'bi-chat-right-text' }
        ];
        
        const createSection = (title, items, layout = 'flex') => {
            const containerClass = layout === 'grid4' 
                ? 'grid grid-cols-4 gap-4' 
                : 'flex flex-wrap gap-4';
            const itemClass = layout === 'grid4'
                ? 'bg-white rounded-lg p-4 shadow-sm'
                : 'bg-white rounded-lg p-4 shadow-sm flex-1 min-w-[200px]';
            
            return `
                <div class="bg-gray-50 rounded-xl p-5 mb-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">${title}</h3>
                    <div class="${containerClass}">
                        ${items.map(item => `
                            <div class="${itemClass}">
                                <div class="flex items-center gap-2 text-gray-500 text-xs mb-1">
                                    <i class="bi ${item.icon}"></i>
                                    <span>${item.label}</span>
                                </div>
                                <div class="text-gray-800 font-medium text-sm">${item.value}</div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        };
        
        let html = '';
        html += createSection('Business Information', businessInfo);
        html += createSection('Appointment Details', appointmentInfo);
        html += createSection('Customer Information', customerInfo, 'grid4');

        $('#reviewSection').html(html);
    },

    getThaiTimeText: function(dateStr, timeStr) {
        try {
            const selectedZone = this.elements.timezone.val() || 'Asia/Bangkok';
            const local = luxon.DateTime.fromISO(`${dateStr}T${timeStr}`, { zone: selectedZone });
            const thai = local.setZone('Asia/Bangkok');
            return `(TH) : ${thai.toFormat('HH:mm')}`;
        } catch (e) {
            return '-';
        }
    },

    /**
     * Submit booking
     */
    submitBooking: function() {
        const self = this;
        
        // Get selected staff data
        const salesId = this.elements.sales.val();
        const salesOption = this.elements.sales.find(':selected');
        const salesEmail = this.getStaffEmail(salesId);
        const salesName = salesOption.text() || '';
        const salesNickname = salesOption.data('nickname') || salesName.split(' ')[0] || '';
        
        // Get booker data
        const bookById = this.elements.bookBy.val();
        const bookByOption = this.elements.bookBy.find(':selected');
        const bookByEmail = this.getStaffEmail(bookById);
        const bookByName = bookByOption.text() || '';
        const bookByNickname = bookByOption.data('nickname') || bookByName.split(' ')[0] || '';
        
        // Get date parts
        const dateVal = this.elements.date.val() || '';
        const datePart = dateVal.split(' ')[0]; // Extract YYYY-MM-DD
        const timeVal = this.elements.time.val() || '';
        
        // Calculate end time
        const endTimeVal = this.addMinutes(timeVal, 15);
        const endTimeThai = this.addMinutes(this.thaiTimePreview, 15);
        
        // Build ISO timestamps for calendar (dtStamp, dtStart, dtEnd)
        const isoDateTime = `${datePart}T${timeVal}:00`;
        const isoEndTime = `${datePart}T${endTimeVal}`;
        const now = new Date().toISOString();
        
        // Prepare data for make.com - matching blueprint field names exactly
        const appointmentDetail = {
            // Staff info
            staff_email: salesEmail,
            staff_name: salesName,
            staff_nickname: salesNickname,
            
            // Booked by info
            book_by: bookById,
            book_by_name: bookByName,
            book_by_nickname: bookByNickname,
            
            // Appointment details
            shop_name: this.elements.shop_name.val() || '',
            shop_type: this.elements.shop_type.val() || '',
            customer_name: this.elements.customer_name.val() || '',
            contact_phone: this.elements.contact_phone.val() || '',
            contact_mobile: this.elements.contact_mobile.val() || '',
            contact_email: this.elements.contact_email.val() || '',
            line_id: this.elements.line_id.val() || '',
            whatsapp: this.elements.whatsapp.val() || '',
            address: this.elements.address.val() || '',
            comment: this.elements.comment.val() || '',
            city: this.elements.city.val() || '',
            country: this.elements.country.val() || '',
            state: this.elements.state.find(':selected').text() || '',
            timezone: this.elements.timezone.val() || '',
            presentation: this.elements.presentation.val() || '',
            
            // Date and time fields for make.com
            startDate: datePart,
            startTime: timeVal,
            endDate: datePart,
            endTime: endTimeVal,
            
            // Thai timezone values
            daythaionly: this.thaiDayPreview || '',
            timethaionly: this.thaiTimePreview || '',
            end_timethaionly: endTimeThai,
            
            // Calendar event timestamps (ISO format)
            dtStamp: now,
            dtStart: isoDateTime,
            dtEnd: isoEndTime,
            
            // Form version
            formVersion: 'v3.0'
        };

        const sendEmailPayload = {
            subject: `Appointment booked for ${appointmentDetail.shop_name}`,
            body: `
                <h3>Appointment Details</h3>
                <p><strong>Shop:</strong> ${appointmentDetail.shop_name}</p>
                <p><strong>Date:</strong> ${appointmentDetail.startDate}</p>
                <p><strong>Time:</strong> ${appointmentDetail.startTime}</p>
                <p><strong>Salesperson:</strong> ${appointmentDetail.staff_name}</p>
                <p><strong>Contact:</strong> ${appointmentDetail.customer_name}</p>
                <p><strong>Phone:</strong> ${appointmentDetail.contact_phone}</p>
            `,
            recipients: [appointmentDetail.contact_email]
        };

        console.log('Sending to make.com webhooks...');
        console.log('Appointment data:', appointmentDetail);

        // Send to make.com webhooks
        $.ajax({
            url: 'https://hook.us1.make.com/yk8yef5sm9m5y4gr8qfj71ynmsorhz7d',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(sendEmailPayload),
            success: function() {
                console.log('Email alert webhook sent');
            },
            error: function(xhr, status, error) {
                console.error('Email alert webhook error:', error);
            }
        });

        $.ajax({
            url: 'https://hook.us1.make.com/hg8rqnmfuxry86lq4ylr967j9uifhlt1',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(appointmentDetail),
            success: function() {
                console.log('Appointment webhook sent');
                // Submit form after webhooks are sent
                document.getElementById('bookingForm').submit();
            },
            error: function(xhr, status, error) {
                console.error('Appointment webhook error:', error);
                // Still submit form even if webhook fails
                document.getElementById('bookingForm').submit();
            }
        });
    },

    /**
     * Staff helpers
     */
    getStaffEmail: function(id) {
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
    },

    getNickname: function(fullName) {
        if (!fullName) return '';
        return fullName.trim().split(' ')[0];
    },

    /**
     * Calendar helpers
     */
    toUTCFormat: function(dateStr, timeStr, offsetHours = 0) {
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
    },

    addMinutes: function(timeStr, minutesToAdd) {
        if (!timeStr || typeof timeStr !== 'string' || !timeStr.includes(':')) {
            return '';
        }
        const [hour, minute, second] = timeStr.split(':').map(Number);
        const date = new Date(0, 0, 0, hour, minute, second || 0);
        date.setMinutes(date.getMinutes() + minutesToAdd);

        const h = String(date.getHours()).padStart(2, '0');
        const m = String(date.getMinutes()).padStart(2, '0');
        const s = String(date.getSeconds()).padStart(2, '0');

        return `${h}:${m}:${s}`;
    },

    /**
     * Get full state name from state code
     */
    getStateFullName: function() {
        const e = this.elements;
        const selectedCountry = e.country.val();
        const selectedStateCode = e.state.val();
        
        if (!selectedCountry || !selectedStateCode) return '-';
        
        const states = this.countryToState[selectedCountry] || [];
        const selectedState = states.find(s => s.code === selectedStateCode);
        
        return selectedState ? selectedState.name : selectedStateCode;
    },

    /**
     * Send email notification
     */
    sendEmail: function() {
        const e = this.elements;
        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        
        this.sendEmailPayload = {
            "staff_id": e.sales.val(),
            "staff_email": this.getStaffEmail(e.sales.val()),
            "book_by": this.getStaffEmail(e.bookBy.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": this.getNickname(selectedSale.text()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": this.getNickname(selectedBookBy.text()),
            "created_by": "1",
            "shop_type_id": e.shop_type.val(),
            "shop_type": $("#shop_type option:selected").text(),
            "country": e.country.val(),
            "timezone": e.timezone.val(),
            "city": e.city.val(),
            "daythaionly": this.thaiDayPreview,
            "timethaionly": this.thaiTimePreview,
            "end_timethaionly": this.addMinutes(this.thaiTimePreview, 15),
            "state": this.getStateFullName(),
            "date": e.date.val(),
            "time": e.time.val(),
            "customer_name": e.customer_name.val(),
            "shop_name": e.shop_name.val(),
            "contact_email": e.contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || e.contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || e.contact_mobile.val(),
            "presentation": e.presentation.val(),
            "line_id": e.line_id.val(),
            "whatsapp": e.whatsapp.val(),
            "address": e.address.val(),
            "comment": e.comment.val(),
            "timetodaynow": e.timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("we call webhook UCanBookMe - email alert");
        console.table(this.sendEmailPayload);

        const sendEmail = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'json',
            url: "https://hook.us1.make.com/yk8yef5sm9m5y4gr8qfj71ynmsorhz7d",
            data: this.sendEmailPayload
        });

        sendEmail.done(function(res) {
            console.log("send Email done");
            console.log("return = ", res);
        });

        sendEmail.fail(function(xhr, status, error) {
            console.log("ajax webhook fail!!");
            console.log(status + ': ' + error);
        });
    },

    /**
     * Book calendar
     */
    bookCalendar: function() {
        const e = this.elements;
        const dtStamp = this.toUTCFormat(e.date.val(), e.time.val(), 0);
        const dtStart = this.toUTCFormat(e.date.val(), e.time.val(), 0);
        const dtEnd = this.toUTCFormat(e.date.val(), this.addMinutes(e.time.val(), 15), 0);
        const timezone = e.timezone.val();

        const dtStartNoZ = dtStart.replace(/Z$/, '');
        const dtEndNoZ = dtEnd.replace(/Z$/, '');

        const dtStartReal = timezone + ":" + dtStartNoZ;
        const dtEndReal = timezone + ":" + dtEndNoZ;

        const selectedSale = $("#sales option:selected");
        const selectedBookBy = $("#bookBy option:selected");
        
        this.appointmentDetail = {
            "staff_email": this.getStaffEmail(e.sales.val()),
            "staff_name": selectedSale.text(),
            "staff_nickname": this.getNickname(selectedSale.text()),
            "book_by": this.getStaffEmail(e.bookBy.val()),
            "book_by_name": selectedBookBy.text(),
            "book_by_nickname": this.getNickname(selectedBookBy.text()),
            "shop_type": $("#shop_type option:selected").text(),
            "country": e.country.val(),
            "timezone": e.timezone.val(),
            "daythaionly": this.thaiDayPreview,
            "timethaionly": this.thaiTimePreview,
            "end_timethaionly": this.addMinutes(this.thaiTimePreview, 15),
            "city": e.city.val(),
            "startDate": e.date.val(),
            "startTime": e.time.val(),
            "endDate": e.date.val(),
            "endTime": this.addMinutes(e.time.val(), 15),
            "dtStamp": dtStamp,
            "dtStart": dtStartReal,
            "dtEnd": dtEndReal,
            "customer_name": e.customer_name.val(),
            "shop_name": e.shop_name.val(),
            "contact_email": e.contact_email.val(),
            "contact_phone": $('#contact_phone').data('formatted') || e.contact_phone.val(),
            "contact_mobile": $('#contact_mobile').data('formatted') || e.contact_mobile.val(),
            "presentation": e.presentation.val(),
            "line_id": e.line_id.val(),
            "whatsapp": e.whatsapp.val(),
            "address": e.address.val(),
            "comment": e.comment.val(),
            "timetodaynow": e.timeToDayNow.val(),
            "formVersion": "1.2.0"
        };

        console.log("dtStamp = ", dtStamp);
        console.log("dtStart = ", dtStart);
        console.log("dtEnd = ", dtEnd);
        console.log("we call webhook UCanBookMe - Appointment created");
        console.table(this.appointmentDetail);

        const makeAppointment = $.ajax({
            type: "POST",
            crossDomain: true,
            dataType: 'json',
            url: "https://hook.us1.make.com/hg8rqnmfuxry86lq4ylr967j9uifhlt1",
            data: this.appointmentDetail
        });

        makeAppointment.done(function(res) {
            console.log("make Appointment done");
            console.log("return = ", res);
        });

        makeAppointment.fail(function(xhr, status, error) {
            console.log("make Appointment webhook fail!!");
            console.log(status + ': ' + error);
        });
    }
};

// Initialize when DOM is ready
$(document).ready(function() {
    BookingController.init();
});
