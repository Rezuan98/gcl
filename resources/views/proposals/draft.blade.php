@extends('dashboard.dashboard')

@section('content')
  <!-- Heading -->
  <div class="mb-6">
    <h1 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Draft Proposals</h1>
    <p class="text-emerald-700/70">Drafts are hidden from public verification until published.</p>
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

  <!-- Filters -->
  <form method="GET" class="mb-4 flex flex-col sm:flex-row gap-3">
    <input name="search" value="{{ request('search') }}" placeholder="Search proposal no / title / client…"
           class="px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">

    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Search</button>
      <a href="{{ route('proposals.drafts') }}" class="px-4 py-2 rounded-lg border border-emerald-200 text-emerald-800 hover:bg-emerald-50">Reset</a>
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
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Contact Phone</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Status</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-emerald-50">
        @forelse ($proposals as $p)
          <tr class="hover:bg-emerald-50/40">
            <td class="px-6 py-3 text-sm font-medium text-emerald-900">{{ $p->proposal_no }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ Str::limit($p->title, 40) }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->client_org ?? 'Not specified' }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">
              @if(!is_null($p->amount)) ৳ {{ number_format($p->amount, 2) }} @else Not specified @endif
            </td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->masked_phone }}</td>
            <td class="px-6 py-3 text-sm">
              <span class="px-2 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 ring-1 ring-gray-200">Draft</span>
            </td>
            <td class="px-6 py-3 text-sm">
              <div class="flex items-center gap-2">
                {{-- <a href="{{ route('proposals.show', $p) }}" 
                   class="text-emerald-600 hover:text-emerald-800 font-medium">View</a> --}}
                
                <form method="POST" action="{{ route('proposals.publish', $p) }}" class="inline">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium">Publish</button>
                </form>
                
                <form method="POST" action="{{ route('proposals.destroy', $p) }}" class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this draft?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-6 text-center text-emerald-700">No drafts found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="px-6 py-4">
      {{ $proposals->links() }}
    </div>
  </div>

  <div class="mt-4 text-sm">
    <a href="{{ route('proposals.index') }}" class="text-emerald-700 hover:underline">Back to All Proposals</a>
  </div>
@endsection