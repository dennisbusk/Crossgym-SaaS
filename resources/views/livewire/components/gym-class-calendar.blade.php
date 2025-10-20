<div>
  <div id="calendar"
       x-data="calendarComponent()"
       x-init="initCalendar()"
       @events-updated.window="updateEvents($event.detail.events)"
  ></div>
</div>

@push('scripts')
  <script>
    function calendarComponent() {
      return {
        events: [],
        calendar: null,
        initCalendar() {
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
              console.log('View changed:', info.view.type, info.start, info.end);
              // Ask Livewire to load events for this date range
              Livewire.emit('loadEvents', info.startStr, info.endStr);
            },
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
