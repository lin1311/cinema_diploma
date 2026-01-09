(function () {
    const publishButton = document.getElementById('publish-sales-btn');
    if (!publishButton) {
        return;
    }

    const notify = (message) => {
        if (message) {
            window.alert(message);
        }
    };

    publishButton.addEventListener('click', function (event) {
        event.preventDefault();
        publishButton.disabled = true;

        fetch('/admin/publications', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('publish_failed');
                }
                return response.json();
            })
            .then((data) => {
                if (!data || !data.success) {
                    throw new Error('publish_failed');
                }
                notify(data.message || 'Данные успешно опубликованы.');
            })
            .catch(() => {
                notify('Не удалось опубликовать данные.');
            })
            .finally(() => {
                publishButton.disabled = false;
            });
    });
})();

