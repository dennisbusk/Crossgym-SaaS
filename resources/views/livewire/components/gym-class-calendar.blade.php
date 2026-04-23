<x-banners/>

<div>
    <div
        wire:ignore
        x-data="calendarComponent($wire, {{ Js::from($events) }})"
        x-init="initCalendar()"
        @events-updated.window="updateEvents($event.detail.events)">
        <div id="calendar"></div>
    <!-- Modal -->
    <div x-show="open && chosenEvent"
         style="background-color: rgba(0,0,0,0.5);display:none;"
         class="fixed inset-0 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-900 p-6 rounded shadow-lg w-xl relative">
        <!-- Add participant toggle (plus badge) -->
        <div class="absolute top-3 right-3" x-show="chosenEvent?.extendedProps.canManage">
          <button
            type="button"
            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700"
            :title="showAdd ? '{{ __('Hide add participant') }}' : '{{ __('Add participant') }}'"
            @click="showAdd = !showAdd">
            <span class="text-xl leading-none">+</span>
          </button>
        </div>
        <h2 class="text-lg font-bold mb-2" x-text="chosenEvent?.title"></h2>
        <p><strong>{{ __('Start') }}:</strong> <span x-text="$formatDateTime(chosenEvent?.start)"></span></p>
        <p><strong>{{ __('End') }}:</strong> <span x-text="$formatDateTime(chosenEvent?.end)"></span></p>
        <p><strong>{{ __('Trainer') }}:</strong> <span x-text="chosenEvent?.extendedProps.trainer"></span></p>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
          <span x-text="(chosenEvent?.extendedProps.participantsCount ?? 0) + ' / ' + (chosenEvent?.extendedProps.maxParticipants ?? 0)"></span>
          {{ __('participants') }}
        </p>

        <!-- Participants list (visible to trainer/admin). Everyone can see names if desired -->
        <div class="mt-4 space-y-2">
          <div class="flex items-center justify-between">
            <h3 class="text-md font-semibold">{{ __('Participants') }}</h3>
            <!-- Self check-in button for participant within 30 minutes before start -->
            <button
              x-show="chosenEvent?.extendedProps.userIsBooked && chosenEvent?.extendedProps.checkInWindowOpen"
              @click="checkIn()"
              type="button"
              class="px-3 py-1 bg-emerald-600 text-white rounded hover:bg-emerald-700 text-sm">
              {{ __('Check in') }}
            </button>
          </div>

          <template x-if="Array.isArray(chosenEvent?.extendedProps.participants) && chosenEvent?.extendedProps.participants.length">
            <ul class="divide-y divide-gray-200 dark:divide-gray-800 rounded border border-gray-200 dark:border-gray-800">
              <template x-for="p in chosenEvent.extendedProps.participants" :key="p.id">
                <li class="flex items-center justify-between px-3 py-2">
                  <div class="flex items-center gap-2">
                    <span class="text-sm" x-text="p.name"></span>
                    <span class="text-xs px-2 py-0.5 rounded"
                          :class="p.checkedIn ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                      <span x-text="p.checkedIn ? '{{ __('Checked in') }}' : '{{ __('Not checked in') }}'"></span>
                    </span>
                  </div>
                  <div class="flex items-center gap-2" x-show="chosenEvent?.extendedProps.canManage">
                    <button
                      class="text-xs px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700"
                      x-show="!p.checkedIn"
                      @click="$wire.checkInParticipant(chosenEvent.id, p.id); open = false;"
                      type="button">
                      {{ __('Check in') }}
                    </button>
                    <button
                      class="text-xs px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                      @click="$wire.removeParticipant(chosenEvent.id, p.id); open = false;"
                      type="button">
                      {{ __('Cancel') }}
                    </button>
                  </div>
                </li>
              </template>
            </ul>
          </template>

          <template x-if="!chosenEvent?.extendedProps.participants || chosenEvent?.extendedProps.participants.length === 0">
            <p class="text-sm text-gray-500">{{ __('No participants yet.') }}</p>
          </template>

          <!-- Add participant search panel (revealed by plus badge) -->
          <div class="mt-3 space-y-2" x-show="chosenEvent?.extendedProps.canManage && showAdd">
            <div class="flex items-center gap-2">
              <input
                type="text"
                x-model="searchQuery"
                @input.debounce.500ms="$wire.searchUsers(chosenEvent.id, searchQuery).then(r => { searchResults = r })"
                class="w-full px-3 py-2 rounded border border-gray-300 dark:bg-gray-800 dark:border-gray-700"
                :placeholder="'{{ __('Search by name or email') }}'" />
              <button type="button"
                      class="px-3 py-2 bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-700"
                      @click="searchQuery=''; searchResults=[]">
                {{ __('Clear') }}
              </button>
            </div>
            <div class="flex items-center gap-2">
              <select x-model.number="selectedUserId"
                      class="w-full px-3 py-2 rounded border border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                <option value="0">{{ __('Select a user') }}</option>
                <template x-for="u in searchResults" :key="u.id">
                  <option :value="u.id" x-text="u.name + ' <' + u.email + '>'"></option>
                </template>
              </select>
              <button type="button"
                      class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                      :disabled="!selectedUserId"
                      @click="$wire.addParticipantById(chosenEvent.id, selectedUserId).then(() => { selectedUserId = 0; searchQuery=''; searchResults=[]; showAdd=false; }); open = false;">
                {{ __('Add') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Subscription/plan limitation context -->
        <div class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
          <template x-if="(chosenEvent?.extendedProps.weeklyLimit ?? 0) > 0">
            <p>
              {{ __('Weekly bookings used') }}:
              <span x-text="(chosenEvent?.extendedProps.usedThisWeek ?? 0) + ' / ' + (chosenEvent?.extendedProps.weeklyLimit ?? 0)"></span>
            </p>
          </template>
          <template x-if="chosenEvent?.extendedProps.creditsRemaining !== null">
            <p>
              {{ __('Credits remaining') }}:
              <span x-text="chosenEvent?.extendedProps.creditsRemaining"></span>
            </p>
          </template>
        </div>

        <!-- If cannot book, show reason -->
        <template x-if="!chosenEvent?.extendedProps.userIsBooked && !(chosenEvent?.extendedProps.canBook ?? true) && (chosenEvent?.extendedProps.cannotBookReason)">
          <div class="mt-3 p-2 rounded bg-gray-100 dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-200" x-text="chosenEvent?.extendedProps.cannotBookReason"></div>
        </template>

        <template x-if="chosenEvent">
          <div class="mt-4 flex items-center gap-2 justify-end">
            <!-- Cancel booking button when already booked -->
            <button
              x-show="chosenEvent?.extendedProps.userIsBooked"
              @click="$wire.cancelBooking(chosenEvent.id); open = false;"
              type="button"
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
              {{ __('Cancel booking') }}
            </button>

            <!-- Book button when not booked and allowed -->
            <button
              x-show="!chosenEvent?.extendedProps.userIsBooked && (chosenEvent?.extendedProps.canBook ?? false)"
              @click="$wire.book(chosenEvent.id); open = false;"
              type="button"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
              {{ __('Book') }}
            </button>

            <!-- Disabled book button with reason when not allowed -->
            <button
              x-show="!chosenEvent?.extendedProps.userIsBooked && !(chosenEvent?.extendedProps.canBook ?? true)"
              type="button"
              class="px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed"
              disabled
              :title="chosenEvent?.extendedProps.cannotBookReason">
              {{ __('Book') }}
            </button>

            <button class="px-4 py-2 bg-blue-500 text-white rounded" @click="open = false">{{ __('Close') }}</button>
      </div>
    </template>
      </div>
    </div>
    </div>
</div>

@push('scripts')
  <script>
    function calendarComponent($wire, initialEvents) {
      return {
        events: initialEvents ?? [],
        chosenEvent: null,
        open: false,
        // Add participant UI state
        showAdd: false,
        searchQuery: '',
        searchResults: [],
        selectedUserId: 0,

        calendar: null,
        async initCalendar() {
          let calendarEl = document.getElementById('calendar');
          this.calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [
              FullCalendar.dayGridPlugin,
              FullCalendar.timeGridPlugin,
              FullCalendar.listPlugin
            ],
            locales:[FullCalendar.enLocale,FullCalendar.daLocale],
            events: this.events,
            eventDisplay: 'block',
            eventTextColor: 'black',
            initialView: window.innerWidth < 768 ? 'listWeek' : 'timeGridWeek',
            navLinks: true,
              height: 'calc(100vh - 135px)',
            // aspectRatio: (window.screen.width / window.screen.height) - .17,
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
              if (!info?.event?.id) return;
              this.chosenEvent = info.event;
              // reset add participant panel state on open
              this.showAdd = false;
              this.searchQuery = '';
              this.searchResults = [];
              this.selectedUserId = 0;
              this.open = true;
            },
            eventDidMount: function(info) {
              const color = info.event.backgroundColor || info.event.extendedProps.color;
              if (color) {
                info.el.style.setProperty('background-color', color, 'important');
                info.el.style.setProperty('border-color', color, 'important');
              }
              info.el.style.setProperty('color', 'black', 'important');
            }
          });
          this.calendar.render();
        },
        updateEvents(newEvents) {
          this.events = newEvents;
          this.calendar.removeAllEvents();
          this.calendar.addEventSource(this.events);
          // Try to update the chosen event with latest data
          if (this.chosenEvent) {
            const updated = this.events.find(e => e.id === this.chosenEvent.id);
            if (updated) {
              // FullCalendar event object update
              this.chosenEvent.setExtendedProp('participantsCount', updated.extendedProps.participantsCount);
              this.chosenEvent.setExtendedProp('availableSeats', updated.extendedProps.availableSeats);
              this.chosenEvent.setExtendedProp('userIsBooked', updated.extendedProps.userIsBooked);
              this.chosenEvent.setExtendedProp('canBook', updated.extendedProps.canBook);
              this.chosenEvent.setExtendedProp('cannotBookReason', updated.extendedProps.cannotBookReason);
              this.chosenEvent.setExtendedProp('weeklyLimit', updated.extendedProps.weeklyLimit);
              this.chosenEvent.setExtendedProp('usedThisWeek', updated.extendedProps.usedThisWeek);
              this.chosenEvent.setExtendedProp('creditsRemaining', updated.extendedProps.creditsRemaining);
              this.chosenEvent.setExtendedProp('participants', updated.extendedProps.participants ?? []);
              this.chosenEvent.setExtendedProp('canManage', updated.extendedProps.canManage ?? false);
              this.chosenEvent.setExtendedProp('checkInWindowOpen', updated.extendedProps.checkInWindowOpen ?? false);
              this.chosenEvent.setExtendedProp('hasStarted', updated.extendedProps.hasStarted ?? false);
            }
          }
        },
        async checkIn() {
          if (!this.chosenEvent) return;

          if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
              (position) => {
                $wire.selfCheckIn(
                  this.chosenEvent.id,
                  position.coords.latitude,
                  position.coords.longitude
                );
                this.open = false;
              },
              (error) => {
                // If user denies location or other error occurs, we still call the backend
                // The backend will handle the missing coordinates and show an error if required
                $wire.selfCheckIn(this.chosenEvent.id, null, null);
                this.open = false;
              },
              {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
              }
            );
          } else {
            // Geolocation not supported
            $wire.selfCheckIn(this.chosenEvent.id, null, null);
            this.open = false;
          }
        }
      }
    }
    document.addEventListener('livewire:navigated', () => {
      window.dispatchEvent(new Event('resize'));
    });
  </script>
@endpush
