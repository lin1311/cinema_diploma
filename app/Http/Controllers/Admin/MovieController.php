<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\BlocksChangesWhenSalesOpen;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Hall;
use Illuminate\Support\Facades\Validator;


class MovieController extends Controller
{
    use BlocksChangesWhenSalesOpen;

    // Список всех фильмов
    public function index()
    {
        $movies = Movie::all();
        $halls = Hall::all(); 
        return view('admin.index', compact('movies', 'halls'));
    }

    // Сохраняет новый фильм
    public function store(Request $request)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'max:255'],
                'duration' => ['required', 'integer', 'min:1'],
                'description' => ['required', 'string'],
                'country' => ['required', 'string', 'max:255'],
                'poster_url' => ['required', 'string', 'max:1024', 'regex:/^\/assets\/admin\/i\/posters\/[A-Za-z0-9._-]+$/'],
            ],
            [
                'title.required' => 'Укажите название фильма.',
                'duration.required' => 'Укажите продолжительность фильма.',
                'duration.integer' => 'Продолжительность должна быть целым числом.',
                'duration.min' => 'Продолжительность должна быть больше 0.',
                'description.required' => 'Укажите описание фильма.',
                'country.required' => 'Укажите страну.',
                'poster_url.required' => 'Сначала загрузите постер.',
                'poster_url.regex' => 'Передан некорректный путь к постеру.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()
                ->route('admin.movies.index')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $duplicateMovie = Movie::where('title', $validated['title'])
            ->where('duration', $validated['duration'])
            ->first();
        if ($duplicateMovie) {
            $this->removePosterFileByUrl($validated['poster_url'] ?? null);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Такой фильм уже существует.'
                ], 422);
            }

            return redirect()
                ->route('admin.movies.index')
                ->withErrors(['title' => 'Такой фильм уже существует.']);
        }

        $movie = Movie::create($validated);

        if ($request->ajax()) {
            // Вернуть данные для вставки на страницу без перезагрузки
            return response()->json([
                'success' => true,
                'movie' => $movie
            ]);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Фильм добавлен');
    }


    // (Остальные CRUD-методы
    public function edit(Movie $movie)
    {
        if (request()->ajax()) {
            return response()->json(['movie' => $movie]);
        }
    }

    public function update(Request $request, Movie $movie)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'max:255'],
                'duration' => ['required', 'integer', 'min:1'],
                'description' => ['nullable', 'string'],
                'country' => ['nullable', 'string', 'max:255'],
                'poster_url' => ['required', 'string', 'max:1024', 'regex:/^\/assets\/admin\/i\/posters\/[A-Za-z0-9._-]+$/'],
            ],
            [
                'title.required' => 'Укажите название фильма.',
                'duration.required' => 'Укажите продолжительность фильма.',
                'duration.integer' => 'Продолжительность должна быть целым числом.',
                'duration.min' => 'Продолжительность должна быть больше 0.',
                'poster_url.required' => 'Укажите постер фильма.',
                'poster_url.regex' => 'Передан некорректный путь к постеру.',
            ]
        );

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()
                ->route('admin.movies.index')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $duplicateMovie = Movie::query()
            ->where('id', '!=', $movie->id)
            ->where('title', $validated['title'])
            ->where('duration', $validated['duration'])
            ->first();

        if ($duplicateMovie) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Такой фильм уже существует.',
                    'errors' => ['title' => ['Такой фильм уже существует.']],
                ], 422);
            }

            return redirect()
                ->route('admin.movies.index')
                ->withErrors(['title' => 'Такой фильм уже существует.'])
                ->withInput();
        }

        $movie->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'movie' => $movie]);
        }
        return redirect()->route('admin.movies.index');
    }

    public function uploadTempPoster(Request $request)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $request->validate([
            'poster' => 'required|image|max:5120',
        ]);

        try {
            $file = $request->file('poster');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файл постера не был загружен корректно.',
                ], 422);
            }

            $storedUrl = $this->storePosterFile($file, 'movie-temp');

            return response()->json([
                'success' => true,
                'poster_url' => $storedUrl,
                'original_name' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка на сервере при загрузке постера.',
            ], 500);
        }
    }

    public function updatePoster(Request $request, Movie $movie)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $request->validate([
            'poster' => 'required|image|max:5120',
        ]);

        try {
            $file = $request->file('poster');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Файл постера не был загружен корректно.',
                ], 422);
            }

            $this->removePosterFileByUrl($movie->poster_url);
            $movie->poster_url = $this->storePosterFile($file, 'movie-' . $movie->id);
            $movie->save();
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Ошибка на сервере при загрузке постера.',
            ], 500);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'movie' => $movie,
            ]);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Постер обновлён');
    }

    public function destroy(Request $request, Movie $movie)
    {
        if ($response = $this->denyIfSalesOpen($request)) {
            return $response;
        }

        $movie->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.movies.index');
    }

    private function storePosterFile($file, string $prefix): string
    {
        $relativeDir = 'assets/admin/i/posters';
        $targetDir = public_path($relativeDir);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = $prefix . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . strtolower($extension);
        $file->move($targetDir, $filename);

        return '/' . $relativeDir . '/' . $filename;
    }

    private function removePosterFileByUrl(?string $posterUrl): void
    {
        $relativePath = $this->extractPublicPosterPath($posterUrl);
        if (!$relativePath) {
            return;
        }

        $fullPath = public_path($relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function extractPublicPosterPath(?string $posterUrl): ?string
    {
        if (!$posterUrl) {
            return null;
        }

        $path = parse_url($posterUrl, PHP_URL_PATH) ?: $posterUrl;
        $allowedPrefix = '/assets/admin/i/posters/';
        if (!str_starts_with($path, $allowedPrefix)) {
            return null;
        }
        return ltrim($path, '/');
    }
}
