document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="prices-hall"]');
    const standartInput = document.querySelector('input[name="prices[standart]"]');
    const vipInput = document.querySelector('input[name="prices[vip]"]');
    const priceForm = document.querySelector('.hall-form');
    const pricesFromServer = window.pricesFromServer || {};

    if (!radios.length || !standartInput || !vipInput || !priceForm) {
        return;
    }
    let localPrices = {};

    // Стартовый зал
    let currentHallId = (document.querySelector('input[name="prices-hall"]:checked') || radios[0]).value;

    if (!priceForm.dataset.baseAction) {
        priceForm.dataset.baseAction = priceForm.action;
    }

    function normalizePrices(rawPrices) {
        if (rawPrices && typeof rawPrices === 'object') {
            return {
                standart: rawPrices.standart ?? '',
                vip: rawPrices.vip ?? ''
            };
        }
        return { standart: '', vip: '' };
    }

    function applyPrices(prices) {
        const standartValue = prices.standart ?? '';
        const vipValue = prices.vip ?? '';
        standartInput.value = standartValue;
        vipInput.value = vipValue;
        standartInput.dataset.base = standartValue;
        vipInput.dataset.base = vipValue;
    }

    function restoreForHall(hallId) {
        const prices = localPrices[hallId] || normalizePrices(pricesFromServer[hallId]);
        localPrices[hallId] = prices;
        applyPrices(prices);
        priceForm.action = priceForm.dataset.baseAction.replace(/prices\/\d+/, 'prices/' + hallId);
    }

    function updatePricesFromInputs() {
        localPrices[currentHallId] = {
            standart: standartInput.value,
            vip: vipInput.value
        };
    }

    standartInput.addEventListener('input', updatePricesFromInputs);
    vipInput.addEventListener('input', updatePricesFromInputs);

    function switchHall(hallId) {
        updatePricesFromInputs();
        currentHallId = hallId;
        restoreForHall(currentHallId);
    }

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            switchHall(radio.value);
        });
    });

    restoreForHall(currentHallId);

    priceForm.addEventListener('submit', function (e) {
        e.preventDefault();
        updatePricesFromInputs();
        let fd = new FormData(priceForm);
        fetch(priceForm.action, {
            method: 'POST',
            body: fd,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(res => res.json())
          .then(data => {
            alert(data.success ? 'Цены успешно сохранены!' : 'Ошибка сохранения');
            if (data.success) {
                pricesFromServer[currentHallId] = {
                    standart: standartInput.value,
                    vip: vipInput.value
                };
                localPrices[currentHallId] = normalizePrices(pricesFromServer[currentHallId]);
                restoreForHall(currentHallId);
            }
          })
          .catch(() => alert('Ошибка сети/сервера!'));
    });

    const resetBtn = priceForm.querySelector('button[type="reset"], input[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            localPrices[currentHallId] = normalizePrices(pricesFromServer[currentHallId]);
            restoreForHall(currentHallId);
        });
    }
});
