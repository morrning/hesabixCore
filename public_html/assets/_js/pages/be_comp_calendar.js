/*
 *  Document   : be_comp_calendar.js
 *  Author     : pixelcave
 *  Description: Custom JS code used in Calendar Page
 */

import faLocale from '../../js/plugins/fullcalendar/locales/fa';


// Full Calendar, for more examples you can check out http://fullcalendar.io/
class pageCompCalendar {
  /*
   * Add event to the events list
   *
   */
  static addEvent() {
    let eventInput = document.querySelector('.js-add-event');
    let eventInputVal = '';

    // When the add event form is submitted
    document.querySelector('.js-form-add-event').addEventListener('submit', e => {
      e.preventDefault();

      // Get input value
      eventInputVal = eventInput.value;

      // Check if the user entered something
      if (eventInputVal) {
        let eventList = document.getElementById('js-events');
        let newEvent = document.createElement('li');
        let newEventDiv = document.createElement('div');

        // Prepare new event div
        newEventDiv.classList.add('js-event');
        newEventDiv.classList.add('p-2');
        newEventDiv.classList.add('fs-sm');
        newEventDiv.classList.add('fw-medium');
        newEventDiv.classList.add('rounded');
        newEventDiv.classList.add('bg-info-light');
        newEventDiv.classList.add('text-info');
        newEventDiv.textContent = eventInputVal;
        
        // Prepare new event li
        newEvent.appendChild(newEventDiv);

        // Add it to the events list
        eventList.insertBefore(newEvent, eventList.firstChild);

        // Clear input field
        eventInput.value = '';
      }
    });
  }

  /*
   * Init drag and drop event functionality
   *
   */
  static initEvents() {
    new FullCalendar.Draggable(document.getElementById('js-events'), {
      itemSelector: '.js-event',
      eventData: function (eventEl) {
        return {
          title: eventEl.textContent,
          backgroundColor: getComputedStyle(eventEl).color,
          borderColor: getComputedStyle(eventEl).color
        };
      }
    });
  }

  /*
   * Init calendar demo functionality
   *
   */
  static initCalendar() {
    let date = new Date();
    let d = date.getDate();
    let m = date.getMonth();
    let y = date.getFullYear();

    let calendar = new FullCalendar.Calendar(document.getElementById('js-calendar'), {
      themeSystem: 'standard',
      firstDay: 6,
      editable: true,
      locales: [ faLocale ],
      locale: 'fa',
      selectable: true,
      selectMirror: true,
      dayMaxEvents: true,
      dir: 'rtl',
      droppable: true,
      headerToolbar: {
        left: 'prev,next today dayGridMonth,timeGridWeek,timeGridDay,listWeek',
        right: 'title'
      },
      drop: function (info) {
        info.draggedEl.parentNode.remove();
      },
      select: function(arg) {
        var title = prompt('عنوان رخداد:');
        if (title) {
          const colors = ["#e04f1a", "#82b54b", "#ffb119", "#82b54b", "#ffb119", "#3c90df"];
          const color =  Math.floor(Math.random() * colors.length)
          console.log(color);
          calendar.addEvent({
            title: title,
            start: arg.start,
            end: arg.end,
            allDay: arg.allDay,
            color: colors[color]
          })
        }
        calendar.unselect()
      },
      eventClick: function(arg) {
        if (confirm('آیا شما مطمئن هستید که میخواهید این رویداد را حذف کنید؟')) {
          arg.event.remove()
        }
      },
      events: [
        {
          title: 'روز بازی',
          start: new Date(y, m, 1),
          allDay: true
        },
        {
          title: 'ملاقات اسکایپی',
          start: new Date(y, m, 3)
        },
        {
          title: 'پروژه 1',
          start: new Date(y, m, 9),
          end: new Date(y, m, 12),
          allDay: true,
          color: '#e04f1a'
        },
        {
          title: 'کار کردن',
          start: new Date(y, m, 17),
          end: new Date(y, m, 19),
          allDay: true,
          color: '#82b54b'
        },
        {
          id: 999,
          title: 'پیاده روی (تکرار)',
          start: new Date(y, m, d - 1, 15, 0)
        },
        {
          id: 999,
          title: 'پیاده روی (تکرار)',
          start: new Date(y, m, d + 3, 15, 0)
        },
        {
          title: 'قالب لندینگ',
          start: new Date(y, m, d - 3),
          end: new Date(y, m, d - 3),
          allDay: true,
          color: '#ffb119'
        },
        {
          title: 'ناهار',
          start: new Date(y, m, d + 7, 15, 0),
          color: '#82b54b'
        },
        {
          title: 'کدنویسی',
          start: new Date(y, m, d, 8, 0),
          end: new Date(y, m, d, 14, 0),
        },
        {
          title: 'سفر کردن',
          start: new Date(y, m, 25),
          end: new Date(y, m, 27),
          allDay: true,
          color: '#ffb119'
        },
        {
          title: 'مطالعه',
          start: new Date(y, m, d + 8, 20, 0),
          end: new Date(y, m, d + 8, 22, 0)
        },
        {
          title: 'دنبال کردن در توییتر',
          start: new Date(y, m, 22),
          allDay: true,
          url: 'http://twitter.com/',
          color: '#3c90df'
        }
      ]
    });
    calendar.render();
  }

  /*
   * Init functionality
   *
   */
  static init() {
    this.addEvent();
    this.initEvents();
    this.initCalendar();
  }
}

// Initialize when page loads
Dashmix.onLoad(() => pageCompCalendar.init());
