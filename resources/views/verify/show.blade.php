<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proposal Verification | GCL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* Custom GCL Green Color */
    :root {
      --gcl-green: #115D28;
      --gcl-green-dark: #0d4720;
      --gcl-green-light: #1a7335;
      --gcl-green-lightest: #e8f5e9;
    }
    
    .bg-gcl-green {
      background-color: var(--gcl-green);
    }
    
    .bg-gcl-green-dark {
      background-color: var(--gcl-green-dark);
    }
    
    .bg-gcl-green-light {
      background-color: var(--gcl-green-light);
    }
    
    .bg-gcl-green-lightest {
      background-color: var(--gcl-green-lightest);
    }
    
    .text-gcl-green {
      color: var(--gcl-green);
    }
    
    .text-gcl-green-dark {
      color: var(--gcl-green-dark);
    }
    
    .border-gcl-green {
      border-color: var(--gcl-green);
    }
    
    .hover\:bg-gcl-green-dark:hover {
      background-color: var(--gcl-green-dark);
    }
    
    .focus\:ring-gcl-green:focus {
      --tw-ring-color: var(--gcl-green);
    }
    
    .focus\:border-gcl-green:focus {
      border-color: var(--gcl-green);
    }
    
    /* Prevent text selection of sensitive data */
    .no-select {
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
    }
    
    /* Rate limit visual feedback */
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }
    
    .shake {
      animation: shake 0.3s ease-in-out;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 via-white to-green-50 flex items-center justify-center p-6">

  <div class="bg-white shadow-2xl rounded-2xl max-w-lg w-full p-8 border-2 border-green-100">
    <!-- Header -->
    <div class="text-center mb-6">
      <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gcl-green text-white text-2xl font-bold shadow-lg">
        GCL
      </div>
      <h1 class="mt-4 text-2xl font-extrabold text-gcl-green-dark">Proposal Verification</h1>
      <p class="text-sm text-gcl-green mt-1">Secure verification system</p>
    </div>

    <!-- Step 1: Enter Proposal Number -->
    <div id="step1" class="space-y-4">
      <label class="block text-sm font-medium text-gcl-green-dark">Proposal Number</label>
      <input id="proposalInput" 
             type="text" 
             placeholder="e.g., P202500001"
             autocomplete="off"
             maxlength="50"
             class="w-full border-2 border-green-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-gcl-green focus:border-gcl-green transition-all">
      <button id="lookupBtn" 
              onclick="lookupProposal()"
              class="w-full bg-gcl-green hover:bg-gcl-green-dark text-white py-3 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg">
        <span id="lookupBtnText">Continue</span>
        <div id="lookupLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
      </button>
      <p id="step1Error" class="hidden text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200"></p>
    </div>

    <!-- Step 2: Show stored phone & Send OTP -->
    <div id="step2" class="hidden space-y-4">
      <div class="bg-gcl-green-lightest border-2 border-green-200 rounded-lg p-4">
        <p class="text-sm text-gcl-green-dark mb-2 font-medium">OTP will be sent to:</p>
        <div id="maskedPhone" class="px-4 py-3 rounded-lg bg-white border-2 border-green-300 font-mono text-gcl-green-dark tracking-wide text-center text-lg font-bold">
          Loading...
        </div>
      </div>

      <button id="sendOtpBtn" 
              onclick="sendOtp()"
              class="w-full bg-gcl-green hover:bg-gcl-green-dark text-white py-3 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg">
        <span id="sendOtpBtnText">Send OTP</span>
        <div id="sendOtpLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
      </button>

      <div id="otpSentNote" class="hidden p-4 text-sm text-green-700 bg-green-50 rounded-lg border-2 border-green-200">
        <div class="flex items-start">
          <svg class="w-5 h-5 mr-2 flex-shrink-0 mt-0.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          <div>
            <p class="font-medium">OTP sent successfully!</p>
            <p class="text-xs mt-1">Please check your phone. Code expires in 5 minutes.</p>
          </div>
        </div>
      </div>

      <!-- Step 3: Enter OTP -->
      <div id="otpBox" class="hidden space-y-3">
        <label class="block text-sm font-medium text-gcl-green-dark">Enter 6-Digit OTP</label>
        <input id="otpInput" 
               type="text" 
               inputmode="numeric"
               pattern="[0-9]*"
               maxlength="6" 
               placeholder="• • • • • •"
               autocomplete="one-time-code"
               class="w-full border-2 border-green-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-gcl-green focus:border-gcl-green text-center tracking-[0.5em] text-2xl font-bold">
        
        <!-- Remaining attempts indicator -->
        <div id="attemptsRemaining" class="hidden text-xs text-amber-600 bg-amber-50 p-2 rounded border border-amber-200"></div>
        
        <button id="verifyBtn" 
                onclick="verifyOtp()"
                class="w-full bg-gcl-green hover:bg-gcl-green-dark text-white py-3 rounded-lg font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg">
          <span id="verifyBtnText">Verify OTP</span>
          <div id="verifyLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
        </button>
        
        <p id="otpError" class="hidden text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200"></p>
        
        <!-- Resend OTP with cooldown -->
        <div class="text-center">
          <button id="resendBtn" 
                  onclick="resendOtp()" 
                  disabled
                  class="text-sm text-gcl-green hover:text-gcl-green-dark underline disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline">
            <span id="resendText">Resend OTP</span>
            <span id="resendCooldown" class="hidden">(wait <span id="cooldownSeconds">30</span>s)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Step 4: Verified -->
    <div id="step3" class="hidden">
      <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
          <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gcl-green-dark">Verified Successfully!</h2>
        <p class="text-sm text-gcl-green mt-1">This proposal is authentic</p>
      </div>
      
      <div class="space-y-3 bg-gcl-green-lightest border-2 border-green-200 rounded-lg p-5">
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Proposal No.</span>
          <span id="vProposal" class="font-mono font-bold text-gcl-green-dark text-right"></span>
        </div>
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Title</span>
          <span id="vTitle" class="text-right text-gcl-green-dark font-medium max-w-[60%]"></span>
        </div>
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Amount</span>
          <span id="vAmount" class="font-bold text-gcl-green"></span>
        </div>
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Organization</span>
          <span id="vClient" class="text-right text-gcl-green-dark max-w-[60%]"></span>
        </div>
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Phone</span>
          <span id="vPhone" class="font-mono text-gcl-green-dark"></span>
        </div>
        <div class="flex justify-between items-start pb-3 border-b border-green-200">
          <span class="text-sm font-medium text-gcl-green-dark">Status</span>
          <span id="vStatus" class="px-3 py-1 bg-gcl-green text-white rounded-full text-xs font-bold uppercase tracking-wide"></span>
        </div>
        <div class="flex justify-between items-start pt-2">
          <span class="text-xs text-gcl-green">Verified at</span>
          <span id="vVerifiedAt" class="text-xs text-gcl-green-dark font-medium"></span>
        </div>
      </div>
      
      <button onclick="resetForm()" 
              class="mt-6 w-full bg-gray-600 hover:bg-gray-700 text-white py-3 rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
        Verify Another Proposal
      </button>
    </div>

    <!-- Security Notice -->
    <div class="mt-6 text-center text-xs text-gray-500 no-select">
      <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
      </svg>
      Secured by GCL Verification System
    </div>
  </div>

  <script>
    // SECURITY ENHANCEMENTS
    const MAX_OTP_ATTEMPTS = 5;
    const OTP_COOLDOWN_SECONDS = 30;
    const RATE_LIMIT_DELAY = 1000; // 1 second between requests
    
    let otpAttempts = 0;
    let lastRequestTime = 0;
    let cooldownInterval = null;
    let cooldownSecondsRemaining = 0;

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
      console.error('CSRF token not found!');
    }
    
    // DOM elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    const step1Error = document.getElementById('step1Error');
    const maskedPhoneDiv = document.getElementById('maskedPhone');
    const otpBox = document.getElementById('otpBox');
    const otpSentNote = document.getElementById('otpSentNote');
    const otpError = document.getElementById('otpError');
    const attemptsRemaining = document.getElementById('attemptsRemaining');

    const proposalInput = document.getElementById('proposalInput');
    const otpInput = document.getElementById('otpInput');

    const lookupBtn = document.getElementById('lookupBtn');
    const lookupBtnText = document.getElementById('lookupBtnText');
    const lookupLoader = document.getElementById('lookupLoader');

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const sendOtpBtnText = document.getElementById('sendOtpBtnText');
    const sendOtpLoader = document.getElementById('sendOtpLoader');

    const verifyBtn = document.getElementById('verifyBtn');
    const verifyBtnText = document.getElementById('verifyBtnText');
    const verifyLoader = document.getElementById('verifyLoader');

    const resendBtn = document.getElementById('resendBtn');
    const resendText = document.getElementById('resendText');
    const resendCooldown = document.getElementById('resendCooldown');
    const cooldownSeconds = document.getElementById('cooldownSeconds');

    const vProposal = document.getElementById('vProposal');
    const vTitle = document.getElementById('vTitle');
    const vAmount = document.getElementById('vAmount');
    const vClient = document.getElementById('vClient');
    const vPhone = document.getElementById('vPhone');
    const vStatus = document.getElementById('vStatus');
    const vVerifiedAt = document.getElementById('vVerifiedAt');

    let currentProposalNo = null;

    // Rate limiting check
    function checkRateLimit() {
      const now = Date.now();
      if (now - lastRequestTime < RATE_LIMIT_DELAY) {
        return false;
      }
      lastRequestTime = now;
      return true;
    }

    // Utility functions
    function showError(element, message) {
      element.textContent = message;
      element.classList.remove('hidden');
      element.classList.add('shake');
      setTimeout(() => element.classList.remove('shake'), 300);
    }

    function hideError(element) {
      element.classList.add('hidden');
    }

    function setLoading(btn, textEl, loader, loading) {
      btn.disabled = loading;
      if (loading) {
        textEl.classList.add('hidden');
        loader.classList.remove('hidden');
      } else {
        textEl.classList.remove('hidden');
        loader.classList.add('hidden');
      }
    }

    // Start resend cooldown
    function startResendCooldown() {
      cooldownSecondsRemaining = OTP_COOLDOWN_SECONDS;
      resendBtn.disabled = true;
      resendText.classList.add('hidden');
      resendCooldown.classList.remove('hidden');
      
      cooldownInterval = setInterval(() => {
        cooldownSecondsRemaining--;
        cooldownSeconds.textContent = cooldownSecondsRemaining;
        
        if (cooldownSecondsRemaining <= 0) {
          clearInterval(cooldownInterval);
          resendBtn.disabled = false;
          resendText.classList.remove('hidden');
          resendCooldown.classList.add('hidden');
        }
      }, 1000);
    }

    // Update attempts remaining display
    function updateAttemptsDisplay() {
      const remaining = MAX_OTP_ATTEMPTS - otpAttempts;
      if (remaining <= 3 && remaining > 0) {
        attemptsRemaining.textContent = `⚠️ ${remaining} attempt${remaining > 1 ? 's' : ''} remaining`;
        attemptsRemaining.classList.remove('hidden');
      } else if (remaining <= 0) {
        attemptsRemaining.textContent = '❌ Too many failed attempts. Please start over.';
        attemptsRemaining.classList.remove('hidden');
        verifyBtn.disabled = true;
        otpInput.disabled = true;
      }
    }

    // API call wrapper with better error handling
    async function apiCall(url, data = {}) {
      if (!checkRateLimit()) {
        throw new Error('Please wait a moment before trying again.');
      }

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify(data)
      });

      const result = await response.json();
      
      if (!response.ok) {
        throw new Error(result.message || 'Something went wrong. Please try again.');
      }
      
      return result;
    }

    // Step 1: Look up proposal
    async function lookupProposal() {
      const proposalNo = proposalInput.value.trim().toUpperCase();
      
      if (!proposalNo) {
        showError(step1Error, 'Please enter a proposal number');
        proposalInput.focus();
        return;
      }

      // Basic format validation
      if (proposalNo.length < 5) {
        showError(step1Error, 'Invalid proposal number format');
        return;
      }

      hideError(step1Error);
      setLoading(lookupBtn, lookupBtnText, lookupLoader, true);

      try {
        const result = await apiCall('{{ route("verify.lookup") }}', { 
          proposal_no: proposalNo 
        });

        if (result.success) {
          currentProposalNo = result.data.proposal_no;
          maskedPhoneDiv.textContent = result.data.masked_phone;
          
          // Reset OTP attempts when starting new verification
          otpAttempts = 0;
          
          // Move to step 2
          step1.classList.add('hidden');
          step2.classList.remove('hidden');
        }
      } catch (error) {
        showError(step1Error, error.message);
      } finally {
        setLoading(lookupBtn, lookupBtnText, lookupLoader, false);
      }
    }

    // Step 2: Send OTP
    async function sendOtp() {
      if (!currentProposalNo) {
        showError(otpError, 'Session expired. Please start over.');
        setTimeout(resetForm, 2000);
        return;
      }

      setLoading(sendOtpBtn, sendOtpBtnText, sendOtpLoader, true);

      try {
        const result = await apiCall('{{ route("verify.send-otp") }}', { 
          proposal_no: currentProposalNo 
        });

        if (result.success) {
          otpSentNote.classList.remove('hidden');
          otpBox.classList.remove('hidden');
          hideError(otpError);
          
          // Start cooldown for resend button
          startResendCooldown();
          
          // Focus OTP input
          setTimeout(() => otpInput.focus(), 100);
        }
      } catch (error) {
        showError(otpError, error.message);
      } finally {
        setLoading(sendOtpBtn, sendOtpBtnText, sendOtpLoader, false);
      }
    }

    // Resend OTP (same as sendOtp but called from resend button)
    async function resendOtp() {
      hideError(otpError);
      otpInput.value = '';
      await sendOtp();
    }

    // Step 3: Verify OTP
    async function verifyOtp() {
      const otp = otpInput.value.trim();
      
      if (!otp || otp.length !== 6 || !/^\d{6}$/.test(otp)) {
        showError(otpError, 'Please enter a valid 6-digit OTP');
        otpInput.focus();
        return;
      }

      if (otpAttempts >= MAX_OTP_ATTEMPTS) {
        showError(otpError, 'Maximum attempts exceeded. Please start over.');
        return;
      }

      hideError(otpError);
      setLoading(verifyBtn, verifyBtnText, verifyLoader, true);

      try {
        const result = await apiCall('{{ route("verify.verify-otp") }}', {
          proposal_no: currentProposalNo,
          otp: otp
        });

        if (result.success) {
          // Clear cooldown interval
          if (cooldownInterval) {
            clearInterval(cooldownInterval);
          }

          // Fill verified data
          const data = result.data;
          vProposal.textContent = data.proposal_no;
          vTitle.textContent = data.title;
          vAmount.textContent = data.amount;
          vClient.textContent = data.client_org;
          vPhone.textContent = data.client_phone;
          vStatus.textContent = data.status;
          vVerifiedAt.textContent = data.verified_at;

          // Move to step 3 (verified)
          step2.classList.add('hidden');
          step3.classList.remove('hidden');
        }
      } catch (error) {
        otpAttempts++;
        updateAttemptsDisplay();
        showError(otpError, error.message);
        otpInput.value = '';
        otpInput.focus();
      } finally {
        setLoading(verifyBtn, verifyBtnText, verifyLoader, false);
      }
    }

    // Reset form to start over
    function resetForm() {
      // Clear cooldown interval
      if (cooldownInterval) {
        clearInterval(cooldownInterval);
      }

      // Reset variables
      currentProposalNo = null;
      otpAttempts = 0;
      cooldownSecondsRemaining = 0;
      
      // Clear inputs
      proposalInput.value = '';
      otpInput.value = '';
      
      // Hide all steps except step 1
      step2.classList.add('hidden');
      step3.classList.add('hidden');
      step1.classList.remove('hidden');
      
      // Hide all messages
      hideError(step1Error);
      hideError(otpError);
      otpSentNote.classList.add('hidden');
      otpBox.classList.add('hidden');
      attemptsRemaining.classList.add('hidden');
      
      // Reset buttons
      resendBtn.disabled = false;
      resendText.classList.remove('hidden');
      resendCooldown.classList.add('hidden');
      verifyBtn.disabled = false;
      otpInput.disabled = false;
      
      // Focus the proposal input
      proposalInput.focus();
    }

    // Auto-submit OTP when 6 digits are entered
    otpInput.addEventListener('input', function(e) {
      // Only allow digits
      e.target.value = e.target.value.replace(/\D/g, '');
      
      // Auto-verify when 6 digits entered
      if (e.target.value.length === 6) {
        verifyOtp();
      }
    });

    // Enter key handlers
    proposalInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        lookupProposal();
      }
    });

    otpInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        verifyOtp();
      }
    });

    // Prevent paste of non-numeric characters in OTP
    otpInput.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedData = e.clipboardData.getData('text');
      const numericOnly = pastedData.replace(/\D/g, '').slice(0, 6);
      e.target.value = numericOnly;
      if (numericOnly.length === 6) {
        verifyOtp();
      }
    });

    // Focus proposal input on page load
    window.addEventListener('load', () => {
      proposalInput.focus();
    });

    // Prevent multiple tab submissions
    let isSubmitting = false;
    document.addEventListener('visibilitychange', function() {
      if (document.hidden && isSubmitting) {
        // Tab is hidden, cancel any ongoing operations
        isSubmitting = false;
      }
    });
  </script>
</body>
</html>