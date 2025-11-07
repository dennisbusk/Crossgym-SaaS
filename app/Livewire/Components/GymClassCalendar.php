<?php

namespace App\Livewire\Components;

use App\Models\GymClass;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class GymClassCalendar extends Component {

    #[On( 'loadEvents' )]
    public function loadEvents( $start = null, $end = null ): void {
        $start = Carbon::parse($start ?? now()->toDateString())->startOfMonth()->startOfWeek();
        $end   = Carbon::parse($end ?? now()->toDateString())->endOfMonth()->endOfWeek();
        // Fetch events for this range
        $events = $this->getEvents($start, $end);
        // Dispatch back to JS
        $this->dispatch('events-updated', events: $events);
    }

    public function render() {
        $start  = Carbon::parse(now()->toDateString())->startOfMonth()->startOfWeek();
        $end    = Carbon::parse(now()->toDateString())->endOfMonth()->endOfWeek();
        $events = $this->getEvents($start, $end);

        return view('livewire.components.gym-class-calendar')->with([ 'events' => $events ]);
    }

    public function getEvents($start, $end){
        return GymClass::query()->whereBetween('class_start', [ $start, $end ])->get()->map(fn( $event ) => [
            'id'            => $event->id,
            'title'         => $event->name,
            'start'         => $event->class_start->toIso8601String(),
            'end'           => $event->class_end->toIso8601String(),
            'color'         => $event->classType->color,
            'extendedProps' => [
                'trainer'         => $event->trainer->name,
                'maxParticipants' => $event->max_participants,
                'participants'    => $event->participants->map(fn( $participant ) => [
                    'id'    => $participant->id,
                    'name'  => $participant->name,
                    'checked_in_at' => $participant->checked_in_at != null
                ]),
            ],
        ]);
    }
}
