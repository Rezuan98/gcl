@extends('dashboard.dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('proposals.index') }}" 
           class="inline-flex items-center gap-2 text-emerald-700 hover:text-emerald-800 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Proposals
        </a>
    </div>

    <!-- Page Header -->
    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-emerald-800">{{ $proposal->title }}</h1>
            <p class="text-emerald-600 mt-1">{{ $proposal->company_name }}</p>
        </div>
        
        <div class="flex gap-3">
            <!-- Status Badge -->
            <span class="px-4 py-2 rounded-lg text-sm font-semibold
                @if($proposal->status === 'verified') bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200
                @elseif($proposal->status === 'pending') bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200
                @else bg-gray-100 text-gray-700 ring-1 ring-gray-200
                @endif">
                {{ ucfirst($proposal->status) }}
                @if($proposal->status === 'verified' && $proposal->verification_count > 0)
                    ({{ $proposal->verification_count }}x)
                @endif
            </span>
            
            <!-- Actions Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium flex items-center gap-2">
                    Actions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                        @if($proposal->status === 'draft')
                        <form method="POST" action="{{ route('proposals.publish', $proposal) }}" class="block">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Publish Proposal
                            </button>
                        </form>
                        @endif
                        
                        @if($proposal->hasPdf())
                        <a href="{{ $proposal->pdf_url }}" 
                           target="_blank"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Open PDF in New Tab
                        </a>
                        @endif
                        
                        <button onclick="copyVerificationUrl()" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Copy Verification URL
                        </button>
                        
                        <hr class="my-1">
                        
                        <form method="POST" 
                              action="{{ route('proposals.destroy', $proposal) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this proposal?')"
                              class="block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Delete Proposal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200">
            <p class="text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
            <p class="text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Proposal Details -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
                <h2 class="text-lg font-semibold text-emerald-800 mb-4">Proposal Information</h2>
                
                <div class="space-y-4">
                    <!-- Company Name -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Company</label>
                        <p class="text-sm text-gray-900 mt-1 font-medium">{{ $proposal->company_name }}</p>
                    </div>
                    
                    <!-- Contact Phone -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Contact Phone</label>
                        <p class="text-sm text-gray-900 mt-1 font-mono">{{ $proposal->client_phone }}</p>
                        <p class="text-xs text-gray-500 mt-1">Masked: {{ $proposal->masked_phone }}</p>
                    </div>
                    
                    <!-- Status -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Status</label>
                        <p class="text-sm text-gray-900 mt-1">{{ ucfirst($proposal->status) }}</p>
                    </div>
                    
                    <!-- Created Date -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Created</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $proposal->created_at->format('M j, Y \a\t g:i A') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $proposal->created_at->diffForHumans() }}</p>
                    </div>
                    
                    <!-- Last Updated -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Last Updated</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $proposal->updated_at->format('M j, Y \a\t g:i A') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $proposal->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Verification Details Card -->
            @if($proposal->status !== 'draft')
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
                <h2 class="text-lg font-semibold text-emerald-800 mb-4">Verification Details</h2>
                
                <div class="space-y-4">
                    <!-- Verification URL -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Verification URL</label>
                        <div class="mt-2 flex items-center gap-2">
                            <input type="text" 
                                   id="verification-url" 
                                   readonly 
                                   value="{{ $proposal->verification_url }}" 
                                   class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded px-3 py-2 font-mono">
                            <button onclick="copyVerificationUrl()"
                                    class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Token -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Unique Token</label>
                        <p class="text-xs text-gray-900 mt-1 font-mono break-all">{{ $proposal->unique_token }}</p>
                    </div>
                    
                    <!-- Verification Count -->
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Verification Count</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $proposal->verification_count }} time(s)</p>
                    </div>
                    
                    <!-- Verified At -->
                    @if($proposal->verified_at)
                    <div>
                        <label class="text-xs font-medium text-emerald-600 uppercase tracking-wide">First Verified</label>
                        <p class="text-sm text-gray-900 mt-1">{{ $proposal->verified_at->format('M j, Y \a\t g:i A') }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $proposal->verified_at->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Notes Card -->
            @if($proposal->notes)
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
                <h2 class="text-lg font-semibold text-emerald-800 mb-4">Additional Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $proposal->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Right Column - PDF Viewer -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
                <div class="p-4 border-b border-emerald-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-emerald-800">Proposal Document</h2>
                    
                    @if($proposal->hasPdf())
                    <div class="flex gap-2">
                        <a href="{{ $proposal->pdf_url }}" 
                           target="_blank"
                           class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm rounded-lg font-medium flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Open in New Tab
                        </a>
                        <a href="{{ $proposal->pdf_url }}" 
                           download="{{ $proposal->title }}.pdf"
                           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg font-medium flex items-center gap-2 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download
                        </a>
                    </div>
                    @endif
                </div>
                
                <div class="p-6">
                    @if($proposal->hasPdf())
                        <!-- PDF Viewer -->
                        <div class="bg-gray-100 rounded-lg overflow-hidden" style="height: 800px;">
                            <iframe src="{{ $proposal->pdf_url }}" 
                                    class="w-full h-full border-0"
                                    title="{{ $proposal->title }}">
                                <p class="p-4 text-center">
                                    Your browser does not support PDF viewing. 
                                    <a href="{{ $proposal->pdf_url }}" class="text-emerald-600 hover:underline">Download the PDF</a> instead.
                                </p>
                            </iframe>
                        </div>
                    @else
                        <!-- No PDF Available -->
                        <div class="text-center py-16">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No PDF Available</h3>
                            <p class="mt-2 text-sm text-gray-500">This proposal does not have a PDF document attached.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    function copyVerificationUrl() {
        const urlInput = document.getElementById('verification-url');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(urlInput.value).then(() => {
            // Show success notification
            const button = event.target.closest('button');
            const originalHTML = button.innerHTML;
            
            button.innerHTML = `
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy URL:', err);
            alert('Failed to copy URL. Please copy manually.');
        });
    }
</script>
@endpush
@endsection