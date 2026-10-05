<?php

namespace App\Http\Controllers;

use App\Support\Help\HelpCenter;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function __construct(private HelpCenter $help) {}

    public function index(Request $request)
    {
        return $this->page($request, null);
    }

    public function show(Request $request, string $guide)
    {
        abort_unless($this->help->guide($request->user(), $guide), 404);

        return $this->page($request, $guide);
    }

    public function panel(Request $request)
    {
        abort_unless($this->help->canSee($request->user()), 403);

        return view('help.panel', $this->data($request) + ['mode' => 'drawer', 'initialGuide' => null]);
    }

    private function page(Request $request, ?string $guide)
    {
        abort_unless($this->help->canSee($request->user()), 403);

        return view('help.index', $this->data($request) + ['mode' => 'page', 'initialGuide' => $guide]);
    }

    private function data(Request $request): array
    {
        $user = $request->user();

        return [
            'guides' => $this->help->guides($user),
            'updates' => $this->help->updates($user),
            'latestKey' => $this->help->latestUpdateKey($user),
        ];
    }
}
