<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proposal Verification | GCL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen bg-emerald-50 flex items-center justify-center p-6">

  <div class="bg-white shadow-xl rounded-2xl max-w-lg w-full p-8">
    <!-- Header -->
    <div class="text-center mb-6">
      <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-600 text-2xl font-bold">
        GCL
      </div>
      <h1 class="mt-4 text-2xl font-extrabold text-emerald-800">Proposal Verification</h1>
      <p class="text-sm text-emerald-600">Enter your proposal number to continue.</p>
    </div>

    <!-- Step 1: Enter Proposal Number -->
    <div id="step1" class="space-y-4">
      <label class="block text-sm font-medium text-emerald-800">Proposal No.</label>
      <input id="proposalInput" type="text" placeholder="e.g., P202500001"
             class="w-full border border-emerald-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
      <button id="lookupBtn" onclick="lookupProposal()"
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <span id="lookupBtnText">Continue</span>
        <div id="lookupLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
      </button>
      <p id="step1Error" class="hidden text-sm text-rose-600"></p>
    </div>

    <!-- Step 2: Show stored phone & Send OTP -->
    <div id="step2" class="hidden">
      <div class="text-sm text-emerald-700 mb-2">OTP will be sent to the registered phone number for this proposal:</div>
      <div id="maskedPhone"
           class="px-4 py-3 rounded-lg border border-emerald-300 bg-emerald-50 font-semibold text-emerald-800 tracking-wide">
        Loading...
      </div>

      <button id="sendOtpBtn" onclick="sendOtp()"
              class="mt-5 w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
        <span id="sendOtpBtnText">Send OTP</span>
        <div id="sendOtpLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
      </button>

      <div id="otpSentNote" class="hidden mt-3 p-3 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200">
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          OTP sent successfully. Please check your phone.
        </div>
        <!-- Debug OTP display (only in development) -->
        <div id="debugOtp" class="hidden mt-2 p-2 bg-yellow-100 border border-yellow-300 rounded text-yellow-800 text-xs">
          <strong>Debug OTP:</strong> <span id="debugOtpCode"></span>
        </div>
      </div>

      <!-- Step 3: Enter OTP -->
      <div id="otpBox" class="hidden mt-4 space-y-3">
        <label class="block text-sm font-medium text-emerald-800">Enter OTP</label>
        <input id="otpInput" type="text" maxlength="6" placeholder="6-digit code"
               class="w-full border border-emerald-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-center tracking-widest">
        <button id="verifyBtn" onclick="verifyOtp()"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          <span id="verifyBtnText">Verify</span>
          <div id="verifyLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
        </button>
        <p id="otpError" class="hidden text-sm text-rose-600"></p>
        
        <!-- Resend OTP -->
        <div class="text-center">
          <button id="resendBtn" onclick="sendOtp()" 
                  class="text-sm text-emerald-600 hover:text-emerald-700 underline disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline">
            Resend OTP
          </button>
        </div>
      </div>
    </div>

    <!-- Step 4: Verified -->
    <div id="step3" class="hidden text-center">
      <div class="text-emerald-600 text-6xl mb-4">✅</div>
      <h2 class="text-xl font-bold text-emerald-800">Proposal Verified</h2>
      <div class="mt-4 text-left text-sm bg-emerald-50 border border-emerald-200 rounded-lg p-4 space-y-2">
        <div class="flex justify-between">
          <strong>Proposal No.:</strong>
          <span id="vProposal" class="font-mono"></span>
        </div>
        <div class="flex justify-between">
          <strong>Title:</strong>
          <span id="vTitle" class="text-right"></span>
        </div>
        <div class="flex justify-between">
          <strong>Amount:</strong>
          <span id="vAmount" class="font-semibold text-emerald-700"></span>
        </div>
        <div class="flex justify-between">
          <strong>Organization:</strong>
          <span id="vClient"></span>
        </div>
        <div class="flex justify-between">
          <strong>Phone:</strong>
          <span id="vPhone" class="font-mono"></span>
        </div>
        <div class="flex justify-between">
          <strong>Status:</strong>
          <span id="vStatus" class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded text-xs font-semibold"></span>
        </div>
        <div class="flex justify-between text-xs text-gray-500">
          <strong>Verified at:</strong>
          <span id="vVerifiedAt"></span>
        </div>
      </div>
      
      <button onclick="resetForm()" 
              class="mt-6 w-full bg-gray-600 hover:bg-gray-700 text-white py-2 rounded-lg font-medium transition-colors">
        Verify Another Proposal
      </button>
    </div>
  </div>

  <script>
    // Get CSRF token for API requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // DOM elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    const step1Error = document.getElementById('step1Error');
    const maskedPhoneDiv = document.getElementById('maskedPhone');
    const otpBox = document.getElementById('otpBox');
    const otpSentNote = document.getElementById('otpSentNote');
    const otpError = document.getElementById('otpError');
    const debugOtp = document.getElementById('debugOtp');
    const debugOtpCode = document.getElementById('debugOtpCode');

    const proposalInput = document.getElementById('proposalInput');
    const otpInput = document.getElementById('otpInput');

    // Buttons and loaders
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

    // Verified info spans
    const vProposal = document.getElementById('vProposal');
    const vTitle = document.getElementById('vTitle');
    const vAmount = document.getElementById('vAmount');
    const vClient = document.getElementById('vClient');
    const vPhone = document.getElementById('vPhone');
    const vStatus = document.getElementById('vStatus');
    const vVerifiedAt = document.getElementById('vVerifiedAt');

    let currentProposalNo = null;

    // Utility functions
    function showError(element, message) {
      element.textContent = message;
      element.classList.remove('hidden');
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

    // API call wrapper
    async function apiCall(url, data = {}) {
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
        throw new Error(result.message || 'Something went wrong');
      }
      
      return result;
    }

    // Step 1: Look up proposal
    async function lookupProposal() {
      const proposalNo = proposalInput.value.trim();
      
      if (!proposalNo) {
        showError(step1Error, 'Please enter a proposal number');
        return;
      }

      hideError(step1Error);
      setLoading(lookupBtn, lookupBtnText, lookupLoader, true);

      try {
        const result = await apiCall('/api/verify/lookup', { 
          proposal_no: proposalNo 
        });

        if (result.success) {
          currentProposalNo = result.data.proposal_no;
          maskedPhoneDiv.textContent = result.data.masked_phone;
          
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
        showError(otpError, 'Please start over');
        return;
      }

      setLoading(sendOtpBtn, sendOtpBtnText, sendOtpLoader, true);
      resendBtn.disabled = true;

      try {
        const result = await apiCall('/api/verify/send-otp', { 
          proposal_no: currentProposalNo 
        });

        if (result.success) {
          otpSentNote.classList.remove('hidden');
          otpBox.classList.remove('hidden');
          hideError(otpError);
          
          // Show debug OTP if provided (development only)
          if (result.debug_otp) {
            debugOtpCode.textContent = result.debug_otp;
            debugOtp.classList.remove('hidden');
          }
          
          // Focus OTP input
          setTimeout(() => otpInput.focus(), 100);
          
          // Enable resend after 30 seconds
          setTimeout(() => {
            resendBtn.disabled = false;
          }, 30000);
        }
      } catch (error) {
        showError(otpError, error.message);
      } finally {
        setLoading(sendOtpBtn, sendOtpBtnText, sendOtpLoader, false);
      }
    }

    // Step 3: Verify OTP
    async function verifyOtp() {
      const otp = otpInput.value.trim();
      
      if (!otp || otp.length !== 6) {
        showError(otpError, 'Please enter a valid 6-digit OTP');
        return;
      }

      hideError(otpError);
      setLoading(verifyBtn, verifyBtnText, verifyLoader, true);

      try {
        const result = await apiCall('/api/verify/verify-otp', { 
          proposal_no: currentProposalNo,
          otp: otp
        });

        if (result.success) {
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
        showError(otpError, error.message);
        otpInput.select(); // Select the input for easy re-entry
      } finally {
        setLoading(verifyBtn, verifyBtnText, verifyLoader, false);
      }
    }

    // Reset form to start over
    function resetForm() {
      // Reset variables
      currentProposalNo = null;
      
      // Clear inputs
      proposalInput.value = '';
      otpInput.value = '';
      
      // Hide all steps except step 1
      step2.classList.add('hidden');
      step3.classList.add('hidden');
      step1.classList.remove('hidden');
      
      // Hide all error messages
      hideError(step1Error);
      hideError(otpError);
      otpSentNote.classList.add('hidden');
      otpBox.classList.add('hidden');
      debugOtp.classList.add('hidden');
      
      // Reset buttons
      resendBtn.disabled = false;
      
      // Focus the proposal input
      proposalInput.focus();
    }

    // Auto-submit OTP when 6 digits are entered
    otpInput.addEventListener('input', function(e) {
      e.target.value = e.target.value.replace(/\D/g, ''); // Only digits
      if (e.target.value.length === 6) {
        verifyOtp();
      }
    });

    // Enter key handlers
    proposalInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        lookupProposal();
      }
    });

    otpInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        verifyOtp();
      }
    });

    // Focus proposal input on page load
    proposalInput.focus();
  </script>
</body>
</html>