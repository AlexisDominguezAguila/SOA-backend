<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    /* ------------------------------------------------------------------------
     |  VISTA PÚBLICA — solo noticias activas
     * --------------------------------------------------------------------- */
    public function publicIndex()
    {
        return News::where('status', 'active')
                   ->orderByDesc('created_at')
                   ->get();
    }

    /* ------------------------------------------------------------------------
     |  LISTADO (paginado para el panel)
     * --------------------------------------------------------------------- */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        return News::orderByDesc('created_at')
                   ->paginate($perPage);
    }

    /* ------------------------------------------------------------------------
     |  CREAR
     * --------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        // Subir imagen (opcional)
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                                         ->store('news', 'public');
        }

        $news = News::create($data);

        return response()->json($news, Response::HTTP_CREATED);
    }

    /* ------------------------------------------------------------------------
     |  MOSTRAR
     * --------------------------------------------------------------------- */
    public function show(News $news)
    {
        return $news;
    }

    /* ------------------------------------------------------------------------
     |  ACTUALIZAR
     * --------------------------------------------------------------------- */
    public function update(Request $request, News $news)
    {
        $data = $this->validatedData($request);

        $updateData = array_intersect_key(
            $data,
            array_flip(['title', 'description', 'url', 'status'])
        );

        // Eliminar imagen existente si se solicita
        if ($request->boolean('remove_image') && $news->image_path) {
            Storage::disk('public')->delete($news->image_path);
            $updateData['image_path'] = null;
        }

        // Subir nueva imagen (si la hay)
        if ($request->hasFile('image')) {
            if ($news->image_path) {
                Storage::disk('public')->delete($news->image_path);
            }
            $updateData['image_path'] = $request->file('image')
                                               ->store('news', 'public');
        }

        if (!empty($updateData)) {
            $news->update($updateData);
        }

        return $news;
    }

    /* ------------------------------------------------------------------------
     |  ELIMINAR
     * --------------------------------------------------------------------- */
    public function destroy(News $news)
    {
        if ($news->image_path && Storage::disk('public')->exists($news->image_path)) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /* ------------------------------------------------------------------------
     |  VALIDACIÓN CENTRALIZADA
     * --------------------------------------------------------------------- */
    protected function validatedData(Request $request): array
    {
        $rules = [
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'url'          => 'nullable|url|max:2048',
            'status'       => 'required|in:active,inactive',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'sometimes|boolean',
        ];

        return Validator::make($request->all(), $rules)->validate();
    }
}
