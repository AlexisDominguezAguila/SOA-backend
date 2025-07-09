<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dean;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DeanController extends Controller
{
    /**
 * Vista pública: solo decanos activos
 */
public function publicIndex()
{
    return Dean::where('is_active', true)
               ->orderByDesc('year_start')
               ->get(['id', 'name', 'year_start', 'year_end', 'image_url']);
}

    /* ------------------------------------------------------------------------
     |  LISTADO (paginado)
     * --------------------------------------------------------------------- */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        return Dean::orderByDesc('year_start')->paginate($perPage);
    }

    /* ------------------------------------------------------------------------
     |  CREAR
     * --------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        // Subir imagen (opcional)
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('deans', 'public');
        }

        $dean = Dean::create($data);
        return response()->json($dean, Response::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------
     |  MOSTRAR
     * --------------------------------------------------------------------- */
    public function show(Dean $dean)
    {
        return $dean;
    }

    /* ------------------------------------------------------------------------
     |  ACTUALIZAR
     * --------------------------------------------------------------------- */
    public function update(Request $request, Dean $dean)
    {
        $data = $this->validatedData($request, $dean->id);

        // Solo aplicar campos realmente modificados
        $updateData = array_intersect_key(
            $data,
            array_flip(['name', 'year_start', 'year_end', 'is_active'])
        );

        // Actualizar imagen si se envía una nueva
        if ($request->hasFile('image')) {
            if ($dean->image_url && Storage::disk('public')->exists($dean->image_url)) {
                Storage::disk('public')->delete($dean->image_url);
            }
            $updateData['image_url'] = $request->file('image')->store('deans', 'public');
        }

        // Guardar solo si hay cambios
        if (!empty($updateData)) {
            $dean->update($updateData);
        }

        return $dean;
    }

    /* ------------------------------------------------------------------------
     |  ELIMINAR
     * --------------------------------------------------------------------- */
    public function destroy(Dean $dean)
    {
        if ($dean->image_url && Storage::disk('public')->exists($dean->image_url)) {
            Storage::disk('public')->delete($dean->image_url);
        }
        $dean->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /* ------------------------------------------------------------------------
     |  VALIDACIÓN CENTRALIZADA
     * --------------------------------------------------------------------- */
    protected function validatedData(Request $request, ?int $id = null): array
    {
        // Reglas básicas
        $rules = [
            'name'        => 'required|string|max:255',
            'year_start'  => 'required|integer|min:1900|max:2100',
            'year_end'    => 'required|integer|min:1900|max:2100|gte:year_start',
            'is_active'   => 'boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url'   => 'nullable|string|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        // Regla extra: evitar solapamiento de periodos
        if ($request->filled(['year_start', 'year_end'])) {
            $validator->after(function ($validator) use ($request, $id) {
                $start = $request->input('year_start');
                $end   = $request->input('year_end');

                $overlap = Dean::where(function ($q) use ($start, $end) {
                                $q->where('year_start', '<=', $end)
                                  ->where('year_end',   '>=', $start);
                            })
                            ->when($id, fn($q) => $q->where('id', '!=', $id))
                            ->exists();

                if ($overlap) {
                    $msg = 'El período se solapa con otro decano existente.';
                    $validator->errors()->add('year_start', $msg);
                    $validator->errors()->add('year_end',   $msg);
                }
            });
        }

        return $validator->validate();
    }
}
