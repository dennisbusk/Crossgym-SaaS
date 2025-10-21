import moment from "moment/min/moment-with-locales.js";

export default function registerAlpineGlobals(Alpine) {

  const formatDate = (dateString, format = 'DD/MM/YYYY',locale = 'da') => {
      return moment(dateString).locale(locale).format(format);
  };
  const formatDateTime = (dateString, format = 'DD/MM/YYYY HH:mm',locale = 'da') => {
      return moment(dateString).locale(locale).format(format);
  };
  const formatDateString = (dateString, format = 'D. MMMM YYYY', locale = 'da') => {
    return moment(dateString).locale(locale).format(format);
    };
  const capitalize = (str) => {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();

  };
  const formatCurrency = (amount) => {
    return amount.toLocaleString('da-DK', {
      style: 'currency',
      currency: 'DKK',
      minimumFractionDigits:0,
      maximumFractionDigits: 2
    });
  };
  const monthsBetweenDates = (fromDate, toDate,locale = 'default') => {
    const months = [];
    const start = new Date(fromDate);
    const end = new Date(toDate);

    // Normalize dates to first day of their respective months
    start.setDate(1);
    end.setDate(1);

    // Create current date pointer
    const current = new Date(start);

    // Loop through months
    while (current <= end) {
      months.push({
        date: new Date(current),
        label: new Intl.DateTimeFormat(locale, {
          month: 'long',
          year: 'numeric'
        }).format(current)
      });
      current.setMonth(current.getMonth() + 1);
    }
    return months;
    };

Alpine.magic('formatDate',() => formatDate);
window.formatDate = formatDate;

Alpine.magic('formatDateTime',() => formatDateTime);
window.formatDateTime = formatDateTime;

Alpine.magic('formatDateString',() => formatDateString);
window.formatDateString = formatDateString;

Alpine.magic('capitalize',() => capitalize);
window.capitalize = capitalize;

Alpine.magic('formatCurrency',() => formatCurrency);
window.formatCurrency = formatCurrency;

Alpine.magic('monthsBetweenDates',() => monthsBetweenDates);
window.monthsBetweenDates = monthsBetweenDates;

}
