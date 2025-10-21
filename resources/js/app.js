
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import daLocale from '@fullcalendar/core/locales/da';
import enLocale from '@fullcalendar/core/locales/en-gb';

window.FullCalendar = { Calendar, dayGridPlugin, timeGridPlugin, listPlugin, daLocale,enLocale };
import registerAlpineGlobals from './alpine-globals.js'

document.addEventListener('alpine:init', () => {
  registerAlpineGlobals(window.Alpine);
})
