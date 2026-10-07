@php
    /** @var \App\Models\StudentBookAnnotation $annotation */
    $hex = $annotation->getColorHex();
    $colorLabel = ucfirst($annotation->color);
    $dotStyle = 'style="background-color: ' . $hex . '; border: 1px solid rgba(0,0,0,0.2);"';
    $quoteStyle = 'style="border-left-color: ' . $hex . '; background-color: rgba(0,0,0,0.03);"';
@endphp
<div class="space-y-4">
    <!-- Header Lokasi & Warna -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full" {!! $dotStyle !!}></span>
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                Stabilo {{ $colorLabel }}
            </span>
        </div>
        <span class="text-xs text-gray-400">
            {{ $annotation->created_at->format('d M Y, H:i') }}
        </span>
    </div>

    <!-- Kutipan Teks Disorot (Jika Ada) -->
    @if($annotation->highlighted_text)
        <div>
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Kutipan Teks yang Ditandai:</span>
            <div class="p-3.5 rounded-xl text-sm italic font-serif leading-relaxed border-l-4" {!! $quoteStyle !!}>
                "{{ $annotation->highlighted_text }}"
            </div>
        </div>
    @endif

    <!-- Catatan / Rangkuman Siswa -->
    <div>
        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Catatan & Rangkuman Siswa:</span>
        @if($annotation->note)
            <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 text-sm text-gray-800 dark:text-gray-200 whitespace-pre-line leading-relaxed shadow-xs">
                📌 {{ $annotation->note }}
            </div>
        @else
            <p class="text-xs text-gray-400 italic">Hanya stabilo tanpa catatan tambahan.</p>
        @endif
    </div>

    <!-- Info Buku -->
    <div class="pt-3 text-xs text-gray-500 dark:text-gray-400 flex items-center justify-between border-t border-gray-100 dark:border-gray-800">
        <span>📖 {{ $annotation->book->title }}</span>
        <span>
            @if($annotation->chapter)
                {{ $annotation->chapter->title }}
            @endif
            @if($annotation->page)
                • Halaman {{ $annotation->page->page_number }}
            @endif
        </span>
    </div>
</div>

