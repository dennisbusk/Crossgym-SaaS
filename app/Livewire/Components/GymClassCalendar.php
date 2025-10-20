<?php

namespace App\Livewire\Components;

use App\Models\GymClass;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class GymClassCalendar extends Component
{

    #[On('loadEvents')]
    public function loadEvents($start = null, $end = null)
    {
        $start = Carbon::parse($start ?? now()->toDateString())->startOfMonth();
        $end = Carbon::parse($end ?? now()->toDateString())->endOfMonth();
        // Fetch events for this range
        $events = GymClass::query()
                          ->whereBetween('start_time', [$start, $end])
                          ->get()
                          ->map(fn($event) => [
                              'id' => $event->id,
                              'title' => $event->name,
                              'start' => $event->start_time->toIso8601String(),
                              'end' => $event->end_time->toIso8601String(),
                          ]);

        // Dispatch back to JS
        $this->dispatchBrowserEvent('events-updated', ['events' => $events]);
        return $events;
    }

    public function render()
    {
        $events = $this->loadEvents(now()->subMonth()->startOfMonth(),now()->addMonth()->endOfMonth());
        return view('livewire.components.gym-class-calendar')->with(['events' => $events]);
    }
}
