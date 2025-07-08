<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dean;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DeanController extends Controller
{
    /**
     * Lista de decanos (paginado).
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        return Dean::orderByDesc('year_start')->paginate($perPage);
    }

    /**
     * Guarda un nuevo decano.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Manejo opcional de archivo
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')
                ->store('deans', 'public');   // storage/app/public/deans
        }

        $dean = Dean::create($data);
        return response()->json($dean, Response::HTTP_CREATED);
    }

    /**
     * Muestra un decano concreto.
     */
    public function show(Dean $dean)
    {
        return $dean;
    }

    /**
     * Actualiza un decano.
     */
    public function update(Request $request, Dean $dean)
    {
        $data = $this->validateData($request, $dean->id);

        // Sustituir imagen si llega una nueva
        if ($request->hasFile('image')) {
            // Elimina la antigua si existía
            if ($dean->image_url && Storage::disk('public')->exists($dean->image_url)) {
                Storage::disk('public')->delete($dean->image_url);
            }
            $data['image_url'] = $request->file('image')
                ->store('deans', 'public');
        }

        $dean->update($data);
        return $dean;
    }

    /**
     * Elimina un decano.
     */
    public function destroy(Dean $dean)
    {
        // Borra la imagen guardada, si corresponde
        if ($dean->image_url && Storage::disk('public')->exists($dean->image_url)) {
            Storage::disk('public')->delete($dean->image_url);
        }
        $dean->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Validación centralizada.
     */
    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'       => 'required|string|max:255',
            'year_start' => 'required|integer|min:1900|max:2100',
            'year_end'   => 'required|integer|min:1900|max:2100|gte:year_start',
            'is_active'  => 'boolean',
             'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url'  => 'nullable|string|max:2048',
        ]);
    }
}
