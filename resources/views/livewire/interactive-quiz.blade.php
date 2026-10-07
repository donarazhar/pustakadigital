<div>
    <div class="quiz-header">
        <div style="font-size: 0.85rem; font-weight: 700; color: #4f46e5; text-transform: uppercase; margin-bottom: 6px;">
            Evaluasi Pemahaman Materi
        </div>
        <h2 class="quiz-title">{{ $quiz->title }}</h2>
        @if($quiz->description)
            <p class="quiz-desc">{{ $quiz->description }}</p>
        @endif
        <div style="margin-top: 10px; font-size: 0.85rem; font-weight: 600; color: #64748b;">
            Standar Kelulusan: <span style="color: #0f172a; font-weight: 800;">{{ $quiz->passing_score }} Poin</span>
        </div>
    </div>

    @if($isSubmitted)
        <!-- Quiz Results Banner -->
        <div class="quiz-score-banner {{ $isPassed ? 'passed' : 'failed' }}">
            <div style="font-size: 1.1rem; font-weight: 700;">
                {{ $isPassed ? '🎉 Selamat! Kamu Berhasil Menjawab dengan Baik' : '💪 Hasil Kuis Kamu Belum Memenuhi KKM' }}
            </div>
            <div class="score-number">{{ $finalScore }}</div>
            <p style="font-size: 0.95rem; opacity: 0.9;">
                Benar: {{ $correctCount }} dari {{ $totalQuestions }} pertanyaan
            </p>
            <div style="margin-top: 20px;">
                <button wire:click="retryQuiz" class="btn-nav-page" style="display: inline-flex; margin: 0 auto; color: #0f172a;">
                    🔄 Ulangi Kuis
                </button>
            </div>
        </div>
    @endif

    <!-- Questions List -->
    <div class="questions-container">
        @foreach($quiz->questions as $index => $q)
            @php
                $isQuestionCorrect = $results[$q->id]['is_user_correct'] ?? false;
                $correctOptId = $results[$q->id]['correct_option_id'] ?? null;
                $questionExplanation = $results[$q->id]['explanation'] ?? null;
            @endphp
            <div class="question-block {{ $isSubmitted ? ($isQuestionCorrect ? 'question-correct' : 'question-wrong') : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px;">
                    <span style="font-size: 0.8rem; font-weight: 800; color: #6366f1; text-transform: uppercase;">
                        Pertanyaan {{ $index + 1 }}
                    </span>
                    <span style="font-size: 0.8rem; font-weight: 600; color: #64748b;">
                        {{ $q->score_weight }} Poin
                    </span>
                </div>

                <div class="question-text">{{ $q->question_text }}</div>

                @if($q->question_image)
                    <img src="{{ asset('storage/' . $q->question_image) }}" alt="Ilustrasi Soal" style="max-height: 240px; border-radius: 8px; margin-bottom: 16px;">
                @endif

                <div class="options-grid">
                    @foreach($q->options as $opt)
                        @php
                            $isSelected = ($userAnswers[$q->id] ?? null) === $opt->id;
                            $optionClass = '';
                            if ($isSubmitted) {
                                if ($correctOptId && $opt->id === $correctOptId) {
                                    $optionClass = 'correct';
                                } elseif ($isSelected && $opt->id !== $correctOptId) {
                                    $optionClass = 'wrong';
                                }
                            } elseif ($isSelected) {
                                $optionClass = 'selected';
                            }
                        @endphp

                        <div wire:click="selectOption({{ $q->id }}, {{ $opt->id }})" class="option-item {{ $optionClass }}">
                            <div class="option-badge">
                                {{ chr(65 + $loop->index) }}
                            </div>
                            <span style="flex: 1;">{{ $opt->option_text }}</span>
                            @if($isSubmitted && $correctOptId && $opt->id === $correctOptId)
                                <span style="color: #059669; font-weight: 800;">✓ Benar</span>
                            @elseif($isSubmitted && $isSelected && $opt->id !== $correctOptId)
                                <span style="color: #dc2626; font-weight: 800;">✗ Pilihanmu</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($isSubmitted && $questionExplanation)
                    <div style="margin-top: 14px; padding: 12px 16px; background: rgba(255,255,255,0.8); border-radius: 8px; font-size: 0.85rem; border-left: 3px solid #6366f1;">
                        <strong>Pembahasan:</strong> {{ $questionExplanation }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @if(!$isSubmitted)
        <div style="text-align: center; margin-top: 32px;">
            <button wire:click="submitQuiz" class="btn-quiz-cta" style="margin: 0 auto;" {{ count($userAnswers) < $totalQuestions ? 'disabled' : '' }}>
                <span>Kirim Jawaban Kuis 🎯</span>
            </button>
            @if(count($userAnswers) < $totalQuestions)
                <p style="margin-top: 8px; font-size: 0.8rem; color: #94a3b8;">
                    Kamu baru menjawab {{ count($userAnswers) }} dari {{ $totalQuestions }} soal.
                </p>
            @endif
        </div>
    @endif
</div>
