<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAController extends Controller
{
    public function redactarNoticia(Request $request)
    {
        // Validación mejorada con mensajes más claros
        $request->validate([
            'detalles' => 'required|string|max:2000'
        ], [
            'detalles.required' => 'El texto a mejorar es requerido',
            'detalles.max' => 'El texto no puede exceder los 2000 caracteres'
        ]);

        $texto = $request->input('detalles');
        
        // Verificar que la clave de API está configurada
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            Log::error('OPENAI_API_KEY no está configurada en .env');
            return response()->json([
                'success' => false,
                'message' => 'Error de configuración del servidor'
            ], 500);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30) // Reducir timeout para manejar errores mejor
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Eres un experto en redacción de noticias institucionales. Mejora la redacción del siguiente texto manteniendo su significado original pero haciéndolo más profesional y atractivo.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $texto
                    ]
                ],
                'temperature' => 0.7, 
                'max_tokens' => 500,
            ]);

            // Depuración: Registrar la solicitud y respuesta
            Log::debug('Solicitud a OpenAI', [
                'payload' => [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => '...'],
                        ['role' => 'user', 'content' => substr($texto, 0, 100) . '...']
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 500
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Verificar estructura de respuesta
                if (isset($responseData['choices'][0]['message']['content'])) {
                    $content = $responseData['choices'][0]['message']['content'];
                    $content = $this->limpiarTextoIA($content);
                    
                    Log::debug('Respuesta de OpenAI', ['redaccion' => $content]);
                    
                    return response()->json([
                        'success' => true,
                        'redaccion' => $content
                    ]);
                } else {
                    Log::error('Estructura de respuesta inesperada de OpenAI', $responseData);
                    return response()->json([
                        'success' => false,
                        'message' => 'La respuesta de IA no tiene el formato esperado'
                    ], 500);
                }
            } else {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                Log::error("Error en OpenAI - Status: $statusCode", [
                    'response' => $errorBody,
                    'request' => $texto
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar la solicitud de IA',
                    'error' => json_decode($errorBody, true)
                ], $statusCode);
            }
        } catch (\Exception $e) {
            Log::error('Excepción en redactarNoticia: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $texto
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    private function limpiarTextoIA($texto)
    {
        // Eliminar espacios extra y saltos de línea innecesarios
        $texto = trim(preg_replace('/\s+/', ' ', $texto));
        
        // Eliminar frases iniciales comunes
        $frasesIniciales = [
            'Por supuesto, aquí tienes una versión mejorada del texto:',
            'Texto mejorado:',
            'Versión mejorada:',
            'Aquí tienes la redacción mejorada:',
            'La versión mejorada sería:'
        ];
        
        foreach ($frasesIniciales as $frase) {
            if (stripos($texto, $frase) === 0) {
                $texto = trim(substr($texto, strlen($frase)));
                break;
            }
        }
        
        return $texto;
    }
}