<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comunicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComunicadoController extends Controller
{
    // Mostrar comunicados públicos activos (sin paginación)
public function publicIndex()
{
    $comunicados = Comunicado::where('status', 'active')
        ->orderByDesc('created_at')
        ->get();

    return $comunicados->map(function ($comunicado) {
        return [
            'id' => $comunicado->id,
            'title' => $comunicado->title,
            'status' => $comunicado->status,
            'image_url' => $comunicado->image ? asset('storage/' . $comunicado->image) : null,
            'created_at' => $comunicado->created_at,
        ];
    });
}

    // Listar todos los comunicados (con paginación opcional)
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        return Comunicado::orderByDesc('created_at')->paginate($perPage);
    }

    // Crear un nuevo comunicado
    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'status' => 'in:active,inactive',
            'image'  => 'nullable|image|max:2048', // 2MB
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('comunicados', 'public');
        }

        $comunicado = Comunicado::create([
            'title'  => $request->title,
            'status' => $request->status ?? 'active',
            'image'  => $imagePath,
        ]);

        return response()->json($comunicado, 201);
    }

    // Actualizar comunicado (usando método POST con _method=PUT)
    public function update(Request $request, $id)
    {
        $comunicado = Comunicado::findOrFail($id);

        $request->validate([
            'title'  => 'required|string|max:255',
            'status' => 'in:active,inactive',
            'image'  => 'nullable|image|max:2048',
        ]);

        // Si hay nueva imagen, eliminar la anterior
        if ($request->hasFile('image')) {
            if ($comunicado->image && Storage::disk('public')->exists($comunicado->image)) {
                Storage::disk('public')->delete($comunicado->image);
            }

            $comunicado->image = $request->file('image')->store('comunicados', 'public');
        }

        $comunicado->title  = $request->title;
        $comunicado->status = $request->status ?? 'active';
        $comunicado->save();

        return response()->json($comunicado);
    }

    // Eliminar comunicado
    public function destroy($id)
    {
        $comunicado = Comunicado::findOrFail($id);

        if ($comunicado->image && Storage::disk('public')->exists($comunicado->image)) {
            Storage::disk('public')->delete($comunicado->image);
        }

        $comunicado->delete();

        return response()->json(['message' => 'Comunicado eliminado']);
    }
}
