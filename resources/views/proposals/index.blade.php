@extends('dashboard.dashboard')

@section('content')
  <!-- Heading -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Proposals</h1>
      <p class="text-emerald-700/70">All proposals with unique verification URLs</p>
    </div>

    <button id="btn-open-create"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-lg shadow-emerald-600/20 transition">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      Add Proposal
    </button>
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
    <input name="search" value="{{ request('search') }}" placeholder="Search title / company / phone…"
           class="px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">

    <select name="status"
            class="px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <option value="">Status: Any</option>
      @foreach (['pending','verified'] as $s)
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
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Title</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Company</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Phone</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">PDF</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Status</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Verification URL</th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-emerald-900">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-emerald-50">
        @forelse ($proposals as $p)
          <tr class="hover:bg-emerald-50/40">
            <td class="px-6 py-3 text-sm text-emerald-800">{{ Str::limit($p->title, 30) }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->company_name }}</td>
            <td class="px-6 py-3 text-sm text-emerald-800">{{ $p->masked_phone }}</td>
            <td class="px-6 py-3 text-sm">
              @if($p->hasPdf())
                <span class="text-green-600">✓ Uploaded</span>
              @else
                <span class="text-red-600">✗ Missing</span>
              @endif
            </td>
            <td class="px-6 py-3 text-sm">
              <span class="px-2 py-1 rounded-lg text-xs font-semibold
                @if($p->status==='verified') bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200
                @elseif($p->status==='pending') bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200
                @endif">
                {{ ucfirst($p->status) }}
                @if($p->status === 'verified' && $p->verification_count > 0)
                  ({{ $p->verification_count }}x)
                @endif
              </span>
            </td>
            <td class="px-6 py-3 text-sm">
              <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $p->verification_url }}" 
                  class="text-xs bg-gray-50 border border-gray-200 rounded px-2 py-1 w-48 truncate"
                  id="url-{{ $p->id }}">
                <button onclick="copyUrl('{{ $p->id }}', '{{ $p->verification_url }}')"
                  class="text-emerald-600 hover:text-emerald-800">
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                </button>
              </div>
            </td>
            <td class="px-6 py-3 text-sm">
              <div class="flex items-center gap-2">
                <a href="{{ route('proposals.show', $p) }}" 
                   class="text-emerald-600 hover:text-emerald-800 font-medium">View</a>
                
                <form method="POST" action="{{ route('proposals.destroy', $p) }}" class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this proposal?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-6 py-6 text-center text-emerald-700">No proposals found.</td>
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
  </div>

  @include('proposals.partials.create-modal')
@endsection

@push('scripts')
<script>
  function copyUrl(id, url) {
    navigator.clipboard.writeText(url).then(() => {
      // Show success message
      const btn = event.target.closest('button');
      const originalHTML = btn.innerHTML;
      btn.innerHTML = '<svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
      
      setTimeout(() => {
        btn.innerHTML = originalHTML;
      }, 2000);
    });
  }
</script>
@endpush