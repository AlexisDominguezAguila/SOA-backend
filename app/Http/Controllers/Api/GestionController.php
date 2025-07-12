<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gestion;
use Illuminate\Support\Facades\Storage;

class GestionController extends Controller
{
/**
 * Mostrar listado público de gestiones (cliente).
 */
public function publicIndex()
{
    // Solo gestiones activas con sus miembros activos
    $gestiones = Gestion::where('status', 'active')
        ->with(['miembros' => function ($query) {
            $query->where('is_active', true);
        }])
        ->orderBy('inicio', 'desc') 
        ->get();

    return response()->json([
        'success' => true,
        'data' => $gestiones
    ]);
}


    /**
     * Mostrar listado completo para administrador.
     */
    public function index() 
{
    $gestiones = Gestion::with('miembros')
                        ->orderBy('inicio', 'desc')
                        ->get();

    return response()->json([
        'success' => true,
        'data' => $gestiones
    ]);
}


    /**
     * Registrar una nueva gestión.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'lema' => 'nullable|string|max:255',
            'inicio' => 'required|integer',
            'fin' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $gestion = Gestion::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gestión registrada correctamente',
            'data' => $gestion
        ]);
    }

    /**
     * Mostrar una gestión específica.
     */
    public function show(string $id)
    {
        $gestion = Gestion::with('miembros')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $gestion
        ]);
    }

    /**
     * Actualizar una gestión.
     */
    public function update(Request $request, string $id)
    {
        $gestion = Gestion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'lema' => 'nullable|string|max:255',
            'inicio' => 'required|integer',
            'fin' => 'required|integer',
            'status' => 'required|in:active,inactive',
        ]);

        $gestion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gestión actualizada correctamente',
            'data' => $gestion
        ]);
    }

    /**
     * Eliminar una gestión.
     */
   public function destroy(string $id)
{
    $gestion = Gestion::with('miembros')->findOrFail($id);
    
    // Eliminar todos los miembros asociados
    foreach ($gestion->miembros as $miembro) {
        if ($miembro->img && Storage::disk('public')->exists($miembro->img)) {
            Storage::disk('public')->delete($miembro->img);
        }
        $miembro->delete();
    }
    
    $gestion->delete();

    return response()->json([
        'success' => true,
        'message' => 'Gestión y sus miembros eliminados correctamente'
    ]);
}
}
