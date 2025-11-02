@extends('dashboard.dashboard')

@section('content')
  <!-- Heading -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Proposals</h1>
      <p class="text-emerald-700/70">All proposals (excluding drafts). Verify using Proposal No. (common QR).</p>
    </div>

    {{-- <button id="btn-open-create"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-lg shadow-emerald-600/20 transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Add Proposal
    </button> --}}
  </div>

  <!-- Filters -->
  <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-3">
    <input name="search" value="{{ request('search') }}" placeholder="Search proposal no / title / client…"
           class="px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">

    <select name="status"
            class="px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <option value="">Status: Any</option>
      @foreach (['pending','verified','rejected'] as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
      @endforeach
    </select>

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Filter</button>
      <a href="{{ route('proposals.index') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-800 hover:bg-emerald-50">Reset</a>
    </div>
  </form>

  <!-- Table -->
  <div class="bg-white rounded-2xl ring-1 ring-emerald-600/10 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-emerald-100">
      <thead class="bg-emerald-50/60">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Proposal No.</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Title</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Client / Organization</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Amount</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">POC(phone)</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-emerald-50">
        @forelse ($proposals as $p)
          <tr class="hover:bg-emerald-50/40">
            <td class="px-6 py-3 text-sm font-medium text-emerald-900">{{ $p->proposal_no }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->title }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->client_org }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">
              @if(!is_null($p->amount)) ৳ {{ number_format($p->amount, 2) }} @endif
            </td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->client_phone }}</td>
            <td class="px-6 py-3 text-sm">
              <span class="px-2 py-1 rounded-lg text-xs font-semibold
                @if($p->status==='verified') bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200
                @elseif($p->status==='pending') bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200
                @elseif($p->status==='rejected') bg-rose-100 text-rose-700 ring-1 ring-rose-200
                @endif">
                {{ ucfirst($p->status) }}
              </span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-6 text-center text-emerald-700">No proposals found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="px-6 py-4">
      {{ $proposals->links() }}
    </div>
  </div>

  <!-- Footer links -->
  <div class="mt-4 text-sm">
    <a href="{{ route('proposals.drafts') }}" class="text-emerald-700 hover:underline">View Draft Proposals</a>
    <span class="mx-2 text-emerald-400">•</span>
    <a href="{{ route('verify.show') }}" class="text-emerald-700 hover:underline">Public Verify Page (QR target)</a>
  </div>

  {{-- Optional: if you want to reuse your existing "Add Proposal" slide-over from the dashboard,
       include it here via @include('partials.create-proposal') and keep the same JS open/close IDs. --}}
@endsection
