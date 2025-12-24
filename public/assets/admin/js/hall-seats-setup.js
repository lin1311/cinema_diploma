
hall-seats-setup-fixed.js
document.addEventListener('DOMContentLoaded', function () {

    const rowsInput = document.getElementById('hall-rows-input');
    const seatsInput = document.getElementById('hall-seats-input');
    const wrapper = document.getElementById('hall-scheme-wrapper');
    const hallsRadios = document.querySelectorAll('input[name="chairs-hall"]');
    const saveBtn = document.getElementById('save-scheme-btn');

    if (!rowsInput || !seatsInput || !wrapper || !hallsRadios.length) {
        return;
    }

    // Инициализация схем
    let schemes = {};

    // Загружаем схемы с сервера
    const serverSchemes = window.hallSchemeFromServer || {};
    console.log('Server schemes:', serverSchemes);

    // Нормализуем данные с более строгой проверкой
    Object.keys(serverSchemes).forEach(hallId => {
        const scheme = serverSchemes[hallId];

        console.log(`Processing hall ${hallId}:`, scheme, 'Type:', typeof scheme, 'Is array:', Array.isArray(scheme));

        // Проверяем что это объект (не массив) и содержит нужные поля
        if (scheme && 
            typeof scheme === 'object' && 
            !Array.isArray(scheme) && 
            scheme.rows && 
            scheme.seats && 
            Array.isArray(scheme.seatsGrid) && 
            scheme.seatsGrid.length > 0) {

            schemes[hallId] = {
                rows: parseInt(scheme.rows, 10),
                seats: parseInt(scheme.seats, 10),
                seatsGrid: scheme.seatsGrid
            };
            console.log(`✓ Loaded valid scheme for hall ${hallId}`);
        } else {
            schemes[hallId] = { rows: 0, seats: 0, seatsGrid: [] };
            console.log(`✗ Empty scheme for hall ${hallId}`);
        }
    });

    let currentHallId = (document.querySelector('input[name="chairs-hall"]:checked') || hallsRadios[0]).value;

    // Убедимся что для всех залов есть схемы
    hallsRadios.forEach(radio => {
        const hallId = radio.value;
        if (!schemes[hallId]) {
            schemes[hallId] = { rows: 0, seats: 0, seatsGrid: [] };
        }
    });

    function renderScheme(seatsGrid) {
        const rows = parseInt(rowsInput.value, 10) || 0;
        const seats = parseInt(seatsInput.value, 10) || 0;

        wrapper.innerHTML = '';

        if (rows === 0 || seats === 0) {
            return;
        }

        for (let i = 0; i < rows; i++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'conf-step__row';

            for (let j = 0; j < seats; j++) {
                const chair = document.createElement('span');
                let type = 'standart';

                if (seatsGrid && seatsGrid[i] && seatsGrid[i][j]) {
                    type = seatsGrid[i][j];
                }

                chair.className = 'conf-step__chair conf-step__chair_' + type;
                chair.dataset.type = type;

                chair.addEventListener('click', function () {
                    const types = ['standart', 'vip', 'disabled'];
                    const currentType = chair.dataset.type;
                    const currentIndex = types.indexOf(currentType);
                    const newType = types[(currentIndex + 1) % types.length];

                    chair.dataset.type = newType;
                    chair.className = 'conf-step__chair conf-step__chair_' + newType;

                    // Обновляем схему
                    updateSchemeFromDOM();
                });

                rowDiv.appendChild(chair);
            }

            wrapper.appendChild(rowDiv);
        }

        console.log(`Rendered scheme for hall ${currentHallId}: ${rows}x${seats}`);
    }

    function updateSchemeFromDOM() {
        const rows = parseInt(rowsInput.value, 10) || 0;
        const seats = parseInt(seatsInput.value, 10) || 0;
        const seatsGrid = [];

        Array.from(wrapper.children).forEach(rowDiv => {
            const row = [];
            rowDiv.querySelectorAll('span.conf-step__chair').forEach(chair => {
                row.push(chair.dataset.type || 'standart');
            });
            seatsGrid.push(row);
        });

        schemes[currentHallId] = { rows, seats, seatsGrid };
    }

    function restoreForHall(hallId) {
        const scheme = schemes[hallId] || { rows: 0, seats: 0, seatsGrid: [] };
        console.log(`Restoring hall ${hallId}:`, scheme);

        if (scheme.rows > 0 && scheme.seats > 0) {
            rowsInput.value = scheme.rows;
            seatsInput.value = scheme.seats;
            renderScheme(scheme.seatsGrid);
        } else {
            rowsInput.value = '';
            seatsInput.value = '';
            wrapper.innerHTML = '';
        }
    }

    // События для инпутов
    rowsInput.addEventListener('input', function () {
        renderScheme(schemes[currentHallId]?.seatsGrid || []);
        updateSchemeFromDOM();
    });

    seatsInput.addEventListener('input', function () {
        renderScheme(schemes[currentHallId]?.seatsGrid || []);
        updateSchemeFromDOM();
    });

    // Переключение между залами
    hallsRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            console.log(`Switching from hall ${currentHallId} to ${radio.value}`);

            // Сохраняем текущую схему
            if (currentHallId) {
                updateSchemeFromDOM();
            }

            currentHallId = radio.value;

            // Убедимся что схема существует
            if (!schemes[currentHallId]) {
                schemes[currentHallId] = { rows: 0, seats: 0, seatsGrid: [] };
            }

            restoreForHall(currentHallId);
        });
    });

    // Сохранение
    if (saveBtn) {
        saveBtn.addEventListener('click', function (e) {
            e.preventDefault();

            updateSchemeFromDOM();
            const schemeToSave = schemes[currentHallId];

            if (!schemeToSave.rows || !schemeToSave.seats) {
                alert('Укажите размеры зала!');
                return;
            }

            console.log('Saving scheme:', schemeToSave);

            fetch('/admin/halls/' + currentHallId + '/scheme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(schemeToSave)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Сохранено!');
                } else {
                    alert('Ошибка сохранения');
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Ошибка запроса!');
            });
        });
    }

    // Инициализация
    restoreForHall(currentHallId);

    // Экспорт для popup-hall.js
    window.hallSchemesLocal = schemes;

});