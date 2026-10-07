<div style="font-size: 13px; line-height: 1.5; color: #374151;">
    <div style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 16px;">
        @if ($book->cover_image)
            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" style="width: 120px; height: 165px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; flex-shrink: 0;">
        @else
            <img src="{{ asset('images/default-book-cover.png') }}" alt="{{ $book->title }}" style="width: 120px; height: 165px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; flex-shrink: 0;">
        @endif

        <div style="flex: 1;">
            <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 700; color: #111827;">{{ $book->title }}</h3>
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #6b7280;">
                Penulis: <strong style="color: #374151;">{{ $book->author ?? '-' }}</strong> &bull; 
                Penerbit: <strong style="color: #374151;">{{ $book->publisher ?? '-' }}</strong> ({{ $book->publication_year ?? '-' }})
            </p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 12px;">
                @if ($book->grade)
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; background-color: #dbeafe; color: #1e40af;">
                        {{ $book->grade->name }}
                    </span>
                @endif
                @if ($book->subject)
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; background-color: #d1fae5; color: #065f46;">
                        {{ $book->subject->name }}
                    </span>
                @endif
                @if ($book->estimated_read_time)
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; background-color: #fef3c7; color: #92400e;">
                        ⏱️ ± {{ $book->estimated_read_time }} menit
                    </span>
                @endif
            </div>

            <div style="font-size: 12px; color: #4b5563; line-height: 1.6; max-height: 90px; overflow-y: auto;">
                {!! $book->description ?: '<em>Tidak ada sinopsis untuk buku ini.</em>' !!}
            </div>
        </div>
    </div>

    <div style="border-top: 1px solid #e5e7eb; padding-top: 12px; margin-bottom: 16px;">
        <h4 style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280;">Daftar Bab & Materi</h4>
        <div style="border: 1px solid #e5e7eb; border-radius: 8px; max-height: 160px; overflow-y: auto; background-color: #fafafa;">
            @forelse ($book->chapters as $index => $chapter)
                <div style="padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6; font-size: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 22px; height: 22px; border-radius: 50%; background-color: #10b981; color: #ffffff; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            {{ $index + 1 }}
                        </span>
                        <span style="font-weight: 600; color: #1f2937;">{{ $chapter->title }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if ($chapter->quiz && $chapter->quiz->is_active)
                            <span style="padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; background-color: #ede9fe; color: #6d28d9;">
                                🎯 Ada Kuis
                            </span>
                        @endif
                        <span style="font-size: 11px; color: #9ca3af;">{{ $chapter->pages->count() }} hal</span>
                    </div>
                </div>
            @empty
                <div style="padding: 12px; font-size: 12px; color: #9ca3af; font-style: italic;">Belum ada bab materi terdaftar.</div>
            @endforelse
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end;">
        <a href="{{ route('books.read', $book->slug) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; background-color: #059669; color: #ffffff; text-decoration: none; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: background-color 0.2s;">
            <svg style="width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            Buka Pembaca Buku 3D
        </a>
    </div>
</div>
