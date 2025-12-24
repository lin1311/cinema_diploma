document.addEventListener('DOMContentLoaded', () => {
    const standartInput = document.querySelector('input[name="prices[standart]"]');
    const vipInput = document.querySelector('input[name="prices[vip]"]');
    const form = document.querySelector('.hall-form');
    const hallButtons = document.querySelectorAll('.hall-btn');

    if (!standartInput || !vipInput || !form || !hallButtons.length) return;

    const STORAGE_KEY = 'cinema_prices_drafts';
    let currentHallId = null;

    const read = () => JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    const write = (data) => localStorage.setItem(STORAGE_KEY, JSON.stringify(data));

    const loadPrices = (hallId) => {
        const data = read()[hallId] || {};
        standartInput.value = data.standart ?? '';
        vipInput.value = data.vip ?? '';
    };

    const savePrices = () => {
        if (!currentHallId) return;
        const data = read();
        data[currentHallId] = {
            standart: standartInput.value,
            vip: vipInput.value
        };
        write(data);
    };

    // сохраняем при вводе
    standartInput.addEventListener('input', savePrices);
    vipInput.addEventListener('input', savePrices);

    //  переключение залов
    hallButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            savePrices();
            currentHallId = btn.dataset.hallId;
            loadPrices(currentHallId);
        });
    });

    //  первый активный зал
    const firstHall = hallButtons[0];
    currentHallId = firstHall.dataset.hallId;
    loadPrices(currentHallId);

    const resetBtn = form.querySelector('[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            if (!currentHallId) return;
            const data = read();
            delete data[currentHallId];
            write(data);
            standartInput.value = '';
            vipInput.value = '';
        });
    }
});


