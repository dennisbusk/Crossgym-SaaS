<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Achievements;

use App\Models\Achievement;
use Illuminate\Support\Str;
use Livewire\Component;

class AchievementForm extends Component
{
    public ?Achievement $achievement = null;

    public string $slug = '';

    public array $name = ['da' => '', 'en' => ''];

    public array $description = ['da' => '', 'en' => ''];

    public ?string $icon = 'star';

    public string $type = 'count';

    public ?string $category = null;

    public bool $hidden = false;

    public bool $repeatable = false;

    public int $points = 100;

    public string $rarity = 'common';

    public bool $is_active = true;

    // Rule data
    public string $event = 'user.checked_in';

    public string $operator = '>=';

    public string $target = '1';

    protected function rules(): array
    {
        return [
            'slug' => 'required|string|max:255',
            'name.da' => 'required|string|max:255',
            'type' => 'required|string',
            'points' => 'required|integer|min:0',
            'rarity' => 'required|string',
            'event' => 'required|string',
            'operator' => 'required|string',
            'target' => 'required|string',
        ];
    }

    public function mount(?Achievement $achievement = null): void
    {
        if ($achievement && $achievement->exists) {
            $this->achievement = $achievement;
            $this->slug = $achievement->slug;
            $this->name = $achievement->getTranslations('name');
            $this->description = $achievement->getTranslations('description');
            $this->icon = $achievement->icon;
            $this->type = $achievement->type;
            $this->category = $achievement->category;
            $this->hidden = $achievement->hidden;
            $this->repeatable = $achievement->repeatable;
            $this->points = $achievement->points;
            $this->rarity = $achievement->rarity;
            $this->is_active = $achievement->is_active;

            $rule = $achievement->rules()->first();
            if ($rule) {
                $this->event = $rule->event;
                $this->operator = $rule->operator;
                $this->target = $rule->target;
            }
        } else {
            $this->slug = '';
        }
    }

    public function updatedNameDa($value): void
    {
        if (! $this->achievement || ! $this->achievement->exists) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'type' => $this->type,
            'category' => $this->category,
            'hidden' => $this->hidden,
            'repeatable' => $this->repeatable,
            'points' => $this->points,
            'rarity' => $this->rarity,
            'is_active' => $this->is_active,
        ];

        if ($this->achievement && $this->achievement->exists) {
            $this->authorize('update', $this->achievement);
            $this->achievement->update($data);
            $message = __('Achievement updated successfully');
        } else {
            $this->authorize('create', Achievement::class);
            $this->achievement = Achievement::create($data);
            $message = __('Achievement created successfully');
        }

        // Save rule (simplification: only one rule for now)
        $this->achievement->rules()->updateOrCreate(
            ['achievement_id' => $this->achievement->id],
            [
                'event' => $this->event,
                'operator' => $this->operator,
                'target' => $this->target,
            ]
        );

        $this->dispatch('banner-message', message: $message, style: 'success');

        return redirect()->route('achievements.index');
    }

    public function render()
    {
        return view('livewire.admin.achievements.form');
    }
}
