<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Hall;


class MovieController extends Controller
{
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
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'country'  => 'nullable|string|max:255',
        ]);
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
        $movie->update($request->all());
        if ($request->ajax()) {
            return response()->json(['success' => true, 'movie' => $movie]);
        }
        return redirect()->route('admin.movies.index');
    }

    public function destroy(Request $request, Movie $movie)
    {
        $movie->delete();
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('admin.movies.index');
    }  
}

