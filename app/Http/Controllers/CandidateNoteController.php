<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CandidateNoteController extends Controller
{
    public function store(Request $request, Candidate $candidate): RedirectResponse
    {
        $request->validate(['content' => 'required|string|max:2000']);

        $candidate->notes()->create(['content' => $request->content]);

        return back()->with('success', 'Nota guardada.');
    }

    public function destroy(Candidate $candidate, CandidateNote $note): RedirectResponse
    {
        abort_if($note->candidate_id !== $candidate->id, 403);
        $note->delete();

        return back()->with('success', 'Nota eliminada.');
    }
}
