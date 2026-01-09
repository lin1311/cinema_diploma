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

  const toDateKey = (date) => date.toISOString().split('T')[0];

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
  };

  const shiftStart = (days) => {
    startDate = new Date(startDate);
    startDate.setDate(startDate.getDate() + days);
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
        selectedDate = new Date(dateValue);
        selectedDate.setHours(0, 0, 0, 0);
        render();
      }
    }
  });

  render();
})();
