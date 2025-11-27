<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proposal Verification | GCL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* Custom scrollbar for PDF viewer */
    #pdfViewer::-webkit-scrollbar {
      width: 8px;
    }
    #pdfViewer::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    #pdfViewer::-webkit-scrollbar-thumb {
      background: #10b981;
      border-radius: 4px;
    }
    #pdfViewer::-webkit-scrollbar-thumb:hover {
      background: #059669;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 to-teal-50 flex items-center justify-center p-4 md:p-6">

  <div class="bg-white shadow-2xl rounded-2xl w-full p-6 md:p-8" id="mainContainer" style="max-width: 600px;">
    <!-- Header -->
    <div class="text-center mb-2">
      <div class="mx-auto h-32 w-32 flex items-center justify-center">
        <img src="{{ asset('logo.png') }}" alt="GCL Logo" class="h-full w-full object-contain">
      </div>
      <h1 class="mt-0 text-xl font-extrabold text-emerald-800">Proposal Verification</h1>
      <p class="text-sm text-emerald-600 mt-1">Secure access to your proposal document</p>
    </div>

    <!-- Proposal Info -->
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
      <div class="space-y-2">
        <div>
          <span class="text-xs font-medium text-emerald-600">Proposal Title:</span>
          <p class="text-sm font-semibold text-emerald-900">{{ $proposal->title }}</p>
        </div>
        <div>
          <span class="text-xs font-medium text-emerald-600">Company:</span>
          <p class="text-sm font-semibold text-emerald-900">{{ $proposal->company_name }}</p>
        </div>
      </div>
    </div>

    <!-- Step 1: Sending OTP (Automatic) -->
    <div id="step1">
      <div class="mb-4">
        <p class="text-sm text-emerald-700">Sending OTP to the registered phone number:</p>
        <div class="mt-2 px-4 py-3 rounded-lg border border-emerald-300 bg-emerald-50 font-mono text-emerald-800 tracking-wide text-center text-lg">
          {{ $proposal->masked_phone }}
        </div>
      </div>

      <!-- Loading Indicator -->
      <div id="sendingLoader" class="flex flex-col items-center justify-center py-6">
        <div class="inline-block w-12 h-12 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
        <p class="mt-4 text-sm text-emerald-700 font-medium">Sending OTP...</p>
      </div>

      <div id="otpSentNote" class="hidden mt-3 p-3 text-sm text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200">
        <div class="flex items-center">
          <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          OTP sent successfully. Please check your phone.
        </div>
        <!-- Debug OTP display (only in development) -->
        @if(config('app.debug'))
        <div id="debugOtp" class="hidden mt-2 p-2 bg-yellow-100 border border-yellow-300 rounded text-yellow-800 text-xs">
          <strong>Debug OTP:</strong> <span id="debugOtpCode"></span>
        </div>
        @endif
      </div>

      <!-- Error Display for Step 1 -->
      <div id="sendError" class="hidden mt-3 p-3 text-sm text-rose-700 bg-rose-50 rounded-lg border border-rose-200">
        <div class="flex items-start">
          <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
          </svg>
          <div class="flex-1">
            <p id="sendErrorMessage" class="font-medium">Failed to send OTP</p>
            <button onclick="sendOtp()" class="mt-2 text-xs text-emerald-600 hover:text-emerald-700 underline font-medium">
              Try Again
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Step 2: Verify OTP -->
    <div id="step2" class="hidden">
      <!-- Instruction Message -->
      <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-blue-800">
          An OTP has been sent to <strong class="font-mono" id="maskedPhoneInStep2">{{ $proposal->masked_phone }}</strong>. 
          Please enter the OTP to verify and access the proposal.
        </p>
        <p class="text-xs text-blue-600 mt-1">
          ⏱️ OTP is valid for <strong>5 minutes</strong>
        </p>
      </div>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-emerald-800 mb-2">Enter OTP</label>
          <input id="otpInput" 
                 type="text" 
                 inputmode="numeric"
                 autocomplete="one-time-code"
                 maxlength="6" 
                 placeholder="6-digit code"
                 class="w-full border border-emerald-300 rounded-lg px-3 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-center tracking-widest text-xl font-bold">
          <p class="mt-1 text-xs text-gray-500 flex items-center gap-1">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <span>OTP may auto-fill from SMS on supported devices</span>
          </p>
        </div>
        
        <button id="verifyBtn" onclick="verifyOtp()"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
          <span id="verifyBtnText">Verify & Access Proposal</span>
          <div id="verifyLoader" class="hidden inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
        </button>
        
        <p id="otpError" class="hidden text-sm text-rose-600 text-center"></p>
        
        <!-- Resend OTP -->
        <div class="text-center">
          <button id="resendBtn" onclick="sendOtp()" 
                  class="text-sm text-emerald-600 hover:text-emerald-700 underline disabled:opacity-50 disabled:cursor-not-allowed disabled:no-underline">
            Resend OTP
          </button>
          <p id="resendTimer" class="hidden text-xs text-gray-500 mt-1"></p>
        </div>
      </div>
    </div>

    <!-- Step 3: Verified - Show PDF Viewer & Download -->
    <div id="step3" class="hidden">
      <div class="text-center mb-4">
        <div class="mx-auto w-20 h-20 mb-3 flex items-center justify-center rounded-full bg-emerald-100">
          <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h2 class="text-xl font-bold text-emerald-800">Verification Successful!</h2>
        <p class="text-xs text-gray-500 mt-1">
          Verified at: <span id="verifiedTime"></span>
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2 mb-4">
        <button id="viewPdfBtn" onclick="togglePdfViewer()" 
                class="flex-1 flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-lg font-semibold transition-colors text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          <span id="viewBtnText">View PDF</span>
        </button>
        
        <a id="downloadBtn" href="#" download
           class="flex-1 flex items-center justify-center gap-2 bg-gray-600 hover:bg-gray-700 text-white py-2.5 rounded-lg font-semibold transition-colors text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Download
        </a>
      </div>

      <!-- PDF Viewer (Initially Hidden) -->
      <div id="pdfViewerContainer" class="hidden">
        <div class="bg-gray-100 rounded-lg overflow-hidden border border-emerald-200">
          <!-- PDF Viewer Header -->
          <div class="bg-emerald-600 text-white px-4 py-2 flex items-center justify-between">
            <span class="text-sm font-medium">📄 Proposal Document</span>
            <button onclick="togglePdfViewer()" class="hover:bg-emerald-700 rounded p-1 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <!-- PDF Embed -->
          <div class="relative" style="height: 500px;">
            <iframe id="pdfViewer" 
                    src="" 
                    class="w-full h-full border-0"
                    title="Proposal PDF">
              <p class="p-4 text-center text-sm text-gray-600">
                Your browser does not support PDF viewing. 
                <a href="#" id="fallbackDownload" class="text-emerald-600 hover:underline">Download the PDF</a> instead.
              </p>
            </iframe>
          </div>

          <!-- PDF Viewer Footer -->
          <div class="bg-gray-50 px-4 py-2 flex items-center justify-between border-t border-gray-200">
            <span class="text-xs text-gray-600">Scroll to view entire document</span>
            <a id="openNewTabBtn" href="#" target="_blank" 
               class="text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
              </svg>
              Open in New Tab
            </a>
          </div>
        </div>
      </div>

      <!-- Info Message -->
      <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
        <p class="text-xs text-emerald-700 text-center">
          🔒 This proposal has been verified <strong><span id="verificationCount"></span></strong> time(s)
        </p>
      </div>
    </div>
  </div>

  <script>
    // Get CSRF token for API requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const proposalToken = '{{ $proposal->unique_token }}';
    
    // DOM elements
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');
    const mainContainer = document.getElementById('mainContainer');

    const sendingLoader = document.getElementById('sendingLoader');
    const otpSentNote = document.getElementById('otpSentNote');
    const sendError = document.getElementById('sendError');
    const sendErrorMessage = document.getElementById('sendErrorMessage');
    const otpError = document.getElementById('otpError');
    const otpInput = document.getElementById('otpInput');

    // Buttons and loaders
    const verifyBtn = document.getElementById('verifyBtn');
    const verifyBtnText = document.getElementById('verifyBtnText');
    const verifyLoader = document.getElementById('verifyLoader');

    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    // PDF viewer elements
    const pdfViewerContainer = document.getElementById('pdfViewerContainer');
    const pdfViewer = document.getElementById('pdfViewer');
    const viewBtnText = document.getElementById('viewBtnText');

    // Resend timer variables
    let resendTimeout = null;
    let resendCountdown = null;

    // Utility functions
    function showError(message) {
      otpError.textContent = message;
      otpError.classList.remove('hidden');
    }

    function hideError() {
      otpError.classList.add('hidden');
    }

    function showSendError(message) {
      sendErrorMessage.textContent = message;
      sendError.classList.remove('hidden');
      sendingLoader.classList.add('hidden');
    }

    function hideSendError() {
      sendError.classList.add('hidden');
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

    function startResendTimer(seconds = 30) {
      resendBtn.disabled = true;
      let remaining = seconds;
      
      // Clear any existing timers
      if (resendTimeout) clearTimeout(resendTimeout);
      if (resendCountdown) clearInterval(resendCountdown);
      
      // Update timer display
      resendTimer.textContent = `Resend available in ${remaining}s`;
      resendTimer.classList.remove('hidden');
      
      // Countdown
      resendCountdown = setInterval(() => {
        remaining--;
        resendTimer.textContent = `Resend available in ${remaining}s`;
        
        if (remaining <= 0) {
          clearInterval(resendCountdown);
          resendTimer.classList.add('hidden');
          resendBtn.disabled = false;
        }
      }, 1000);
    }

    // Toggle PDF viewer
    function togglePdfViewer() {
      const isHidden = pdfViewerContainer.classList.contains('hidden');
      
      if (isHidden) {
        pdfViewerContainer.classList.remove('hidden');
        viewBtnText.textContent = 'Hide PDF';
        mainContainer.style.maxWidth = '900px';
        
        // Smooth scroll to viewer
        setTimeout(() => {
          pdfViewerContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
      } else {
        pdfViewerContainer.classList.add('hidden');
        viewBtnText.textContent = 'View PDF';
        mainContainer.style.maxWidth = '600px';
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

    // Send OTP
    async function sendOtp() {
      // Show loading state
      sendingLoader.classList.remove('hidden');
      hideSendError();
      hideError();
      resendBtn.disabled = true;

      try {
        const result = await apiCall('{{ route("verify.send-otp") }}', { 
          token: proposalToken 
        });

        if (result.success) {
          // Hide loading
          sendingLoader.classList.add('hidden');
          
          // Show success message
          otpSentNote.classList.remove('hidden');
          
          // Move to step 2
          step1.classList.add('hidden');
          step2.classList.remove('hidden');
          
          // Show debug OTP if provided (development only)
          @if(config('app.debug'))
          if (result.debug_otp) {
            document.getElementById('debugOtpCode').textContent = result.debug_otp;
            document.getElementById('debugOtp').classList.remove('hidden');
          }
          @endif
          
          // Focus OTP input
          setTimeout(() => otpInput.focus(), 100);
          
          // ⭐ Start WebOTP listener for auto-fill ⭐
          setupWebOTP();
          
          // Start resend timer (30 seconds)
          startResendTimer(30);
        }
      } catch (error) {
        showSendError(error.message || 'Failed to send OTP. Please try again.');
      }
    }

    // Verify OTP
    async function verifyOtp() {
      const otp = otpInput.value.trim();
      
      if (!otp || otp.length !== 6) {
        showError('Please enter a valid 6-digit OTP');
        return;
      }

      hideError();
      setLoading(verifyBtn, verifyBtnText, verifyLoader, true);

      try {
        const result = await apiCall('{{ route("verify.verify-otp") }}', {
          token: proposalToken,
          otp: otp
        });

        if (result.success) {
          // Move to step 3 first (so elements become available)
          step2.classList.add('hidden');
          step3.classList.remove('hidden');
          
          // Fill verified data
          const data = result.data;
          
          // Use setTimeout to ensure DOM is updated
          setTimeout(() => {
            const verifiedTimeEl = document.getElementById('verifiedTime');
            const verificationCountEl = document.getElementById('verificationCount');
            
            if (verifiedTimeEl) verifiedTimeEl.textContent = data.verified_at;
            if (verificationCountEl) verificationCountEl.textContent = data.verification_count;
            
            // Set PDF URLs
            if (data.has_pdf && data.pdf_url) {
              const pdfUrl = data.pdf_url;
              
              // Set download button
              const downloadBtn = document.getElementById('downloadBtn');
              if (downloadBtn) downloadBtn.href = pdfUrl;
              
              // Set PDF viewer
              if (pdfViewer) pdfViewer.src = pdfUrl;
              
              // Set open in new tab button
              const openNewTabBtn = document.getElementById('openNewTabBtn');
              if (openNewTabBtn) openNewTabBtn.href = pdfUrl;
              
              // Set fallback download link
              const fallbackDownload = document.getElementById('fallbackDownload');
              if (fallbackDownload) fallbackDownload.href = pdfUrl;
            }
          }, 100);
        }
      } catch (error) {
        showError(error.message);
        otpInput.select(); // Select the input for easy re-entry
      } finally {
        setLoading(verifyBtn, verifyBtnText, verifyLoader, false);
      }
    }

    // Auto-submit OTP when 6 digits are entered
    otpInput.addEventListener('input', function(e) {
      e.target.value = e.target.value.replace(/\D/g, ''); // Only digits
      if (e.target.value.length === 6) {
        verifyOtp();
      }
    });

    // Enter key handler
    otpInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        verifyOtp();
      }
    });

    // ⭐ WebOTP API - Auto-fill OTP from SMS ⭐
    async function setupWebOTP() {
      if ('OTPCredential' in window) {
        try {
          const ac = new AbortController();
          
          // Set timeout for OTP retrieval (5 minutes to match OTP expiry)
          setTimeout(() => {
            ac.abort();
          }, 5 * 60 * 1000);

          // Request OTP from SMS
          const otp = await navigator.credentials.get({
            otp: { transport: ['sms'] },
            signal: ac.signal
          });

          if (otp && otp.code) {
            console.log('✅ OTP auto-filled from SMS:', otp.code);
            otpInput.value = otp.code;
            
            // Small delay for better UX, then auto-verify
            setTimeout(() => {
              verifyOtp();
            }, 500);
          }
        } catch (err) {
          // User cancelled or error occurred
          console.log('WebOTP not available or cancelled:', err);
        }
      } else {
        console.log('WebOTP API not supported on this browser');
      }
    }

    // ⭐ AUTO-SEND OTP ON PAGE LOAD ⭐
    window.addEventListener('DOMContentLoaded', function() {
      console.log('Page loaded, automatically sending OTP...');
      sendOtp();
    });
  </script>
</body>
</html>