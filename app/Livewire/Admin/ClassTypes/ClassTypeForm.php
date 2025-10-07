<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ClassTypes;

use App\Models\ClassType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ClassTypeForm extends Component
{
    use AuthorizesRequests;

    public ?ClassType $classType = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    public ?string $description = null;

    public ?string $slug = null;

    public ?string $color = null;

    public ?string $image = null;

    public function mount($classType = null): void
    {
        $this->classType = $classType instanceof ClassType ? $classType : new ClassType();
        if ($this->classType && $this->classType->exists) {
            $this->authorize('update', $this->classType);
            $this->name = (string)($this->classType->getTranslation('name', app()->getLocale()) ?? '');
            $this->description = (string)($this->classType->getTranslation('description', app()->getLocale()) ?? '');
            $this->slug = $this->classType->slug;
            $this->color = $this->classType->color;
            $this->image = $this->classType->image;
        } else {
            $this->authorize('create', ClassType::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'color' => $this->color,
            'image' => $this->image,
            'name' => [app()->getLocale() => $this->name],
            'description' => [app()->getLocale() => (string) $this->description],
        ];

        if ($this->classType && $this->classType->exists) {
            $this->classType->update($data);
            session()->flash('status', __('Class type updated.'));
        } else {
            $user = Auth::user();
            $data['tenant_id'] = $user?->tenant_id;
            $this->classType = ClassType::create($data);
            session()->flash('status', __('Class type created.'));
            return redirect()->route('class-types.edit', $this->classType);
        }
    }

    public function render()
    {
        return view('livewire.admin.class-types.form');
    }
}
