@extends('dashboard.dashboard')

@section('content')
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-emerald-800">Dashboard</h1>
            <p class="text-emerald-600 mt-1">Overview of your tender proposal system</p>
        </div>
        <div class="flex gap-3">
            <button id="btn-open-create"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-lg shadow-emerald-600/20 transition-all duration-200 hover:scale-105">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Proposal
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg shadow hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition duration-200">
                    Logout
                </button>
            </form>
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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Proposals -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-600">Total Proposals</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="h-12 w-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <span class="text-{{ $stats['monthly_growth'] >= 0 ? 'green' : 'red' }}-500 text-sm font-medium">
                    {{ $stats['monthly_growth'] >= 0 ? '+' : '' }}{{ $stats['monthly_growth'] }}
                </span>
                <span class="text-gray-500 text-sm ml-1">this month</span>
            </div>
        </div>

        <!-- Verified -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-600">Verified</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['verified'] }}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <span class="text-gray-500 text-sm">{{ $stats['verification_percentage'] }}% of total</span>
            </div>
        </div>

        <!-- Pending + Drafts Combined -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-600">Pending & Drafts</p>
                    <div class="flex items-baseline gap-2 mt-2">
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        <span class="text-sm text-gray-500">pending</span>
                        <span class="text-gray-300">•</span>
                        <p class="text-2xl font-bold text-gray-600">{{ $stats['drafts'] }}</p>
                        <span class="text-sm text-gray-500">drafts</span>
                    </div>
                </div>
                <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex items-center mt-4">
                <span class="text-gray-500 text-sm">Awaiting action</span>
            </div>
        </div>
    </div>

    @include('proposals.partials.create-modal')
@endsection

@push('scripts')
<script>
    // Modal functionality will be in the create-modal partial
</script>
@endpush