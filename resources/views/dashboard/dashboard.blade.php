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
    html, body { 
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; 
    }
    
    /* Custom GCL Green Color */
    :root {
      --gcl-green: #115D28;
      --gcl-green-dark: #0d4720;
      --gcl-green-light: #1a7335;
      --gcl-green-lightest: #e8f5e9;
    }
    
    /* Improved gradient background with GCL green tones */
    .gradient-bg {
      background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 50%, #a5d6a7 100%);
      min-height: 100vh;
    }
    
    /* Header styling with GCL green */
    .header-gradient {
      background: linear-gradient(135deg, var(--gcl-green) 0%, var(--gcl-green-dark) 100%);
      box-shadow: 0 4px 6px -1px rgba(17, 93, 40, 0.2), 0 2px 4px -1px rgba(17, 93, 40, 0.1);
    }
    
    /* GCL Green utilities */
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
    
    /* Smooth transitions */
    * {
      transition: all 0.2s ease-in-out;
    }
  </style>
</head>
<body class="gradient-bg">
  <!-- Top Nav -->
  <header class="header-gradient text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div>
          <a href="{{ url('/') }}" class="hover:opacity-90 transition-opacity">
            <img src="{{ asset('logo.png') }}" alt="GCL Logo" class="h-10">
          </a>
        </div>
      </div>
      <nav class="flex items-center gap-2">
        <a href="{{ route('dashboard') }}" 
           class="px-4 py-2 rounded-lg hover:bg-white/10 font-medium {{ request()->routeIs('dashboard') ? 'bg-white/20' : '' }}">
          Dashboard
        </a>
        <a href="{{ route('proposals.index') }}" 
           class="px-4 py-2 rounded-lg hover:bg-white/10 font-medium {{ request()->routeIs('proposals.*') ? 'bg-white/20' : '' }}">
          Proposals
        </a>
      </nav>
    </div>
  </header>

  <!-- Content -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="mt-16 border-t border-green-200 bg-white/50 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-sm text-gcl-green-dark">
          © {{ now()->year }} Grameen Cybernet Limited – Tender Verification System
        </p>
        <div class="flex items-center gap-2 text-xs text-gcl-green">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
          </svg>
          Secured Platform
        </div>
      </div>
    </div>
  </footer>

  @stack('modals')
  @stack('scripts')
</body>
</html>