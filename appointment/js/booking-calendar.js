(function (Drupal, drupalSettings) {
  Drupal.behaviors.bookingCalendar = {

    attach(context, settings) {
      const el = context.querySelector('#booking-calendar');
      if (!el || el.dataset.calendarInit) return;
      el.dataset.calendarInit = true;

      const bookedSlots = settings.appointment.bookedSlots ?? [];
      const adviserAvailableTime =  Number(settings.appointment.availableTime ?? 0);
      const adviserStartTime =  settings.appointment.adviserStartTime;

      const startHH      = String(adviserStartTime).padStart(2, '0');
      const endHH        = String(adviserStartTime + adviserAvailableTime).padStart(2, '0');
      const startTimeStr = `${startHH}:00`;
      const endTimeStr   = `${endHH}:00`;

      console.group('Booking Calendar — Adviser Schedule');
      console.log('adviserStartHour (as Number)    :', adviserStartTime);
      console.log('adviserAvailableTime (as Number):', adviserAvailableTime);
      console.log('Derived startTimeStr            :', startTimeStr);
      console.log('Derived endTimeStr              :', endTimeStr);
      console.log('Type check — startHour is number:', typeof adviserStartTime === 'number');
      console.log('Type check — availTime is number:', typeof adviserAvailableTime === 'number');
      console.log('Booked slots                    :', bookedSlots);
      console.groupEnd();

      const bookedEvents = bookedSlots.map(slot => ({
        start: slot,
        end: new Date(new Date(slot).getTime() + 30 * 60000).toISOString(),
        display: 'background',
        color: '#F76245',
      }));


      const businessHours = {
        daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
        startTime: startTimeStr,
        endTime: endTimeStr,
      }

      console.log('businessHours object passed to FullCalendar:', businessHours);


      const calendar = new FullCalendar.Calendar(el, {
        initialView:  'timeGridWeek',
        slotMinTime:  '08:00:00',
        slotMaxTime:  '18:00:00',
        slotDuration: '00:30:00',

        businessHours,
        selectedConstraint: businessHours,
        eventConstraint: businessHours,

        events:       bookedEvents,

        selectable: true,

        // Single click on a time slot
        dateClick(info) {
          const fullDate = info.dateStr;
          const date     = fullDate.substring(0, 10);
          const hour     = fullDate.substring(11, 16);
          const datetime = `${date}T${hour}:00`;

          // Checking if already booked.
          if (bookedSlots.includes(datetime)) {
            alert(Drupal.t('This slot is already booked. Please choose another.'));
            return;
          }

          //checking is selected outside of business hours
          if(hour >= businessHours.endTime || hour < businessHours.startTime) {
            alert(Drupal.t('Selected time is outside of business hours. Please choose another.'));
            return;
          }

          //checking if it's a weekend
          const dayOfWeek = new Date(date).getDay();
          if (dayOfWeek === 0 || dayOfWeek === 6) {
            alert(Drupal.t('The adviser is not available on weekends.'));
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
