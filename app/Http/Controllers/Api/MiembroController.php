<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Miembro;
use Illuminate\Support\Facades\Storage;

class MiembroController extends Controller
{
   /**
 * Vista pública: Obtener todos los miembros activos con su gestión activa.
 */
public function publicIndex()
{
    // Solo miembros activos de gestiones activas
    $miembros = Miembro::where('is_active', true)
        ->whereHas('gestion', function ($query) {
            $query->where('status', 'active');
        })
        ->with(['gestion' => function ($query) {
            $query->where('status', 'active');
        }])
        ->get();

    return response()->json([
        'success' => true,
        'data' => $miembros
    ]);
}

    /**
     * Admin: Listar todos los miembros con gestión.
     */
    public function index()
    {
        $miembros = Miembro::with('gestion')->get();

        return response()->json([
            'success' => true,
            'data' => $miembros
        ]);
    }

    /**
     * Admin: Registrar nuevo miembro.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'estado' => 'required|in:active,inactive',
            'gestion_id' => 'required|exists:gestiones,id',
            'imagen' => 'nullable|image|max:2048', 
        ]);

        $miembro = new Miembro();
        $miembro->nombre = $validated['nombre'];
        $miembro->cargo = $validated['cargo'];
        $miembro->is_active = $validated['estado'] === 'active';
        $miembro->gestion_id = $validated['gestion_id'];

        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('miembros', 'public');
            $miembro->img = $path;
        }

        $miembro->save();

        return response()->json([
            'success' => true,
            'message' => 'Miembro registrado correctamente.',
            'data' => $miembro
        ], 201);
    }

    /**
     * Admin: Mostrar un miembro específico.
     */
    public function show(string $id)
    {
        $miembro = Miembro::with('gestion')->find($id);

        if (!$miembro) {
            return response()->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $miembro
        ]);
    }

    /**
     * Admin: Actualizar datos del miembro.
     */
    public function update(Request $request, string $id)
    {
            $miembro = Miembro::find($id);

        if (!$miembro) {
            return response()->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'cargo' => 'sometimes|required|string|max:255',
            'estado' => 'sometimes|required|in:active,inactive',
            'gestion_id' => 'sometimes|required|exists:gestiones,id',
            'imagen' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['nombre'])) $miembro->nombre = $validated['nombre'];
        if (isset($validated['cargo'])) $miembro->cargo = $validated['cargo'];
        if (isset($validated['estado'])) $miembro->is_active = $validated['estado'] === 'active';
        if (isset($validated['gestion_id'])) $miembro->gestion_id = $validated['gestion_id'];

        if ($request->hasFile('imagen')) {
            if ($miembro->img && Storage::disk('public')->exists($miembro->img)) {
                Storage::disk('public')->delete($miembro->img);
            }

            $path = $request->file('imagen')->store('miembros', 'public');
            $miembro->img = $path;
        }

        $miembro->save();

        return response()->json([
            'success' => true,
            'message' => 'Miembro actualizado correctamente.',
            'data' => $miembro
        ]);
    }

    /**
     * Admin: Eliminar un miembro.
     */
    public function destroy(string $id)
    {
        $miembro = Miembro::find($id);

        if (!$miembro) {
            return response()->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
        }

        if ($miembro->img && Storage::disk('public')->exists($miembro->img)) {
            Storage::disk('public')->delete($miembro->img);
        }

        $miembro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Miembro eliminado correctamente.'
        ]);
    }
}
