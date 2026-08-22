<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgramContextController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasDualProgramAccess(), 403);

        $context = $request->validate([
            'context' => 'required|in:codecamp,codeclub',
        ])['context'];

        if ($context === 'codeclub') {
            abort_unless(config('features.code_club', false), 404);
        }

        session(['active_program_context' => $context]);

        return redirect()->back()->with('message', 'Switched to ' . ($context === 'codeclub' ? 'Code Club' : 'CodeCamp') . ' view.');
    }
}
