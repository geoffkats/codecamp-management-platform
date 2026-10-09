<?php

namespace App\Livewire\Admin\Camps;

use App\Models\CampReport;
use App\Models\CodeCamp;
use App\Services\Reports\CampReportNarrator;
use App\Services\Reports\CampReportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Report extends Component
{
    public CodeCamp $camp;

    public bool $editing = false;

    public string $summary = '';

    public string $highlights = '';

    public string $challenges = '';

    public string $recommendations = '';

    public function mount(CodeCamp $camp, CampReportService $reports, CampReportNarrator $narrator): void
    {
        abort_unless(Auth::user()->can('review_daily_reports'), 403);

        $this->camp = $camp;

        if (! $this->report()) {
            $this->store($narrator->write($reports->build($camp), useAi: false));
        }

        $this->fillFromReport();
    }

    public function generate(CampReportService $reports, CampReportNarrator $narrator): void
    {
        $result = $narrator->write($reports->build($this->camp), useAi: true);
        $this->store($result);
        $this->fillFromReport();
        $this->editing = false;

        if (isset($result['ai_error'])) {
            session()->flash('warning', 'The AI could not write the report ('.$result['ai_error'].'). A summary was compiled from the camp data instead.');
        } elseif (! $narrator->aiAvailable()) {
            session()->flash('warning', 'AI is not set up (GEMINI_API_KEY), so the summary was compiled from the camp data.');
        } else {
            session()->flash('message', 'Report rewritten from the latest instructor reports and camp data.');
        }
    }

    public function startEditing(): void
    {
        $this->fillFromReport();
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->fillFromReport();
        $this->editing = false;
    }

    public function save(): void
    {
        $this->validate([
            'summary' => 'required|string|max:5000',
            'highlights' => 'nullable|string|max:5000',
            'challenges' => 'nullable|string|max:5000',
            'recommendations' => 'nullable|string|max:5000',
        ]);

        $this->report()->update([
            'summary' => trim($this->summary),
            'highlights' => trim($this->highlights),
            'challenges' => trim($this->challenges),
            'recommendations' => trim($this->recommendations),
            'source' => 'manual',
            'updated_by' => Auth::id(),
        ]);

        $this->editing = false;
        session()->flash('message', 'Report text saved.');
    }

    private function report(): ?CampReport
    {
        return CampReport::where('camp_id', $this->camp->id)->first();
    }

    private function store(array $narrative): void
    {
        CampReport::updateOrCreate(['camp_id' => $this->camp->id], [
            'summary' => $narrative['summary'],
            'highlights' => $narrative['highlights'],
            'challenges' => $narrative['challenges'],
            'recommendations' => $narrative['recommendations'],
            'source' => $narrative['source'],
            'generated_by' => Auth::id(),
            'generated_at' => now(),
        ]);
    }

    private function fillFromReport(): void
    {
        $report = $this->report();
        $this->summary = (string) $report?->summary;
        $this->highlights = (string) $report?->highlights;
        $this->challenges = (string) $report?->challenges;
        $this->recommendations = (string) $report?->recommendations;
    }

    public function render()
    {
        return view('livewire.admin.camps.report', [
            ...app(CampReportService::class)->build($this->camp),
            'narrative' => CampReport::with(['generatedBy:id,name', 'updatedBy:id,name'])->where('camp_id', $this->camp->id)->first(),
            'aiAvailable' => app(CampReportNarrator::class)->aiAvailable(),
        ]);
    }
}
