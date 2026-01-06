document.addEventListener('DOMContentLoaded', function () {
    // ===== Открытие попапа =====
    const openBtn = document.getElementById('create-movie-btn');
    const popup = document.getElementById('add-movie-popup');
    if (openBtn && popup) {
        openBtn.addEventListener('click', function (e) {
            e.preventDefault();
            popup.classList.add('active');
        });

        // Крестик
        const closeBtn = popup.querySelector('.popup__dismiss');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form?.reset();
                popup.classList.remove('active');
            });
        }

        // Кнопка "отменить"
        const cancelBtn = popup.querySelector('.conf-step__button-regular[type="button"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                form?.reset();
                popup.classList.remove('active');
            });
        }
    }

    // ===== AJAX отправка формы =====
    const form = popup ? popup.querySelector('form') : null;
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const url = form.action;
            const formData = new FormData(form);
            const title = (formData.get('title') || '').toString().trim().toLowerCase();
            const duration = (formData.get('duration') || '').toString().trim();

            const moviesList = document.querySelector('.conf-step__movies');
            if (moviesList) {
                const existingMovies = Array.from(moviesList.querySelectorAll('.conf-step__movie'));
                const hasDuplicate = existingMovies.some((movie) => {
                    const movieTitle = movie.querySelector('.conf-step__movie-title')?.textContent?.trim().toLowerCase();
                    const movieDurationText = movie.querySelector('.conf-step__movie-duration')?.textContent?.trim();
                    const movieDuration = movieDurationText ? movieDurationText.split(' ')[0] : '';
                    return movieTitle === title && movieDuration === duration;
                });

                if (hasDuplicate) {
                    alert('Такой фильм уже есть в списке.');
                    return;
                }
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.movie) {
                    form.reset();
                    popup.classList.remove('active');
                    // Динамически добавить новый фильм на страницу!
                    
                    if (moviesList) {
                        moviesList.insertAdjacentHTML('beforeend',
                        `<div class="conf-step__movie" data-id="${data.movie.id}">
                            <img class="conf-step__movie-poster" alt="poster" src="/assets/admin/i/poster.png">
                            <h3 class="conf-step__movie-title">${data.movie.title}</h3>
                            <p class="conf-step__movie-duration">${data.movie.duration} минут</p>
                        </div>`);
                    }
                } else {
                    alert(data.message || 'Ошибка при добавлении фильма');
                }
            })
            .catch(() => alert('Ошибка соединения'));
        });
    }

    if (popup) {
        popup.addEventListener('click', function (event) {
            if (event.target === popup) {
                form?.reset();
                popup.classList.remove('active');
            }
        });
    }
});
