<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dean;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeanController extends Controller
{
    // ...

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // ───── Subida de imagen ─────────────────────────
        if ($request->hasFile('image')) {
            // Genera un nombre único conservando la extensión
            $filename = Str::uuid() . '.' . $request->file('image')->extension();

            // Guardamos en storage/app/public/decanos/...
            $relativePath = $request->file('image')
                ->storeAs('decanos', $filename, 'public');

            // Guardamos solo la ruta relativa en BD
            $data['image_url'] = $relativePath;
        }

        $dean = Dean::create($data);
        return response()->json($dean, Response::HTTP_CREATED);
    }

    public function update(Request $request, Dean $dean)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            // Elimina la antigua
            if ($dean->image_url && Storage::disk('public')->exists($dean->image_url)) {
                Storage::disk('public')->delete($dean->image_url);
            }

            $filename = Str::uuid() . '.' . $request->file('image')->extension();
            $relativePath = $request->file('image')
                ->storeAs('decanos', $filename, 'public');

            $data['image_url'] = $relativePath;
        }

        $dean->update($data);
        return $dean;
    }

    // ...
}
