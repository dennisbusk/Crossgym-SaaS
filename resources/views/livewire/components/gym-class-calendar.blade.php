<div>
  <div id="calendar"
       x-data="calendarComponent()"
       x-init="initCalendar()"
  ></div>
</div>
@push('scripts')
  <script>
    function calendarComponent() {
      return {
        events: [],
        allEvents: [],
        initCalendar() {
          let calendarEl = document.getElementById('calendar');
          let calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [FullCalendar.dayGridPlugin, FullCalendar.timeGridPlugin, FullCalendar.listPlugin],
            locales:[FullCalendar.enLocale,FullCalendar.daLocale],
            events: this.events,
            initialView: 'dayGridMonth',
            navLinks: true,
            aspectRatio: (window.screen.width / window.screen.height)-.17,
            locale: '{{app()->getLocale() ?? 'da'}}',
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            datesSet: function(info) {
              console.log('View changed!');
              console.log('Current view type:', info.view.type);
              console.log('Start:', info.start);
              console.log('End:', info.end);
            }
          });
          calendar.render();
        },
        getEvents(){
        
        }
      }
    }
  </script>
@endpush
