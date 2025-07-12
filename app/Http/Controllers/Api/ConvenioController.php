<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Convenio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConvenioController extends Controller
{
public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);

    $convenios = Convenio::orderByDesc('created_at')->paginate($perPage);

    $convenios->getCollection()->transform(function ($convenio) {
        return [
            'id' => $convenio->id,
            'title' => $convenio->titulo,
            'beneficios' => $convenio->beneficios,
            'url' => $convenio->url,
            'status' => $convenio->activo, 
            'image_url' => $convenio->imagen ? asset('storage/' . $convenio->imagen) : null,
            'created_at' => $convenio->created_at,
        ];
    });

    return $convenios;
}


    public function create()
    {
        return view('convenios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'beneficios' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'url' => 'nullable|url',
            'activo' => 'required|in:active,inactive',
        ]);

        $rutaImagen = null;
        if ($request->hasFile('imagen')) {
            $rutaImagen = $request->file('imagen')->store('convenios', 'public');
        }

        Convenio::create([
            'titulo' => $request->titulo,
            'beneficios' => $request->beneficios,
            'imagen' => $rutaImagen,
            'url' => $request->url,
            'activo' => $request->activo,
        ]);

        return redirect()->route('convenios.index')->with('success', 'Convenio registrado correctamente.');
    }

    public function edit(Convenio $convenio)
    {
        return view('convenios.edit', compact('convenio'));
    }

    public function update(Request $request, Convenio $convenio)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'beneficios' => 'required|string',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'url' => 'nullable|url',
            'activo' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('imagen')) {
            if ($convenio->imagen) {
                Storage::disk('public')->delete($convenio->imagen);
            }
            $convenio->imagen = $request->file('imagen')->store('convenios', 'public');
        }

        $convenio->update([
            'titulo' => $request->titulo,
            'beneficios' => $request->beneficios,
            'url' => $request->url,
            'activo' => $request->activo,
        ]);

        return redirect()->route('convenios.index')->with('success', 'Convenio actualizado correctamente.');
    }

 public function destroy(Convenio $convenio)
{
    if ($convenio->imagen) {
        Storage::disk('public')->delete($convenio->imagen);
    }

    $convenio->delete();

    return response()->json([
        'message' => 'Convenio eliminado correctamente.',
    ], 200);
}

    //  Ruta pública para la vista cliente
    public function publicIndex()
    {
        $convenios = Convenio::where('activo', 'active')
            ->orderByDesc('created_at')
            ->get();

        return $convenios->map(function ($convenio) {
            return [
                'id' => $convenio->id,
                'title' => $convenio->titulo,
                'benefits' => explode("\n", $convenio->beneficios),
                'url' => $convenio->url,
                'status' => $convenio->activo,
                'image_url' => $convenio->imagen ? asset('storage/' . $convenio->imagen) : null,
                'created_at' => $convenio->created_at,
            ];
        });
    }
}
