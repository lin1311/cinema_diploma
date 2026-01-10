(() => {
  const wrapper = document.querySelector('.buying-scheme__wrapper');
  if (!wrapper) {
    return;
  }

  const selectedCountEl = document.querySelector('[data-role="selected-count"]');
  const reserveButton = document.querySelector('[data-role="reserve-button"]');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const reserveUrl = wrapper.dataset.seatReserveUrl;

  const updateSelectedCount = () => {
    if (!selectedCountEl) {
      return;
    }

    const selectedSeats = wrapper.querySelectorAll('.buying-scheme__chair_selected');
    const count = selectedSeats.length;
    selectedCountEl.textContent = count > 0 ? `(${count})` : '';
  };

  const toggleSeatState = async (seatEl) => {
    const status = seatEl.dataset.seatStatus;
    if (status === 'taken') {
      return;
    }

    if (!reserveUrl) {
      return;
    }

    const row = Number(seatEl.dataset.seatRow);
    const seat = Number(seatEl.dataset.seatNumber);
    if (!row || !seat) {
      return;
    }

    const response = await fetch(reserveUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ row, seat, action: 'toggle' }),
    });

    if (!response.ok) {
      return;
    }

    const data = await response.json();
    const selected = Boolean(data.selected);

    seatEl.dataset.seatStatus = selected ? 'selected' : 'available';
    seatEl.classList.toggle('buying-scheme__chair_selected', selected);
    updateSelectedCount();
  };

  wrapper.addEventListener('click', (event) => {
    const seatEl = event.target.closest('.buying-scheme__chair');
    if (!seatEl || seatEl.classList.contains('buying-scheme__chair_disabled')) {
      return;
    }

    toggleSeatState(seatEl);
  });

  if (reserveButton) {
    reserveButton.addEventListener('click', async () => {
      if (!reserveUrl) {
        return;
      }

      const selectedSeats = wrapper.querySelectorAll('.buying-scheme__chair_selected');
      if (selectedSeats.length === 0) {
        window.alert('Сначала выберите место');
        return;
      }

      const response = await fetch(reserveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ action: 'reserve' }),
      });

      if (!response.ok) {
        return;
      }

      const data = await response.json();
      if (data.redirect) {
        window.location.href = data.redirect;
      }
    });
  }

  updateSelectedCount();
})();
