<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CursoController extends Controller
{
    public function publicIndex()
{
    $cursos = Curso::where('activo', 'active')->orderByDesc('created_at')->get();

    return $cursos->map(function ($curso) {
        return [
            'id' => $curso->id,
            'title' => $curso->titulo,
            'description' => $curso->descripcion,
            'speaker' => $curso->ponente,
            'url' => $curso->url,
            'status' => $curso->activo,
            'image_url' => $curso->imagen ? asset('storage/' . $curso->imagen) : null,
            'created_at' => $curso->created_at,
        ];
    });
}


    public function index()
    {
        $cursos = Curso::all()->map(function ($curso) {
            return [
                'id' => $curso->id,
                'title' => $curso->titulo,
                'description' => $curso->descripcion,
                'speaker' => $curso->ponente,
                'url' => $curso->url,
                'status' => $curso->activo,
                'image_url' => $curso->imagen
                    ? asset('storage/' . $curso->imagen) 
                    : null,
                'created_at' => $curso->created_at,
            ];
        });

        return response()->json(['data' => $cursos]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'ponente' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'activo' => 'nullable|in:active,inactive',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $curso = new Curso();
        $curso->titulo = $request->titulo;
        $curso->descripcion = $request->descripcion;
        $curso->ponente = $request->ponente;
        $curso->url = $request->url;
        $curso->activo = $request->activo ?? 'active';

        if ($request->hasFile('imagen')) {
            $curso->imagen = $request->file('imagen')->store('cursos', 'public');
        }

        $curso->save();

        return response()->json(['message' => 'Curso creado correctamente'], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'ponente' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'activo' => 'nullable|in:active,inactive',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $curso = Curso::findOrFail($id);
        $curso->titulo = $request->titulo;
        $curso->descripcion = $request->descripcion;
        $curso->ponente = $request->ponente;
        $curso->url = $request->url;
        $curso->activo = $request->activo ?? $curso->activo;

        if ($request->hasFile('imagen')) {
            if ($curso->imagen && Storage::disk('public')->exists($curso->imagen)) {
                Storage::disk('public')->delete($curso->imagen);
            }

            $curso->imagen = $request->file('imagen')->store('cursos', 'public');
        }

        $curso->save();

        return response()->json(['message' => 'Curso actualizado correctamente'], 200);
    }

    public function destroy(Curso $curso)
    {
        if ($curso->imagen && Storage::disk('public')->exists($curso->imagen)) {
            Storage::disk('public')->delete($curso->imagen);
        }

        $curso->delete();

        return response()->json(['message' => 'Curso eliminado correctamente'], 200);
    }
}
