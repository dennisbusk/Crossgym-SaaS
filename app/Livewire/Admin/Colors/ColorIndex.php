<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Colors;

use App\Models\Color;
use App\Models\GymClass;
use App\Traits\WithSorting;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ColorIndex extends Component
{
    use WithPagination;
    use WithSorting;

    protected string $paginationTheme = 'tailwind';

    #[Url]
    public string $search = '';

    public ?int $selectedColorId = null;

    public ?int $targetColorId = null;

    public bool $showMoveModal = false;

    // Create functionality
    public bool $showCreateModal = false;

    public string $newName = '';

    public string $newColorHex = '#000000';

    // Edit functionality
    public bool $showEditModal = false;

    public ?int $editingColorId = null;

    public string $editingName = '';

    public string $editingColorHex = '';

    // Delete functionality
    public bool $showDeleteModal = false;

    public ?int $deletingColorId = null;

    public ?int $replacementColorId = null;

    public int $classesCountForDelete = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected $rules = [
        'editingName' => 'required|min:2',
        'editingColorHex' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        'newName' => 'required|min:2',
        'newColorHex' => 'required|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
    ];

    public function create(): void
    {
        $this->reset(['newName', 'newColorHex']);
        $this->newColorHex = '#000000';
        $this->showCreateModal = true;
    }

    public function store(): void
    {
        $this->validateOnly('newName');
        $this->validateOnly('newColorHex');

        Color::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $this->newName,
            'color' => $this->newColorHex,
        ]);

        session()->flash('status', __('Color created successfully.'));
        $this->showCreateModal = false;
        $this->reset(['newName', 'newColorHex']);
    }

    public function edit(int $colorId): void
    {
        $color = Color::where('tenant_id', Auth::user()->tenant_id)->findOrFail($colorId);
        $this->editingColorId = $color->id;
        $this->editingName = $color->name;
        $this->editingColorHex = $color->color;
        $this->showEditModal = true;
    }

    public function update(): void
    {
        $this->validate();

        $color = Color::where('tenant_id', Auth::user()->tenant_id)->findOrFail($this->editingColorId);
        $color->update([
            'name' => $this->editingName,
            'color' => $this->editingColorHex,
        ]);

        session()->flash('status', __('Color updated successfully.'));
        $this->showEditModal = false;
        $this->reset(['editingColorId', 'editingName', 'editingColorHex']);
    }

    public function confirmDelete(int $colorId): void
    {
        $tenantId = Auth::user()->tenant_id;
        $color = Color::where('tenant_id', $tenantId)->findOrFail($colorId);

        $this->deletingColorId = $color->id;
        $this->replacementColorId = null;
        $this->classesCountForDelete = GymClass::where('tenant_id', $tenantId)
            ->where('color_id', $colorId)
            ->count();

        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $tenantId = Auth::user()->tenant_id;
        $color = Color::where('tenant_id', $tenantId)->findOrFail($this->deletingColorId);

        if ($this->classesCountForDelete > 0 && $this->replacementColorId) {
            GymClass::where('tenant_id', $tenantId)
                ->where('color_id', $this->deletingColorId)
                ->update(['color_id' => $this->replacementColorId]);
        }

        $color->delete();

        session()->flash('status', __('Color deleted successfully.'));
        $this->showDeleteModal = false;
        $this->reset(['deletingColorId', 'classesCountForDelete', 'replacementColorId']);

        // Reset pagination after delete
        $this->resetPage();
    }

    public function moveClasses(): void
    {
        if (! $this->selectedColorId || ! $this->targetColorId) {
            return;
        }

        if ($this->selectedColorId === $this->targetColorId) {
            $this->addError('targetColorId', __('You cannot move classes to the same color.'));

            return;
        }

        $tenantId = Auth::user()->tenant_id;

        GymClass::where('tenant_id', $tenantId)
            ->where('color_id', $this->selectedColorId)
            ->update(['color_id' => $this->targetColorId]);

        session()->flash('status', __('Classes moved successfully.'));
        $this->showMoveModal = false;
        $this->selectedColorId = null;
        $this->targetColorId = null;
    }

    public function openMoveModal(int $colorId): void
    {
        $this->selectedColorId = $colorId;
        $this->showMoveModal = true;
    }

    public function render()
    {
        $tenantId = Auth::user()->tenant_id;

        $colors = Color::where('tenant_id', $tenantId)
            ->where('name', 'like', '%'.$this->search.'%')
            ->withCount(['classes' => function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }]);

        $colors = $this->applySorting($colors)->paginate(10);

        $allColors = Color::where('tenant_id', $tenantId)->get();

        return view('livewire.admin.colors.index', [
            'colors' => $colors,
            'allColors' => $allColors,
        ]);
    }
}
