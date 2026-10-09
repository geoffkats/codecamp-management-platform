<?php

namespace App\Livewire\Admin\CampReports;

use App\Models\CampReport;
use App\Models\CodeCamp;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $camps = CodeCamp::query()
            ->withCount(['enrollments', 'dailyReports'])
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByRaw("status = 'completed' desc")
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('livewire.admin.camp-reports.index', [
            'camps' => $camps,
            'reports' => CampReport::with('updatedBy:id,name')
                ->whereIn('camp_id', $camps->pluck('id'))
                ->get()
                ->keyBy('camp_id'),
        ]);
    }
}
