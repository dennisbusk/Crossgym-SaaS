<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ClassTypes;

use App\Models\ClassType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Str;

class ClassTypeForm extends Component
{
    use AuthorizesRequests;

    protected $listeners = ['imageUpdated'];

    public ?ClassType $classType = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    public ?string $description = null;

    public ?string $slug = null;

    public ?string $image = null;

    public function mount($classType = null): void
    {
        $this->classType = $classType instanceof ClassType ? $classType : new ClassType;
        if ($this->classType && $this->classType->exists) {
            $this->authorize('update', $this->classType);
            $this->name = (string) ($this->classType->getTranslation('name', app()->getLocale()) ?? '');
            $this->description = (string) ($this->classType->getTranslation('description', app()->getLocale()) ?? '');
            $this->slug = $this->classType->slug;
            $this->image = $this->classType->image;
        } else {
            $this->authorize('create', ClassType::class);
        }
    }

    public function imageUpdated($url)
    {
        $this->image = $url;
        if ($this->classType && $this->classType->exists) {
            $this->classType->update(['image' => $url]);
        }
    }

    public function save()
    {
        $this->validate();
        if (! $this->classType->exists) {
            $this->slug = STR::slug($this->name);
        }

        $data = [
            'slug' => $this->slug,
            'image' => $this->image,
            'name' => [app()->getLocale() => $this->name],
            'description' => [app()->getLocale() => (string) $this->description],
        ];

        if ($this->classType && $this->classType->exists) {
            $this->saveImage();
            $this->classType->update($data);
            session()->flash('status', __('Class type updated.'));
        } else {
            $user = Auth::user();
            $data['tenant_id'] = $user?->tenant_id;
            $this->classType = ClassType::create($data);
            $this->saveImage();
            session()->flash('status', __('Class type created.'));

            return redirect()->route('class-types.edit', $this->classType);
        }
    }

    public function saveImage()
    {
        $this->emitTo('image-uploader', 'save', 'class-types/'.$this->classType->id);
    }

    public function render()
    {
        return view('livewire.admin.class-types.form');
    }
}
