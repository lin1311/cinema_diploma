(function () {
    const publishButton = document.getElementById('publish-sales-btn');
    if (!publishButton) {
        return;
    }
    const publishHint = document.getElementById('publish-sales-hint');
    let salesOpen = Boolean(window.salesOpenFromServer);

    const notify = (message) => {
        if (message) {
            window.alert(message);
        }
    };

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content') || '';

    const getErrorMessage = (error, fallback) => {
        return error?.message
            || (error?.errors && Object.values(error.errors)[0]?.[0])
            || fallback;
    };

    const parseJsonSafe = async (response) => {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    };

    const getLockableSections = () => {
        return Array.from(document.querySelectorAll('.conf-step'))
            .filter((section) => !section.contains(publishButton));
    };

    const applySalesState = (isOpen) => {
        salesOpen = Boolean(isOpen);
        publishButton.textContent = salesOpen ? 'Закрыть продажи' : 'Открыть продажи';
        if (publishHint) {
            publishHint.textContent = salesOpen
                ? 'Чтобы внести измения надо:'
                : 'Всё готово, теперь можно:';
        }

        const sections = getLockableSections();
        sections.forEach((section) => {
            section.style.pointerEvents = salesOpen ? 'none' : '';
            section.style.opacity = salesOpen ? '0.55' : '';
        });

        if (salesOpen) {
            document.querySelectorAll('.popup.active').forEach((popup) => {
                popup.classList.remove('active');
                popup.style.display = 'none';
            });
        }
    };

    const syncAllPrices = async () => {
        const pricesForm = document.querySelector('.prices-form-block form.hall-form');
        const currentHallId = pricesForm?.dataset?.hallId || null;
        const currentStdInput = pricesForm?.querySelector('input[name="prices[standart]"]');
        const currentVipInput = pricesForm?.querySelector('input[name="prices[vip]"]');

        const hallIds = Array.from(document.querySelectorAll('input[name="prices-hall"]'))
            .map((input) => input.value)
            .filter(Boolean);

        if (!hallIds.length) {
            return;
        }

        const hallIdsSet = new Set(hallIds);
        const uniqueHallIds = [...new Set(hallIds)];

        const priceDrafts = typeof window.getPriceDrafts === 'function'
            ? (window.getPriceDrafts() || {})
            : (window.priceDraftsLocal || {});

        for (const hallId of uniqueHallIds) {
            const currentHallValues = currentHallId === hallId
                ? {
                    standart: currentStdInput ? currentStdInput.value : '',
                    vip: currentVipInput ? currentVipInput.value : '',
                }
                : null;
            const draftValues = priceDrafts && typeof priceDrafts[hallId] === 'object'
                ? priceDrafts[hallId]
                : null;
            const serverValues = window.pricesFromServer?.[hallId] || {};
            const priceValues = currentHallValues || draftValues || serverValues;

            const body = new FormData();
            body.append('prices[standart]', priceValues?.standart ?? '');
            body.append('prices[vip]', priceValues?.vip ?? '');

            const response = await fetch(`/admin/prices/${hallId}`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body,
            });

            const payload = await parseJsonSafe(response);
            if (!response.ok || !payload?.success) {
                throw payload || new Error('sync_prices_failed');
            }

            if (!window.pricesFromServer) {
                window.pricesFromServer = {};
            }
            window.pricesFromServer[hallId] = payload.prices || priceValues;
            if (typeof window.clearPriceDraftForHall === 'function') {
                window.clearPriceDraftForHall(hallId);
            }
        }

        if (currentHallId && !hallIdsSet.has(currentHallId) && pricesForm) {
            pricesForm.dataset.hallId = '';
        }
    };

    const syncHallSchemes = async () => {
        const schemes = window.hallSchemesLocal || {};
        const existingHallIds = new Set(
            Array.from(document.querySelectorAll('.conf-step__seances-hall[data-hall]'))
                .map((el) => String(el.dataset.hall))
                .filter(Boolean)
        );
        const hallIds = Object.keys(schemes).filter((hallId) => existingHallIds.has(String(hallId)));
        for (const hallId of hallIds) {
            const scheme = schemes[hallId];
            if (!scheme) {
                continue;
            }

            const response = await fetch(`/admin/halls/${hallId}/scheme`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: JSON.stringify(scheme),
            });

            const payload = await parseJsonSafe(response);
            if (!response.ok || !payload?.success) {
                throw payload || new Error('sync_scheme_failed');
            }
        }
    };

    publishButton.addEventListener('click', async function (event) {
        event.preventDefault();
        publishButton.disabled = true;

        try {
            if (!salesOpen) {
                await syncAllPrices();
                await syncHallSchemes();
            }

            const response = await fetch('/admin/publications', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const data = await parseJsonSafe(response);
            if (!response.ok || !data?.success) {
                throw data || new Error('publish_failed');
            }

            applySalesState(Boolean(data.sales_open));
            notify(data.message || 'Данные успешно опубликованы.');
        } catch (error) {
            notify(getErrorMessage(error, 'Не удалось опубликовать данные.'));
        } finally {
            publishButton.disabled = false;
        }
    });

    applySalesState(salesOpen);
})();
