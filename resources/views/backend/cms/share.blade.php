<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $media->name }} · Shared via Schedara</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
          colors: {
            ink:   '#021b2e',
            paper: '#f5fefe',
            line:  '#e3e9ee',
            brand: {
              50:'#eef5fb', 100:'#dceaf5', 200:'#b4cfe8',
              300:'#8bb4dc', 400:'#65a1d8', 500:'#4a8ccc',
              600:'#2f76bd', 700:'#235b95', 800:'#18406d',
            },
            mint: '#22B07E',
          },
        },
      },
    };
  </script>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:#f5fefe;color:#021b2e;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column;}
  </style>
</head>
<body>

  {{-- Minimal header --}}
  <header class="bg-white border-b border-line px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <div class="w-7 h-7 rounded-lg bg-brand-500 flex items-center justify-center">
        <svg viewBox="0 0 32 32" class="w-4 h-4" style="stroke:#fff;stroke-width:2.4;stroke-linecap:round;fill:none">
          <path d="M4 22 C 10 13, 19 10, 28 11"/>
          <path d="M8 22 C 13 16, 20 14, 27 15" opacity=".7"/>
          <path d="M12 22 C 16 19, 22 17, 27 19" opacity=".45"/>
        </svg>
      </div>
      <span class="font-bold text-ink text-sm">Schedara</span>
    </div>
    <p class="text-xs text-ink/40">Shared file</p>
  </header>

  {{-- Main content --}}
  <main class="flex-1 flex flex-col items-center justify-center p-6 py-12">

    {{-- Preview card --}}
    <div class="w-full max-w-3xl bg-white rounded-2xl border border-line shadow-sm overflow-hidden">

      {{-- Media preview --}}
      <div class="bg-ink/5 flex items-center justify-center min-h-[300px] max-h-[560px] overflow-hidden p-4">
        @if($media->isImage())
          <img src="{{ $media->publicUrl() }}" alt="{{ $media->alt_text }}"
               class="max-w-full max-h-[540px] object-contain rounded-xl">
        @elseif($media->isVideo())
          <video src="{{ $media->publicUrl() }}" controls
                 class="max-w-full max-h-[540px] rounded-xl"></video>
        @elseif($media->isAudio())
          <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-ink/20 mb-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            <audio src="{{ $media->publicUrl() }}" controls></audio>
          </div>
        @else
          <div class="text-center py-14">
            <svg class="w-20 h-20 mx-auto text-ink/15 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            <p class="text-sm text-ink/40 font-medium">{{ strtoupper($media->extension) }} Document</p>
          </div>
        @endif
      </div>

      {{-- Info bar --}}
      <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="font-bold text-ink text-lg leading-tight">{{ $media->name }}</h1>
          <div class="flex items-center gap-3 mt-1 text-sm text-ink/40">
            <span class="capitalize">{{ $media->type }}</span>
            <span>·</span>
            <span>{{ $media->humanSize() }}</span>
            @if($media->width && $media->height)
              <span>·</span>
              <span>{{ $media->width }}×{{ $media->height }}px</span>
            @endif
            @if($media->duration)
              <span>·</span>
              <span>{{ $media->humanDuration() }}</span>
            @endif
          </div>
          @if($media->alt_text)
            <p class="text-xs text-ink/40 mt-1.5 italic">{{ $media->alt_text }}</p>
          @endif
        </div>

        <a href="{{ $media->publicUrl() }}" download="{{ $media->original_name }}"
           class="flex-shrink-0 flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90 shadow-sm"
           style="background:#4a8ccc">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Download
        </a>
      </div>

    </div>

    {{-- Expiry notice --}}
    @if($media->share_expires_at)
      <p class="mt-4 text-xs text-ink/30">
        This link expires {{ $media->share_expires_at->diffForHumans() }}.
      </p>
    @endif

  </main>

  {{-- Footer --}}
  <footer class="py-5 text-center text-xs text-ink/25">
    Shared via <a href="{{ route('home') }}" class="hover:text-ink/50 transition-colors">Schedara</a>
  </footer>

</body>
</html>
