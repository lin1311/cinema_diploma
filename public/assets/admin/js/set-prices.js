document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="prices-hall"]');
    // Первый input — стандарт, второй — VIP, ориентируемся на шаблон с name!
    const standartInput = document.querySelector('input[name="prices[standart]"]');
    const vipInput = document.querySelector('input[name="prices[vip]"]');
    const priceForm = document.querySelector('.hall-form');
    const pricesFromServer = window.pricesFromServer || {};

    // Черновики между переключениями
    let localPrices = {};

    // Стартовый зал
    let currentHallId = (document.querySelector('input[name="prices-hall"]:checked') || radios[0]).value;

    function updateForm(hallId) {
        let prices = localPrices[hallId] || pricesFromServer[hallId] || {};
        standartInput.value = prices.standart !== undefined && prices.standart !== null ? prices.standart : '';
        vipInput.value = prices.vip !== undefined && prices.vip !== null ? prices.vip : '';
        priceForm.action = priceForm.action.replace(/prices\/\d+/, 'prices/' + hallId);
    }

    function trackInput() {
        localPrices[currentHallId] = {
            standart: standartInput.value,
            vip: vipInput.value
        };
    }

    // Черновик при ручном вводе
    standartInput.addEventListener('input', trackInput);
    vipInput.addEventListener('input', trackInput);

    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            trackInput();
            currentHallId = radio.value;
            updateForm(currentHallId);
        });
    });

    // Первая подстановка
    updateForm(currentHallId);

    // AJAX submit
    priceForm.addEventListener('submit', function (e) {
        e.preventDefault();
        trackInput();
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
                delete localPrices[currentHallId];
            }
          })
          .catch(() => alert('Ошибка сети/сервера!'));
    });

    // Кнопка "Отмена" должна сбрасывать значение на последнее сохранённое
    const resetBtn = priceForm.querySelector('button[type="reset"], input[type="reset"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            delete localPrices[currentHallId];
            updateForm(currentHallId);
        });
    }
});
