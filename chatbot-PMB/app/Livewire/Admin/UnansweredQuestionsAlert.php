<?php

namespace App\Livewire\Admin;

use App\Models\ChatHistory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UnansweredQuestionsAlert extends Component
{
    public string $search = '';

    public function markAsValidated(int $id): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $chatHistory = ChatHistory::find($id);

        if ($chatHistory) {
            $chatHistory->update(['is_validated' => true]);
            session()->flash('success_validation', 'Pertanyaan berhasil divalidasi!');
        }
    }

    public function markAllAsValidated(): void
    {
        if (! auth()->user()->isAdmin()) {
            return;
        }

        $likeOp = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $updatedCount = ChatHistory::where('is_validated', false)
            ->where(function ($q) use ($likeOp) {
                $q->where('answer', $likeOp, '%maaf%')
                    ->orWhere('answer', $likeOp, '%belum tersedia%');
            })
            ->when($this->search, function ($q) use ($likeOp) {
                $q->where(function ($sub) use ($likeOp) {
                    $sub->where('question', $likeOp, '%'.$this->search.'%')
                        ->orWhere('answer', $likeOp, '%'.$this->search.'%');
                });
            })
            ->update(['is_validated' => true]);

        if ($updatedCount > 0) {
            session()->flash('success_validation', "Berhasil memvalidasi {$updatedCount} pertanyaan!");
        }
    }

    public function render()
    {
        $likeOp = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $baseQuery = ChatHistory::with('user')
            ->where('is_validated', false)
            ->where(function ($q) use ($likeOp) {
                $q->where('answer', $likeOp, '%maaf%')
                    ->orWhere('answer', $likeOp, '%belum tersedia%');
            });

        $unansweredCount = (clone $baseQuery)->count();

        $unansweredQuestions = (clone $baseQuery)
            ->when($this->search, function ($q) use ($likeOp) {
                $q->where(function ($sub) use ($likeOp) {
                    $sub->where('question', $likeOp, '%'.$this->search.'%')
                        ->orWhere('answer', $likeOp, '%'.$this->search.'%');
                });
            })
            ->latest()
            ->get();

        return view('livewire.admin.unanswered-questions-alert', [
            'unansweredCount' => $unansweredCount,
            'unansweredQuestions' => $unansweredQuestions,
        ]);
    }
}
