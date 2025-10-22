{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GCL | Tender Verification</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    html, body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; }
    .glass {
      backdrop-filter: blur(8px);
      background: rgba(255,255,255,0.7);
    }
    .green-gradient {
      background: #115D28;
    }
    .green-soft {
      background: linear-gradient(180deg, #ecfdf5 0%, #f0fdf4 100%);
    }
  </style>
</head>
<body class="min-h-screen green-soft">
  <!-- Top Nav -->
  <header class="green-gradient text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        {{-- <div class="h-10 w-10 rounded-xl bg-white/15 flex items-center justify-center ring-1 ring-white/20">
          <span class="font-extrabold text-xl">GCL</span>
        </div> --}}
        <div>
          <div class="text-xl font-extrabold tracking-tight"><a href="{{ url('/') }}"><img src="{{ asset('logo.png') }}" alt=""></a></div>
          {{-- <div class="text-white/80 text-sm -mt-0.5">Tender Verification Console</div> --}}
        </div>
      </div>
      <nav class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</a>
        <a href="{{ route('proposals.index') }}" class="px-3 py-2 rounded-lg hover:bg-white/10">Proposals</a>
        
      </nav>
    </div>
  </header>

  <!-- Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-sm text-gray-600">
      © {{ now()->year }} Grameen Cybernet Limited — Tender Verification System
    </div>
  </footer>

  @stack('modals')
  @stack('scripts')
</body>
</html>
