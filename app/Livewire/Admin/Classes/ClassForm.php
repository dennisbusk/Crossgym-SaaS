<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Classes;

use App\Models\ClassType;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class ClassForm extends Component
{
    use AuthorizesRequests;

    public ?GymClass $gymClass = null;

    #[Rule('required|string|max:255')]
    public string $name = '';

    public ?string $description = null;

    #[Rule('required|exists:users,id')]
    public ?int $trainer_id = null;

    #[Rule('required|exists:class_types,id')]
    public ?int $class_type_id = null;

    #[Rule('nullable|exists:colors,id')]
    public ?int $color_id = null;

    public ?int $max_participants = null;

    public ?string $class_start = null; // datetime-local string

    public ?string $class_end = null;

    // For creating a new color on the fly
    public bool $showNewColorForm = false;

    public string $newColorHex = '#488aff';

    public string $newColorName = '';

    public function mount($gymClass = null): void
    {
        $this->gymClass = $gymClass instanceof GymClass ? $gymClass : new GymClass;
        if ($this->gymClass && $this->gymClass->exists) {
            $this->authorize('update', $this->gymClass);
            $this->name = (string) ($this->gymClass->getTranslation('name', app()->getLocale()) ?? '');
            $this->description = (string) ($this->gymClass->getTranslation('description', app()->getLocale()) ?? '');
            $this->trainer_id = $this->gymClass->trainer_id;
            $this->class_type_id = $this->gymClass->class_type_id;
            $this->max_participants = $this->gymClass->max_participants;
            $this->class_start = optional($this->gymClass->class_start)->format('Y-m-d\TH:i');
            $this->class_end = optional($this->gymClass->class_end)->format('Y-m-d\TH:i');
            $this->color_id = $this->gymClass->color_id;
        } else {
            $this->authorize('create', GymClass::class);
            $this->trainer_id = Auth::id();
        }
    }

    public function createColor()
    {
        $this->validate([
            'newColorHex' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'newColorName' => 'required|string|max:255',
        ]);

        $color = \App\Models\Color::create([
            'tenant_id' => Auth::user()->tenant_id,
            'color' => $this->newColorHex,
            'name' => $this->newColorName,
        ]);

        $this->color_id = $color->id;
        $this->showNewColorForm = false;
        $this->newColorName = '';
    }

    public function getContrastColor(string $hexColor): string
    {
        // Remove # if present
        $hex = str_replace('#', '', $hexColor);

        // Convert hex to RGB
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        // Calculate relative luminance
        // (Formula from W3C)
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }

    public function save()
    {
        $this->validate();

        $data = [
            'trainer_id' => $this->trainer_id,
            'class_type_id' => $this->class_type_id,
            'max_participants' => $this->max_participants,
            'class_start' => $this->class_start ? date('Y-m-d H:i:s', strtotime($this->class_start)) : null,
            'class_end' => $this->class_end ? date('Y-m-d H:i:s', strtotime($this->class_end)) : null,
            'name' => [app()->getLocale() => $this->name],
            'description' => [app()->getLocale() => (string) $this->description],
            'color_id' => $this->color_id,
        ];

        if ($this->gymClass && $this->gymClass->exists) {
            $this->gymClass->update($data);
            session()->flash('status', __('Class updated.'));
        } else {
            $user = Auth::user();
            $data['tenant_id'] = $user?->tenant_id;
            $this->gymClass = GymClass::create($data);
            session()->flash('status', __('Class created.'));

            return redirect()->route('classes.edit', $this->gymClass);
        }
    }

    public function render()
    {
        return view('livewire.admin.classes.form', [
            'trainers' => User::query()->whereHas('role', fn ($q) => $q->where('name', 'Trainer'))->get(),
            'classTypes' => ClassType::query()->latest()->get(),
            'colors' => \App\Models\Color::query()->latest()->get(),
        ]);
    }
}
