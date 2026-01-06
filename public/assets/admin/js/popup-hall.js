document.addEventListener('DOMContentLoaded', function() {
    // ======== Попап создания зала ========
    const openBtn = document.getElementById('create-hall-btn');
    const addHallPopup = document.getElementById('add-hall-popup');

    if (openBtn && addHallPopup) {
        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addHallPopup.classList.add('active');
        });

        const closeBtn = addHallPopup.querySelector('.popup__dismiss');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addHallPopup.classList.remove('active');
            });
        }

        const cancelBtn = addHallPopup.querySelector('.conf-step__button-regular[type="button"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addHallPopup.classList.remove('active');
            });
        }
    }

    const createForm = addHallPopup ? addHallPopup.querySelector('form') : null;

    // ======== Попап удаления зала ========
    const deletePopup = document.getElementById('delete-hall-popup');
    const deleteForm = document.getElementById('delete-hall-form');
    const deleteNameSpan = document.getElementById('delete-hall-name');
    const cancelDeleteBtn = document.getElementById('cancel-delete');
    const closeDeleteBtn = document.getElementById('close-delete-popup');

    function closeDeletePopup(e) {
        if (e) e.preventDefault();
        if (!deletePopup || !deleteForm) return;

        deletePopup.style.display = 'none';
        deletePopup.classList.remove('active');
        deleteForm.dataset.hallId = '';
        deleteForm.dataset.url = '';
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', closeDeletePopup);
    }
    if (closeDeleteBtn) {
        closeDeleteBtn.addEventListener('click', closeDeletePopup);
    }

    // ======== Глобальные данные от Blade ========
    window.hallSchemeFromServer = window.hallSchemeFromServer || {};
    window.pricesFromServer = window.pricesFromServer || {};

    let priceDrafts = {};

    function readPriceDrafts() {
        return priceDrafts;
    }

    function writePriceDrafts(data) {
        priceDrafts = data;
    }

    function getDraftForHall(hallId) {
        const drafts = readPriceDrafts();
        return drafts[hallId];
    }

    function setDraftForHall(hallId, draft) {
        const drafts = readPriceDrafts();
        drafts[hallId] = draft;
        writePriceDrafts(drafts);
    }

    function clearDraftForHall(hallId) {
        const drafts = readPriceDrafts();
        delete drafts[hallId];
        writePriceDrafts(drafts);
    }

    function persistCurrentPricesDraft() {
        const form = document.querySelector('.prices-form-block form.hall-form');
        if (!form) return;
        const hallId = form.dataset.hallId;
        if (!hallId) return;
        const inputStd = form.querySelector('input[name="prices[standart]"]');
        const inputVip = form.querySelector('input[name="prices[vip]"]');
        setDraftForHall(hallId, {
            standart: inputStd ? inputStd.value : '',
            vip: inputVip ? inputVip.value : ''
        });
    }


    // ======== Хелперы по состоянию «есть/нет залов» ========
    function hasAnyHalls() {
        return !!document.querySelector('.conf-step__list li[data-hall-id]');
    }

    function updateEmptyStateTopList() {
        const hallsList = document.querySelector('.conf-step__list');
        if (!hallsList) return;

        const emptyLi = hallsList.querySelector('#no-halls-message');
        if (!hasAnyHalls()) {
            if (!emptyLi) {
                hallsList.insertAdjacentHTML(
                    'beforeend',
                    '<li id="no-halls-message">Нет данных о залах. Создайте новый</li>'
                );
            }
        } else if (emptyLi) {
            emptyLi.remove();
        }
    }

    function updateEmptyStateSelectors() {
        // селектор схем
        const chairsBox = document.querySelector('.conf-step__selectors-box[data-type="chairs"]');
        if (chairsBox) {
            const hasRadios = !!chairsBox.querySelector('input[name="chairs-hall"]');
            const emptyMsg = chairsBox.querySelector('.no-halls-message');
            if (!hasRadios) {
                if (!emptyMsg) {
                    chairsBox.insertAdjacentHTML(
                        'beforeend',
                        '<li class="no-halls-message">Нет залов для настройки схемы.</li>'
                    );
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }
        }

        // селектор цен
        const pricesBox = document.querySelector('.conf-step__selectors-box[data-type="prices"]');
        if (pricesBox) {
            const hasRadios = !!pricesBox.querySelector('input[name="prices-hall"]');
            const emptyMsg = pricesBox.querySelector('.no-halls-message');
            if (!hasRadios) {
                if (!emptyMsg) {
                    pricesBox.insertAdjacentHTML(
                        'beforeend',
                        '<li class="no-halls-message">Нет залов для настройки цен.</li>'
                    );
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }
        }
    }

    function showPricesFormBlockForHall(hallId) {
        const block = document.querySelector('.prices-form-block');
        if (!block) return;

        const form = block.querySelector('form.hall-form');
        if (!form) return;

        block.style.display = '';
        form.style.display = '';
        form.dataset.hallId = hallId;
        form.action = '/admin/prices/' + hallId;

        const draftPrices = getDraftForHall(hallId);
        const normalizedDraft = (draftPrices && typeof draftPrices === 'object') ? draftPrices : null;

        const serverPrices = (window.pricesFromServer && window.pricesFromServer[hallId])
            ? window.pricesFromServer[hallId]
            : {};

        const prices = normalizedDraft || serverPrices || {};

        const inputStd = form.querySelector('input[name="prices[standart]"]');
        const inputVip = form.querySelector('input[name="prices[vip]"]');

        if (inputStd) {
            inputStd.value = prices.standart != null ? prices.standart : '';
            inputStd.dataset.base = serverPrices.standart != null ? serverPrices.standart : '';
        }
        if (inputVip) {
            inputVip.value = prices.vip != null ? prices.vip : '';
            inputVip.dataset.base = serverPrices.vip != null ? serverPrices.vip : '';
        }
    }


    function hidePricesFormBlockIfNoHalls() {
        const block = document.querySelector('.prices-form-block');
        if (!block) return;

        if (!hasAnyHalls()) {
            block.style.display = 'none';
        }
    }

    function showHallSchemeBlockIfNeeded() {
        const block = document.querySelector('.hall-scheme-block');
        if (!block) return;

        if (hasAnyHalls()) {
            block.style.display = '';
        }
    }

    function hideHallSchemeBlockIfNoHalls() {
        const block = document.querySelector('.hall-scheme-block');
        if (!block) return;

        if (!hasAnyHalls()) {
            block.style.display = 'none';
        }
    }

    // ======== Работа со схемой зала ========
    function onSchemeHallChanged(hallId) {
        const block = document.querySelector('.hall-scheme-block');
        if (block) {
            block.style.display = '';
        }

        window.currentSchemeHallId = hallId;

        const wrapper = document.getElementById('hall-scheme-wrapper');
        if (!wrapper) return;

        const schemeData = (window.hallSchemesLocal && window.hallSchemesLocal[hallId])
            ? window.hallSchemesLocal[hallId]
            : (window.hallSchemeFromServer && window.hallSchemeFromServer[hallId])
                ? window.hallSchemeFromServer[hallId]
                : null;

        if (schemeData && typeof window.renderHallScheme === 'function') {
            window.renderHallScheme(schemeData, hallId);
        } else if (typeof window.renderHallScheme === 'function') {
            window.renderHallScheme(null, hallId);
        } else {
            wrapper.innerHTML = '';
        }
    }

    // ======== Добавить зал во ВСЕ блоки ========
    function addHallToUI(hall) {
        const hallId = hall.id;
        const hallName = hall.name;
        const destroyUrl = hall.destroy_url;

        if (!window.hallSchemeFromServer) window.hallSchemeFromServer = {};
        if (!window.hallSchemesLocal) window.hallSchemesLocal = window.hallSchemeFromServer;

        if (!window.hallSchemesLocal[hallId]) {
            window.hallSchemesLocal[hallId] = { rows: 0, seats: 0, seatsGrid: [] };
        }



        // инициализация пустых данных для нового зала
        if (!window.hallSchemeFromServer) window.hallSchemeFromServer = {};
        if (!window.pricesFromServer) window.pricesFromServer = {};
        if (!window.hallSchemeFromServer[hallId]) {
            window.hallSchemeFromServer[hallId] = null;
        }
        if (!window.pricesFromServer[hallId]) {
            window.pricesFromServer[hallId] = { standart: null, vip: null };
        }

        // 1) Верхний список «Доступные залы»
        const hallsList = document.querySelector('.conf-step__list');
        if (hallsList) {
            const noHallsMsg = document.getElementById('no-halls-message');
            if (noHallsMsg) noHallsMsg.remove();

            hallsList.insertAdjacentHTML(
                'beforeend',
                `
                <li data-hall-id="${hallId}">
                    ${hallName}
                    <form action="${destroyUrl}" method="POST" style="display:inline;">
                        <button
                            type="button"
                            class="conf-step__button conf-step__button-trash"
                            data-hall-id="${hallId}"
                            data-hall-name="${hallName}"
                            data-destroy-url="${destroyUrl}">
                        </button>
                    </form>
                </li>
                `
            );
        }

        // 2) Селектор залов для схемы
        const chairsBox = document.querySelector('.conf-step__selectors-box[data-type="chairs"]');
        if (chairsBox) {
            const emptyMsg = chairsBox.querySelector('.no-halls-message');
            if (emptyMsg) emptyMsg.remove();

            chairsBox.insertAdjacentHTML(
                'beforeend',
                `
                <li>
                    <input type="radio"
                           class="conf-step__radio"
                           name="chairs-hall"
                           value="${hallId}">
                    <span class="conf-step__selector">${hallName}</span>
                </li>
                `
            );

            const allRadios = chairsBox.querySelectorAll('input[name="chairs-hall"]');
            if (allRadios.length === 1) {
                allRadios[0].checked = true;
                allRadios[0].dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // 3) Селектор залов для цен
        const pricesBox = document.querySelector('.conf-step__selectors-box[data-type="prices"]');
        if (pricesBox) {
            const emptyMsg = pricesBox.querySelector('.no-halls-message');
            if (emptyMsg) emptyMsg.remove();

            pricesBox.insertAdjacentHTML(
                'beforeend',
                `
                <li>
                    <input type="radio"
                           class="conf-step__radio"
                           name="prices-hall"
                           value="${hallId}">
                    <span class="conf-step__selector">${hallName}</span>
                </li>
                `
            );

            const allRadios = pricesBox.querySelectorAll('input[name="prices-hall"]');
            if (allRadios.length === 1) {
                allRadios[0].checked = true;
                showPricesFormBlockForHall(hallId);
            }
        }

        // 4) Блок сеансов (если используется)
        const seancesContainer = document.querySelector('.conf-step__seances');
        if (seancesContainer) {
            seancesContainer.insertAdjacentHTML(
                'beforeend',
                `
                <div class="conf-step__seances-hall" data-hall="${hallId}">
                    <h3 class="conf-step__seances-title">${hallName}</h3>
                    <div class="conf-step__seances-timeline"></div>
                </div>
                `
            );
            const seancesWrapper = seancesContainer.closest('.conf-step__wrapper');
            const buttons = seancesWrapper?.querySelector('.conf-step__buttons');
            if (buttons) {
                seancesWrapper.appendChild(buttons);
            }
        }

        updateEmptyStateTopList();
        updateEmptyStateSelectors();
        showHallSchemeBlockIfNeeded();
        persistCurrentPricesDraft();
        const selectedPricesHall = document.querySelector('input[name="prices-hall"]:checked');
        if (selectedPricesHall) {
            showPricesFormBlockForHall(selectedPricesHall.value);
        }
        bindDeleteButtons();
    }

    // ======== Удалить зал из всех блоков ========
        function removeHallFromUI(hallId) {
            if (window.hallSchemesLocal && window.hallSchemesLocal[hallId]) {
                delete window.hallSchemesLocal[hallId];
            }
            if (window.hallSchemeFromServer && window.hallSchemeFromServer[hallId]) {
                delete window.hallSchemeFromServer[hallId];
            }
            if (window.pricesFromServer && window.pricesFromServer[hallId]) {
                delete window.pricesFromServer[hallId];
            }

            // 1) Верхний список
            let mainLi = document.querySelector(`.conf-step__list li[data-hall-id="${hallId}"]`);
            if (!mainLi) {
                const btn = document.querySelector(
                    `.conf-step__list .conf-step__button-trash[data-hall-id="${hallId}"]`
                );
                if (btn) mainLi = btn.closest('li');
            }
            if (mainLi) mainLi.remove();

            // 2) Селектор схем
            const chairsBox = document.querySelector('.conf-step__selectors-box[data-type="chairs"]');
            if (chairsBox) {
                const radio = chairsBox.querySelector(`input[name="chairs-hall"][value="${hallId}"]`);
                if (radio) {
                    const li = radio.closest('li');
                    if (li) li.remove();
                }
                const anyChecked = chairsBox.querySelector('input[name="chairs-hall"]:checked');
                if (!anyChecked) {
                    const firstRadio = chairsBox.querySelector('input[name="chairs-hall"]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        firstRadio.dispatchEvent(new Event('change', { bubbles: true }));
                    } else {
                        const wrapper = document.getElementById('hall-scheme-wrapper');
                        if (wrapper) wrapper.innerHTML = '';
                    }
                }
            }

            // 3) Селектор цен
            const pricesBox = document.querySelector('.conf-step__selectors-box[data-type="prices"]');
            if (pricesBox) {
                const radio = pricesBox.querySelector(`input[name="prices-hall"][value="${hallId}"]`);
                if (radio) {
                    const li = radio.closest('li');
                    if (li) li.remove();
                }
                const anyChecked = pricesBox.querySelector('input[name="prices-hall"]:checked');
                if (!anyChecked) {
                    const firstRadio = pricesBox.querySelector('input[name="prices-hall"]');
                    if (firstRadio) {
                        firstRadio.checked = true;
                        showPricesFormBlockForHall(firstRadio.value);
                    }
                }
            }

            // 4) Сеансы
            const seancesHall = document.querySelector(`.conf-step__seances-hall[data-hall="${hallId}"]`);
            if (seancesHall) seancesHall.remove();

            updateEmptyStateTopList();
            updateEmptyStateSelectors();
            hideHallSchemeBlockIfNoHalls();
            hidePricesFormBlockIfNoHalls();
        }

        // ======== Привязка кнопок удаления ========
        function bindDeleteButtons() {
            if (!deletePopup || !deleteForm || !deleteNameSpan) return;

            document.querySelectorAll('.conf-step__button-trash').forEach(function(btn) {
                btn.onclick = function(e) {
                    e.preventDefault();

                    const hallName = btn.getAttribute('data-hall-name');
                    const hallId = btn.getAttribute('data-hall-id');

                    let action = btn.dataset.destroyUrl;
                    if (!action) {
                        const form = btn.closest('form');
                        if (form) action = form.action;
                    }

                    if (!action) {
                        console.error('Не удалось определить URL удаления для зала', hallId);
                        return;
                    }

                    deleteNameSpan.textContent = hallName;
                    deleteForm.dataset.hallId = hallId;
                    deleteForm.dataset.url = action;

                    deletePopup.style.display = 'block';
                    deletePopup.classList.add('active');
                };
            });
        }

        // ======== AJAX-удаление ========
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const hallId = deleteForm.dataset.hallId;
                const url = deleteForm.dataset.url;

                if (!hallId || !url) {
                    console.error('Не заданы hallId или url для удаления');
                    return;
                }

                const csrf = deleteForm.querySelector('input[name="_token"]').value;
                const formData = new FormData();
                formData.append('_method', 'DELETE');

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: formData
                })
                    .then(resp => resp.json())
                    .then(data => {
                        if (data.success) {
                            removeHallFromUI(hallId);
                            closeDeletePopup();
                        } else {
                            alert('Не удалось удалить зал.');
                        }
                    })
                    .catch(() => {
                        alert('Ошибка при удалении зала (AJAX).');
                    });
            });
        }

        // ======== AJAX-создание ========
        if (createForm) {
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(createForm);
                const hallName = formData.get('name');

                if (!hallName || !hallName.trim()) {
                    alert('Введите название зала!');
                    return;
                }

                const url = createForm.action;
                const csrf = createForm.querySelector('input[name="_token"]').value;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ name: hallName.trim() })
                })
                .then(async resp => {
                    if (resp.status === 422) {
                        const data = await resp.json();
                        throw { type: 'validation', data };
                    }
                    if (!resp.ok) throw { type: 'other' };
                    return resp.json();
                })
                .then(data => {
                    if (data.success && data.hall) {
                        const hallId = data.hall.id;
                        const hallName = data.hall.name;

                        // 1. Инициализируем глобальные объекты, если их нет
                        if (!window.hallSchemeFromServer) window.hallSchemeFromServer = {};
                        if (!window.hallSchemesLocal) window.hallSchemesLocal = {};
                        if (!window.pricesFromServer) window.pricesFromServer = {};

                        // 2. Инициализируем пустую схему для нового зала
                        window.hallSchemesLocal[hallId] = { rows: 0, seats: 0, seatsGrid: [] };
                        window.hallSchemeFromServer[hallId] = null;

                        // 3. Инициализируем цены для нового зала
                        window.pricesFromServer[hallId] = data.prices || { standart: null, vip: null };

                        // 4. Закрываем попап и очищаем форму
                        createForm.reset();
                        if (addHallPopup) addHallPopup.classList.remove('active');

                        // 5. Добавляем зал в UI через существующую функцию
                        addHallToUI(data.hall);

                        alert('Зал успешно создан!');
                    } else {
                        alert('Не удалось создать зал.');
                    }
                })
                .catch(err => {
                    if (err.type === 'validation' && err.data && err.data.errors) {
                        const errors = err.data.errors;
                        const msg = errors.name ? errors.name.join('\n') : 'Ошибка валидации.';
                        alert(msg);
                    } else {
                        alert('Ошибка при создании зала.');
                    }
                });
            });
        }

        // ======== Переключение залов по радиокнопкам ========
        document.addEventListener('change', function(e) {
            // Схема
            if (e.target.matches('input[name="chairs-hall"]')) {
                const hallId = e.target.value;
                onSchemeHallChanged(hallId);
            }

            // Цены
            if (e.target.matches('input[name="prices-hall"]')) {
                const hallId = e.target.value;
                showPricesFormBlockForHall(hallId);
            }
        });

        // ======== Инициализация при загрузке ========
        bindDeleteButtons();
        updateEmptyStateTopList();
        updateEmptyStateSelectors();

        const initChairsRadio = document.querySelector('input[name="chairs-hall"]:checked');
        if (initChairsRadio) {
            onSchemeHallChanged(initChairsRadio.value);
        }

        const initPricesRadio = document.querySelector('input[name="prices-hall"]:checked');
        if (initPricesRadio) {
            showPricesFormBlockForHall(initPricesRadio.value);
        } else {
            hidePricesFormBlockIfNoHalls();
        }

        const pricesForm = document.querySelector('.prices-form-block form.hall-form');
        if (pricesForm) {
            const inputStd = pricesForm.querySelector('input[name="prices[standart]"]');
            const inputVip = pricesForm.querySelector('input[name="prices[vip]"]');
            const tokenInput = pricesForm.querySelector('input[name="_token"]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')|| (tokenInput ? tokenInput.value : '');
            const saveDraft = () => {
                const hallId = pricesForm.dataset.hallId;
                if (!hallId) return;
                setDraftForHall(hallId, {
                    standart: inputStd ? inputStd.value : '',
                    vip: inputVip ? inputVip.value : ''
                });
            };

            if (inputStd) inputStd.addEventListener('input', saveDraft);
            if (inputVip) inputVip.addEventListener('input', saveDraft);

            pricesForm.addEventListener('reset', function() {
                const hallId = pricesForm.dataset.hallId;
                if (!hallId) return;
                clearDraftForHall(hallId);
                const serverPrices = (window.pricesFromServer && window.pricesFromServer[hallId])
                    ? window.pricesFromServer[hallId]
                    : {};
                setTimeout(() => {
                    if (inputStd) inputStd.value = serverPrices.standart != null ? serverPrices.standart : '';
                    if (inputVip) inputVip.value = serverPrices.vip != null ? serverPrices.vip : '';
                }, 0);
            });

            pricesForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const hallId = pricesForm.dataset.hallId;
                    if (!hallId) return;
                    const formData = new FormData(pricesForm);
                    fetch(pricesForm.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        },
                        body: formData
                    })
                        .then(async resp => {
                            if (!resp.ok) {
                                throw new Error('save_failed');
                            }
                            try {
                                return await resp.json();
                            } catch (error) {
                                return null;
                            }
                        })
                        .then(data => {
                            if (data && !data.success) {
                                throw new Error('save_failed');
                            }
                            if (!window.pricesFromServer) window.pricesFromServer = {};
                            window.pricesFromServer[hallId] = (data && data.prices) ? data.prices : {
                                standart: inputStd ? inputStd.value : '',
                                vip: inputVip ? inputVip.value : ''
                            };
                            clearDraftForHall(hallId);
                            if (inputStd) inputStd.dataset.base = window.pricesFromServer[hallId].standart ?? '';
                            if (inputVip) inputVip.dataset.base = window.pricesFromServer[hallId].vip ?? '';
                            alert('Сохранено!');
                        })
                        .catch(() => {
                            alert('Не удалось сохранить цены.');
                        });
            });
        }

        document.addEventListener('click', function (e) {
            const header = e.target.closest('.conf-step__header');
            if (!header) return;

            const step = header.closest('.conf-step');
            if (!step) return;

            if (step.dataset.step === 'scheme') {
                setTimeout(function () {
                    const checked = document.querySelector('input[name="chairs-hall"]:checked');
                    if (checked) {
                        onSchemeHallChanged(checked.value);
                    }
                }, 0);
            }
        });

        hideHallSchemeBlockIfNoHalls();
});