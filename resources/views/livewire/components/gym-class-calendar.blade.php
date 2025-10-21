<div
    wire:ignore
    x-data="calendarComponent(@this)"
    x-init="initCalendar(@js($events))"
    @events-updated.window="updateEvents($event.detail.events)">
  <div id="calendar"></div>
    <!-- Modal -->
    <div x-show="open"
         style="background-color: rgba(0,0,0,0.5);"
         class="fixed inset-0 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-900 p-6 rounded shadow-lg w-xl">
        <h2 class="text-lg font-bold mb-2" x-text="chosenEvent?.title"></h2>
        <p><strong>Start:</strong> <span x-text="$formatDateTime(chosenEvent?.start)"></span></p>
        <p><strong>End:</strong> <span x-text="$formatDateTime(chosenEvent?.end)"></span></p>
        <p><strong>Trainer:</strong> <span x-text="chosenEvent?.extendedProps.trainer"></span></p>
        <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded" @click="open = false">Close</button>
      </div>
    </div>

</div>

@push('scripts')
  <script>
    function calendarComponent($wire) {
      return {
        events: [],
        chosenEvent: null,
        open: false,
        calendar: null,
        async initCalendar(events) {
          this.events = events;
          let calendarEl = document.getElementById('calendar');
          this.calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [
              FullCalendar.dayGridPlugin,
              FullCalendar.timeGridPlugin,
              FullCalendar.listPlugin
            ],
            locales:[FullCalendar.enLocale,FullCalendar.daLocale],
            events: this.events,
            initialView: 'dayGridMonth',
            navLinks: true,
            aspectRatio: (window.screen.width / window.screen.height) - .17,
            locale: '{{ app()->getLocale() ?? 'da' }}',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            datesSet: (info) => {
              // Ask Livewire to load events for this date range
              $wire.loadEvents( info.startStr, info.endStr);
            },
            eventClick: (info) => {
              console.log('Event clicked:', info.event);
              console.log(info.event.extendedProps)
              this.chosenEvent = info.event;
              this.open = true;
            }
          });
          this.calendar.render();
        },
        updateEvents(newEvents) {
          this.events = newEvents;
          this.calendar.removeAllEvents();
          this.calendar.addEventSource(this.events);
        }
      }
    }
  </script>
@endpush
