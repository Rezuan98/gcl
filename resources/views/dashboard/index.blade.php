@extends('dashboard.dashboard')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gcl-green-dark">Dashboard</h1>
            <p class="text-gcl-green mt-1">Overview of your tender verification system</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button id="btn-open-create"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gcl-green hover:bg-gcl-green-dark text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Proposal
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                    class="px-6 py-3 bg-gray-600 text-white font-semibold rounded-xl shadow-lg hover:bg-gray-700 hover:shadow-xl transition-all duration-200">
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 border-2 border-green-200 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 border-2 border-red-200 shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-red-800 font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Proposals -->
        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-shadow border-2 border-green-100">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gcl-green-lightest rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-gcl-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gcl-green">Total</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-{{ $stats['monthly_growth'] >= 0 ? 'green' : 'red' }}-600 font-semibold">
                    {{ $stats['monthly_growth'] >= 0 ? '+' : '' }}{{ $stats['monthly_growth'] }}
                </span>
                <span class="text-gray-500 ml-1">this month</span>
            </div>
        </div>

        <!-- Verified -->
        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-shadow border-2 border-green-100">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-green-600">Verified</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['verified'] }}</p>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">{{ $stats['verification_percentage'] }}% of total</span>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-shadow border-2 border-green-100">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-yellow-600">Pending</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">Awaiting verification</span>
            </div>
        </div>

        <!-- Drafts -->
        <div class="bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition-shadow border-2 border-green-100">
            <div class="flex items-center justify-between mb-4">
                <div class="h-12 w-12 bg-gray-100 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-600">Drafts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['drafts'] }}</p>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-gray-500">Not published</span>
            </div>
        </div>
    </div>

    <!-- QR Code Section -->
    <div class="bg-white rounded-2xl shadow-md border-2 border-green-100 overflow-hidden">
        <div class="bg-gradient-to-r from-gcl-green-lightest to-green-100 px-6 py-4 border-b-2 border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gcl-green-dark">Standard Verification QR Code</h2>
                    <p class="text-sm text-gcl-green mt-1">
                        Universal QR code for all proposals. Users scan and enter Proposal No. to verify.
                    </p>
                </div>
                <div class="h-12 w-12 bg-gcl-green rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- QR Code Preview -->
                <div class="flex flex-col items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 border-2 border-dashed border-gcl-green">
                    <div class="bg-white p-4 rounded-xl shadow-lg">
                        <img src="{{ route('qr.standard.inline', ['size' => 250]) }}" 
                             alt="GCL Standard Verify QR"
                             class="h-64 w-64 rounded-lg">
                    </div>
                    <p class="mt-4 text-sm text-gcl-green text-center font-medium">Scan to verify any proposal</p>
                </div>

                <!-- QR Details -->
                <div class="space-y-6">
                    <!-- Verification URL -->
                    <div>
                        <label class="block text-sm font-semibold text-gcl-green-dark mb-2">
                            Verification URL
                        </label>
                        <div class="flex rounded-xl border-2 border-green-200 overflow-hidden shadow-sm">
                            <input id="verify-url" 
                                   type="text" 
                                   readonly 
                                   value="{{ route('verify.show') }}"
                                   class="flex-1 px-4 py-3 border-0 bg-gray-50 text-sm font-mono focus:outline-none">
                            <button id="btn-copy-verify-url"
                                    class="px-6 py-3 bg-gcl-green hover:bg-gcl-green-dark text-white font-semibold transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                        <p id="copy-toast" class="hidden mt-2 text-sm text-green-600 font-medium animate-pulse">
                            ✓ Copied to clipboard
                        </p>
                    </div>

                    <!-- Download QR Code -->
                    <div>
                        <label class="block text-sm font-semibold text-gcl-green-dark mb-3">
                            Download QR Code
                        </label>
                        <a href="{{ route('qr.standard.download', ['size' => 800]) }}"
                           class="inline-flex items-center gap-3 px-6 py-3 rounded-xl bg-gcl-green hover:bg-gcl-green-dark text-white font-semibold shadow-lg hover:shadow-xl transition-all w-full justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Download QR Code (High Quality)
                        </a>
                        <p class="mt-2 text-xs text-gray-500 text-center">
                            800x800px SVG format - Perfect for printing
                        </p>
                    </div>

                    <!-- Usage Instructions -->
                    <div class="bg-gcl-green-lightest border-2 border-green-200 rounded-xl p-4">
                        <h3 class="font-semibold text-gcl-green-dark text-sm mb-2">How to use:</h3>
                        <ol class="text-xs text-gcl-green space-y-1 list-decimal list-inside">
                            <li>Download and print the QR code</li>
                            <li>Display it at your office or on documents</li>
                            <li>Users scan and enter their Proposal Number</li>
                            <li>OTP verification ensures authenticity</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('proposals.partials.create-modal')
@endsection

@push('scripts')
<script>
    // Copy URL functionality with better UX
    const copyBtn = document.getElementById('btn-copy-verify-url');
    const input = document.getElementById('verify-url');
    const toast = document.getElementById('copy-toast');

    copyBtn?.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(input.value);
            showToast();
        } catch (e) {
            // Fallback for older browsers
            input.select();
            document.execCommand('copy');
            showToast();
        }
    });

    function showToast() {
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 2500);
    }
</script>
@endpush