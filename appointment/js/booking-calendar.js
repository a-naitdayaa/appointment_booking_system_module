(function (Drupal, drupalSettings) {
  Drupal.behaviors.bookingCalendar = {
    attach(context, settings) {
      const el = context.querySelector('#booking-calendar');
      if (!el || el.dataset.calendarInit) return;
      el.dataset.calendarInit = true;

      const bookedSlots = settings.appointment.bookedSlots ?? [];

      const bookedEvents = bookedSlots.map(slot => ({
        start: slot,
        end: new Date(new Date(slot).getTime() + 30 * 60000).toISOString(),
        display: 'background',
        color: '#ffcccc',
      }));

      const calendar = new FullCalendar.Calendar(el, {
        initialView:  'timeGridWeek',
        slotMinTime:  '08:00:00',
        slotMaxTime:  '18:00:00',
        slotDuration: '00:30:00',
        events:       bookedEvents,

        selectable: true,

        // Single click on a time slot
        dateClick(info) {
          const fullDate = info.dateStr;
          const date     = fullDate.substring(0, 10);
          const hour     = fullDate.substring(11, 16);
          const datetime = `${date}T${hour}:00`;

          // Checkiing if already booked.
          if (bookedSlots.includes(datetime)) {
            alert(Drupal.t('This slot is already booked. Please choose another.'));
            return;
          }

          // Setting hidden field values.
          document.querySelector('.booking-date-value').value = date;
          document.querySelector('.booking-hour-value').value = hour;

          // Highlight selected slot visually.
          document.querySelectorAll('.fc-timegrid-slot-selected')
            .forEach(el => el.classList.remove('fc-timegrid-slot-selected'));
          info.dayEl.classList.add('fc-timegrid-slot-selected');

          // Show selected slot to user.
          document.getElementById('booking-selected-slot').textContent =
            Drupal.t('Selected: @date at @hour', { '@date': date, '@hour': hour });
        },
      });

      calendar.render();
    },
  };
})(Drupal, drupalSettings);
