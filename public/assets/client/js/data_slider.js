(() => {
  const slider = document.querySelector('[data-role="date-slider"]');
  if (!slider) {
    return;
  }

  const dayItems = Array.from(slider.querySelectorAll('[data-role="date-day"]'));
  const nextButton = slider.querySelector('[data-role="date-next"]');
  const weekdayNames = ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'];

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  let startDate = new Date(today);
  let selectedDate = new Date(today);

  const toDateKey = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  const parseDateKey = (dateKey) => {
    const [year, month, day] = dateKey.split('-').map(Number);
    return new Date(year, month - 1, day);
  };

  const parseSeanceMinutes = (timeValue) => {
    const [hours, minutes] = String(timeValue || '').trim().split(':').map(Number);
    if (!Number.isInteger(hours) || !Number.isInteger(minutes)) {
      return null;
    }

    return hours * 60 + minutes;
  };

  const isPastSeanceForSelectedDate = (timeValue, date) => {
    if (toDateKey(date) !== toDateKey(today)) {
      return false;
    }

    const seanceMinutes = parseSeanceMinutes(timeValue);
    if (seanceMinutes === null) {
      return false;
    }

    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    return seanceMinutes < currentMinutes;
  };

  const updateSeanceLinks = () => {
    const dateKey = toDateKey(selectedDate);
    const seanceLinks = document.querySelectorAll('[data-role="seance-link"]');

    seanceLinks.forEach((link) => {
      const baseHref = link.dataset.baseHref || link.getAttribute('href');
      if (!baseHref) {
        return;
      }

      const url = new URL(baseHref, window.location.origin);
      url.searchParams.set('date', dateKey);
      link.setAttribute('href', `${url.pathname}${url.search}`);
      if (!link.dataset.baseHref) {
        link.dataset.baseHref = baseHref;
      }

      const seanceTime = link.textContent || '';
      const isPast = isPastSeanceForSelectedDate(seanceTime, selectedDate);

      link.classList.toggle('movie-seances__time_disabled', isPast);
      link.setAttribute('aria-disabled', isPast ? 'true' : 'false');
      if (isPast) {
        link.setAttribute('tabindex', '-1');
      } else {
        link.removeAttribute('tabindex');
      }
    });
  };

  const render = () => {
    dayItems.forEach((dayItem, index) => {
      const date = new Date(startDate);
      date.setDate(startDate.getDate() + index);

      const dayIndex = date.getDay();
      const isWeekend = dayIndex === 0 || dayIndex === 6;
      const isToday = date.getTime() === today.getTime();

      dayItem.dataset.date = toDateKey(date);
      dayItem.classList.toggle('page-nav__day_weekend', isWeekend);
      dayItem.classList.toggle('page-nav__day_today', isToday);

      const weekLabel = dayItem.querySelector('.page-nav__day-week');
      const numberLabel = dayItem.querySelector('.page-nav__day-number');

      if (weekLabel) {
        weekLabel.textContent = weekdayNames[dayIndex];
      }
      if (numberLabel) {
        numberLabel.textContent = date.getDate();
      }
    });

    const selectedKey = toDateKey(selectedDate);
    const hasSelected = dayItems.some((dayItem) => dayItem.dataset.date === selectedKey);

    if (!hasSelected) {
      selectedDate = new Date(startDate);
    }

    dayItems.forEach((dayItem) => {
      dayItem.classList.toggle('page-nav__day_chosen', dayItem.dataset.date === toDateKey(selectedDate));
    });

    updateSeanceLinks();
  };

  const shiftStart = (days) => {
    startDate = new Date(startDate);
    startDate.setDate(startDate.getDate() + days);
    if (startDate.getTime() < today.getTime()) {
      startDate = new Date(today);
    }
    render();
  };

  slider.addEventListener('click', (event) => {
    const link = event.target.closest('a');
    if (!link) {
      return;
    }

    if (link === nextButton) {
      event.preventDefault();
      shiftStart(1);
      return;
    }

    if (link.dataset.role === 'date-day') {
      event.preventDefault();
      const dateValue = link.dataset.date;
      if (dateValue) {
        const clickedDate = parseDateKey(dateValue);
        clickedDate.setHours(0, 0, 0, 0);

        if (today.getTime() < startDate.getTime() && clickedDate.getTime() < selectedDate.getTime()) {
          const diffDays = Math.round((selectedDate.getTime() - clickedDate.getTime()) / 86400000);
          if (diffDays > 0) {
            shiftStart(-diffDays);
          }
        }

        selectedDate = clickedDate;
        render();
      }
    }
  });

  document.addEventListener('click', (event) => {
    const seanceLink = event.target.closest('[data-role="seance-link"]');
    if (seanceLink && seanceLink.classList.contains('movie-seances__time_disabled')) {
      event.preventDefault();
    }
  });

  render();
})();
