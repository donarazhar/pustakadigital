<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\StudentQuizAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InteractiveQuiz extends Component
{
    public Quiz $quiz;
    public array $userAnswers = []; // [question_id => option_id]
    public bool $isSubmitted = false;
    public int $finalScore = 0;
    public int $correctCount = 0;
    public int $totalQuestions = 0;
    public bool $isPassed = false;
    public array $results = []; // [question_id => ['correct_option_id' => int, 'is_user_correct' => bool, 'explanation' => string]]

    public function mount(Quiz $quiz)
    {
        $this->quiz = $quiz->load(['questions.options']);
        $this->totalQuestions = $this->quiz->questions->count();
    }

    public function selectOption(int $questionId, int $optionId)
    {
        if ($this->isSubmitted) {
            return;
        }

        $this->userAnswers[$questionId] = $optionId;
    }

    public function submitQuiz()
    {
        if ($this->isSubmitted || empty($this->quiz->questions)) {
            return;
        }

        $totalScoreWeight = 0;
        $earnedScore = 0;
        $this->correctCount = 0;
        $this->results = [];

        foreach ($this->quiz->questions as $question) {
            $totalScoreWeight += $question->score_weight;
            $selectedOptionId = $this->userAnswers[$question->id] ?? null;

            // Cari opsi yang benar langsung di server
            $correctOption = $question->options->first(fn ($opt) => (bool) $opt->is_correct);
            $correctOptionId = $correctOption ? $correctOption->id : null;
            $isCorrect = ($selectedOptionId && $correctOptionId && (int)$selectedOptionId === (int)$correctOptionId);

            if ($isCorrect) {
                $earnedScore += $question->score_weight;
                $this->correctCount++;
            }

            // Simpan pembahasan dan kunci jawaban HANYA setelah siswa submit
            $this->results[$question->id] = [
                'correct_option_id' => $correctOptionId,
                'is_user_correct' => $isCorrect,
                'explanation' => $question->explanation,
            ];
        }

        // Hitung skala 0-100
        $this->finalScore = $totalScoreWeight > 0 ? (int) round(($earnedScore / $totalScoreWeight) * 100) : 0;
        $this->isPassed = $this->finalScore >= $this->quiz->passing_score;
        $this->isSubmitted = true;

        if (Auth::check()) {
            StudentQuizAttempt::create([
                'user_id' => Auth::id(),
                'quiz_id' => $this->quiz->id,
                'score' => $this->finalScore,
                'total_questions' => $this->totalQuestions,
                'correct_answers' => $this->correctCount,
                'is_passed' => $this->isPassed,
                'submitted_at' => now(),
            ]);
        }
    }

    public function retryQuiz()
    {
        $this->userAnswers = [];
        $this->results = [];
        $this->isSubmitted = false;
        $this->finalScore = 0;
        $this->correctCount = 0;
        $this->isPassed = false;
    }

    public function render()
    {
        return view('livewire.interactive-quiz');
    }
}
