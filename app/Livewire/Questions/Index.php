<?php

namespace App\Livewire\Questions;

use App\Models\Course;
use App\Models\Question;
use App\Models\Tag;
use App\Services\Assessments\QuestionWriter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Question Bank')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: '')]
    public string $difficulty = '';

    #[Url(except: 'active')]
    public string $status = 'active';

    #[Url(except: '')]
    public string $tag = '';

    #[Url(except: '')]
    public string $course = '';

    /** @var array<int, int|string> */
    public array $selected = [];

    public string $bulkTags = '';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'type', 'difficulty', 'status', 'tag', 'course'], true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'type', 'difficulty', 'tag', 'course', 'selected']);
        $this->status = 'active';
        $this->resetPage();
    }

    public function togglePage(string $ids): void
    {
        $pageIds = array_values(array_filter(explode(',', $ids), 'is_numeric'));
        $selected = array_map('strval', $this->selected);

        $this->selected = array_diff($pageIds, $selected) === []
            ? array_values(array_diff($selected, $pageIds))
            : array_values(array_unique(array_merge($selected, $pageIds)));
    }

    protected function baseQuery()
    {
        return Question::query()
            ->visibleTo(Auth::user())
            ->whereNull('questions.quiz_id');
    }

    protected function filteredQuery()
    {
        return $this->baseQuery()
            ->when($this->search !== '', fn ($q) => $q->where('question_text', 'like', '%'.$this->search.'%'))
            ->when($this->type !== '', fn ($q) => $q->where('question_type', $this->type))
            ->when($this->difficulty !== '', fn ($q) => $q->where('difficulty', $this->difficulty))
            ->when($this->status !== '' && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->tag !== '', fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('tags.id', $this->tag)))
            ->when($this->course !== '', fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('placements', fn ($p) => $p->where('course_id', $this->course))
                ->orWhereHas('assessments', fn ($a) => $a->where('assessments.course_id', $this->course))));
    }

    /**
     * Only ids the user can see are ever acted on.
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, int>
     */
    protected function visibleIds(array $ids): array
    {
        return $this->baseQuery()->whereIn('questions.id', array_map('intval', $ids))->pluck('questions.id')->map(fn ($id) => (int) $id)->all();
    }

    public function setStatus(int $questionId, string $status): void
    {
        abort_unless(isset(Question::STATUSES[$status]), 422);
        $ids = $this->visibleIds([$questionId]);
        abort_if($ids === [], 403);

        Question::whereKey($ids[0])->first()?->update(['status' => $status]);
        session()->flash('message', 'Question '.($status === 'archived' ? 'archived' : 'set to '.$status).'.');
    }

    public function duplicate(int $questionId, QuestionWriter $writer)
    {
        abort_if($this->visibleIds([$questionId]) === [], 403);

        $copy = $writer->duplicate(Question::with(['options', 'tags', 'placements'])->findOrFail($questionId));

        return $this->redirect(route('questions.edit', $copy), navigate: true);
    }

    public function delete(int $questionId): void
    {
        abort_if($this->visibleIds([$questionId]) === [], 403);

        Question::findOrFail($questionId)->delete();
        session()->flash('message', 'Question deleted. Assessments no longer include it; past attempts keep their copy.');
    }

    public function bulk(string $action): void
    {
        $ids = $this->visibleIds($this->selected);
        if ($ids === []) {
            return;
        }

        $questions = Question::whereIn('id', $ids)->get();

        match ($action) {
            'archive' => $questions->each->update(['status' => 'archived']),
            'activate' => $questions->each->update(['status' => 'active']),
            'draft' => $questions->each->update(['status' => 'draft']),
            'delete' => $questions->each->delete(),
            'tag' => $this->bulkTag($questions),
            default => abort(422),
        };

        $this->selected = [];
        session()->flash('message', count($ids).' question(s) updated.');
    }

    protected function bulkTag($questions): void
    {
        $tags = Tag::findOrCreateMany(preg_split('/[,\n]/', $this->bulkTags) ?: []);
        if ($tags->isEmpty()) {
            return;
        }

        $questions->each(fn (Question $q) => $q->tags()->syncWithoutDetaching($tags->pluck('id')));
        $this->bulkTags = '';
    }

    public function render()
    {
        $user = Auth::user();

        $questions = $this->filteredQuery()
            ->with(['tags', 'placements.course:id,title', 'placements.module:id,title', 'creator:id,name'])
            ->withCount('assessments')
            ->latest('questions.id')
            ->paginate(25);

        $stats = $this->baseQuery()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'active') as active")
            ->selectRaw("SUM(status = 'draft') as draft")
            ->selectRaw("SUM(status = 'archived') as archived")
            ->first();

        $unused = $this->baseQuery()->whereDoesntHave('assessments')->count();

        return view('livewire.questions.index', [
            'questions' => $questions,
            'stats' => $stats,
            'unused' => $unused,
            'types' => QuestionEditor::BANK_TYPES,
            'difficulties' => Question::DIFFICULTIES,
            'statuses' => Question::STATUSES,
            'tags' => Tag::whereHas('questions', fn ($q) => $q->visibleTo($user))->orderBy('name')->get(['id', 'name']),
            'courses' => Course::query()
                ->when(! $user->isAdmin(), fn ($q) => $q->where(fn ($w) => $w
                    ->where('instructor_id', $user->id)
                    ->orWhereHas('collaborators', fn ($c) => $c->where('user_id', $user->id))))
                ->orderBy('title')
                ->get(['id', 'title']),
            'pageIds' => $questions->getCollection()->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ]);
    }
}
