document.addEventListener('DOMContentLoaded', function () {
    const addPopup = document.getElementById('add-seance-popup');
    const editPopup = document.getElementById('edit-seance-popup');
    if (!addPopup || !editPopup) {
        return;
    }

    const addForm = addPopup.querySelector('form');
    const addHallSelect = addPopup.querySelector('#seance-hall');
    const addFilmSelect = addPopup.querySelector('#seance-film');
    const addStartTimeInput = addPopup.querySelector('#seance-start-time');

    const editForm = editPopup.querySelector('form');
    const editHallSelect = editPopup.querySelector('#edit-seance-hall');
    const editFilmSelect = editPopup.querySelector('#edit-seance-film');
    const editStartTimeInput = editPopup.querySelector('#edit-seance-start-time');
    const deleteBtn = editPopup.querySelector('[data-action="delete"]');

    const minutesToPixels = 0.5;
    const movieColors = [
        'rgb(133, 255, 137)',
        'rgb(202, 255, 133)',
        'rgb(133, 235, 255)',
        'rgb(255, 211, 133)',
        'rgb(255, 154, 133)',
        'rgb(195, 133, 255)'
    ];

    const getMovieInfo = (movieId) => {
        const movieCard = document.querySelector(`.conf-step__movie[data-id="${movieId}"]`);
        if (!movieCard) {
            return null;
        }
        const title = movieCard.querySelector('.conf-step__movie-title')?.textContent?.trim() || '';
        const durationText = movieCard.querySelector('.conf-step__movie-duration')?.textContent?.trim() || '';
        const duration = Number.parseInt(durationText.split(' ')[0], 10);
        if (!title || Number.isNaN(duration)) {
            return null;
        }
        return { title, duration };
    };

    const parseTimeToMinutes = (time) => {
        if (!time) {
            return null;
        }
        const [hours, minutes] = time.split(':').map((value) => Number.parseInt(value, 10));
        if (Number.isNaN(hours) || Number.isNaN(minutes)) {
            return null;
        }
        return hours * 60 + minutes;
    };

    const getMovieColor = (movieId) => {
        const numericId = Number.parseInt(movieId, 10);
        const index = Number.isNaN(numericId) ? 0 : Math.abs(numericId) % movieColors.length;
        return movieColors[index];
    };

    const buildOptions = (items, placeholder) => {
        if (!items.length) {
            return `<option value="" disabled selected>${placeholder}</option>`;
        }
        return items
            .map(({ value, label }) => `<option value="${value}">${label}</option>`)
            .join('');
    };

    const refreshSelects = (selectedHallId, hallSelect, filmSelect) => {
        const hallItems = Array.from(
            document.querySelectorAll('.conf-step__seances-hall[data-hall]')
        ).map((hall) => ({
            value: hall.dataset.hall,
            label: hall.querySelector('.conf-step__seances-title')?.textContent?.trim() || ''
        }));

        const filmItems = Array.from(
            document.querySelectorAll('.conf-step__movie[data-id]')
        ).map((movie) => ({
            value: movie.dataset.id,
            label: movie.querySelector('.conf-step__movie-title')?.textContent?.trim() || ''
        }));

        if (!hallSelect || !filmSelect) {
            return;
        }

        hallSelect.innerHTML = buildOptions(hallItems, 'Нет доступных залов');
        if (selectedHallId && hallItems.some((item) => item.value === selectedHallId)) {
            hallSelect.value = selectedHallId;
        }

        filmSelect.innerHTML = buildOptions(filmItems, 'Нет доступных фильмов');
    };

    const openAddPopup = (hallId) => {
        refreshSelects(hallId, addHallSelect, addFilmSelect);
        addPopup.classList.add('active');
    };

    const closeAddPopup = () => {
        addForm?.reset();
        addPopup.classList.remove('active');
    };

    document.addEventListener('click', function (event) {
        if (event.target.closest('.conf-step__seances-movie')) {
            return;
        }
        const hallBlock = event.target.closest('.conf-step__seances-hall');
        if (!hallBlock) {
            return;
        }

        const isTitleClick = event.target.closest('.conf-step__seances-title');
        const isTimelineClick = event.target.closest('.conf-step__seances-timeline');
        if (!isTitleClick && !isTimelineClick) {
            return;
        }

        event.preventDefault();
        openAddPopup(hallBlock.dataset.hall);
    });

    document.addEventListener('click', function (event) {
        const seance = event.target.closest('.conf-step__seances-movie');
        if (!seance) {
            return;
        }

        event.preventDefault();
        const hallId = seance.closest('.conf-step__seances-hall')?.dataset.hall;
        if (!hallId) {
            return;
        }
        editPopup.dataset.activeSeanceId = seance.dataset.seanceId || '';
        refreshSelects(hallId, editHallSelect, editFilmSelect);
        editHallSelect.value = hallId;
        editFilmSelect.value = seance.dataset.movieId || '';
        editStartTimeInput.value = seance.dataset.startTime || '00:00';
        editPopup.classList.add('active');
    });

    const closeAddBtn = addPopup.querySelector('.popup__dismiss');
    if (closeAddBtn) {
        closeAddBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeAddPopup();
        });
    }

    const cancelAddBtn = addPopup.querySelector('.conf-step__button-regular[type="button"]');
    if (cancelAddBtn) {
        cancelAddBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeAddPopup();
        });
    }

    addPopup.addEventListener('click', function (event) {
        if (event.target === addPopup) {
            closeAddPopup();
        }
    });

    const renderSeance = ({ hallId, filmId, startTime, seanceId }) => {
        const movieInfo = getMovieInfo(filmId);
        const startMinutes = parseTimeToMinutes(startTime);
        if (!movieInfo || startMinutes === null) {
            return null;
        }

        const timeline = document.querySelector(
            `.conf-step__seances-hall[data-hall="${hallId}"] .conf-step__seances-timeline`
        );
        if (!timeline) {
            return null;
        }

        const width = movieInfo.duration * minutesToPixels;
        const left = startMinutes * minutesToPixels;
        const seance = document.createElement('div');
        seance.classList.add('conf-step__seances-movie');
        seance.dataset.movieId = filmId;
        seance.dataset.startTime = startTime;
        seance.dataset.seanceId = seanceId;
        seance.style.width = `${width}px`;
        seance.style.left = `${left}px`;
        seance.style.backgroundColor = getMovieColor(filmId);
        seance.innerHTML = `
            <p class="conf-step__seances-movie-title">${movieInfo.title}</p>
            <p class="conf-step__seances-movie-start">${startTime}</p>
        `;
        timeline.appendChild(seance);
        return seance;
    };

    const hydrateSeances = () => {
        if (!Array.isArray(window.seancesFromServer)) {
            return;
        }
        window.seancesFromServer.forEach((seance) => {
            renderSeance({
                hallId: String(seance.hall_id),
                filmId: String(seance.movie_id),
                startTime: seance.start_time,
                seanceId: String(seance.id)
            });
        });
    };

    const saveSeances = () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const seanceNodes = Array.from(document.querySelectorAll('.conf-step__seances-movie'));
        const payload = seanceNodes.map((node) => {
            const hallId = node.closest('.conf-step__seances-hall')?.dataset.hall;
            return {
                hall_id: Number.parseInt(hallId, 10),
                movie_id: Number.parseInt(node.dataset.movieId, 10),
                start_time: node.dataset.startTime
            };
        }).filter((seance) => Number.isFinite(seance.hall_id)
            && Number.isFinite(seance.movie_id)
            && seance.start_time);

        fetch('/admin/seances', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {})
            },
            body: JSON.stringify({ seances: payload })
        })
            .then((resp) => resp.json())
            .then((data) => {
                if (!data?.success) {
                    alert('Не удалось сохранить сеансы.');
                }
            })
            .catch(() => {
                alert('Не удалось сохранить сеансы.');
            });
    };

    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const hallId = addHallSelect?.value;
            const filmId = addFilmSelect?.value;
            const startTime = addStartTimeInput?.value;
            if (!hallId || !filmId || !startTime) {
                return;
            }

            const seanceId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            renderSeance({ hallId, filmId, startTime, seanceId });
            closeAddPopup();
        });
    }
    
    const seancesWrapper = document.querySelector('.conf-step__seances')?.closest('.conf-step__wrapper');
    const saveBtn = seancesWrapper?.querySelector('.conf-step__buttons .conf-step__button-accent');
    if (saveBtn) {
        saveBtn.addEventListener('click', function (event) {
            event.preventDefault();
            saveSeances();
        });
    }

    const closeEditPopup = () => {
        editForm?.reset();
        editPopup.classList.remove('active');
        editPopup.dataset.activeSeanceId = '';
    };

    const closeEditBtn = editPopup.querySelector('.popup__dismiss');
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeEditPopup();
        });
    }

    const cancelEditBtn = editPopup.querySelector('.conf-step__button-regular[type="button"]');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function (event) {
            event.preventDefault();
            closeEditPopup();
        });
    }

    editPopup.addEventListener('click', function (event) {
        if (event.target === editPopup) {
            closeEditPopup();
        }
    });

    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            event.preventDefault();
            const seanceId = editPopup.dataset.activeSeanceId;
            const hallId = editHallSelect?.value;
            const filmId = editFilmSelect?.value;
            const startTime = editStartTimeInput?.value;
            if (!seanceId || !hallId || !filmId || !startTime) {
                return;
            }

            const existing = document.querySelector(`.conf-step__seances-movie[data-seance-id="${seanceId}"]`);
            if (!existing) {
                return;
            }

            existing.remove();
            renderSeance({ hallId, filmId, startTime, seanceId });
            closeEditPopup();
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function (event) {
            event.preventDefault();
            const seanceId = editPopup.dataset.activeSeanceId;
            if (!seanceId) {
                return;
            }
            const existing = document.querySelector(`.conf-step__seances-movie[data-seance-id="${seanceId}"]`);
            if (existing) {
                existing.remove();
            }
            closeEditPopup();
        });
    }
});




