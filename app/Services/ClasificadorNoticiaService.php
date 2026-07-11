<?php

namespace App\Services;

use App\Models\TipoNoticia;
use App\Models\Tema;
use App\Models\TemaEspecifico;
use App\Models\SubTema_Especifico;
use App\Models\TemaMinisterio;
use Illuminate\Support\Facades\Http;

class ClasificadorNoticiaService
{
    public function clasificar(string $titular, string $texto, bool $esMinisterio = false): array
    {
        $catalogo = $this->catalogo($esMinisterio);

        $response = Http::withToken(config('services.openai.api_key'))
            ->timeout(45)
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4o-mini',
                'input' => [
                    [
                        'role' => 'system',
                        'content' => '
                            Clasifica noticias usando únicamente las opciones del catálogo provisto.

                            Reglas obligatorias:
                            1. Usa únicamente IDs existentes en el catálogo.
                            2. Devuelve máximo 3 clasificaciones temáticas relevantes.
                            3. Cada tema_id debe pertenecer obligatoriamente al tipo_noticia_id mediante tiponoticia_id.
                            4. Cada tema_especifico_id debe pertenecer obligatoriamente al tema_id.
                            5. Cada subtema_especifico_id debe pertenecer obligatoriamente al tema_especifico_id mediante temaespecifico_id.
                            6. No mezcles IDs de jerarquías diferentes.
                            7. Si una jerarquía no es válida, usa null en ese nivel y en los niveles inferiores.
                            8. Si es Ministerio, devuelve temas_ministerio y deja temas vacío.
                            9. Si no es Ministerio, devuelve temas y deja temas_ministerio vacío.

                            Devuelve justificación breve por cada clasificación.
                        ',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'titular' => $titular,
                            'texto' => $texto,
                            'es_ministerio' => $esMinisterio,
                            'catalogo' => $catalogo,
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'clasificacion_noticia_multiple',
                        'strict' => true,
                        'schema' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => [
                                'es_ministerio' => ['type' => 'boolean'],

                                'temas' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'additionalProperties' => false,
                                        'properties' => [
                                            'tipo_noticia_id' => ['type' => ['integer', 'null']],
                                            'tema_id' => ['type' => ['integer', 'null']],
                                            'tema_especifico_id' => ['type' => ['integer', 'null']],
                                            'subtema_especifico_id' => ['type' => ['integer', 'null']],
                                            'confianza' => ['type' => 'number'],
                                            'justificacion' => ['type' => 'string'],
                                        ],
                                        'required' => [
                                            'tipo_noticia_id',
                                            'tema_id',
                                            'tema_especifico_id',
                                            'subtema_especifico_id',
                                            'confianza',
                                            'justificacion',
                                        ],
                                    ],
                                ],

                                'temas_ministerio' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'object',
                                        'additionalProperties' => false,
                                        'properties' => [
                                            'tema_ministerio_id' => ['type' => ['integer', 'null']],
                                            'confianza' => ['type' => 'number'],
                                            'justificacion' => ['type' => 'string'],
                                        ],
                                        'required' => [
                                            'tema_ministerio_id',
                                            'confianza',
                                            'justificacion',
                                        ],
                                    ],
                                ],
                            ],
                            'required' => [
                                'es_ministerio',
                                'temas',
                                'temas_ministerio',
                            ],
                        ],
                    ],
                ],
            ]);

        $response->throw();

        $data = $response->json();

        $json = $data['output'][0]['content'][0]['text'] ?? null;

        $resultado = $json ? json_decode($json, true) : [];

        return $this->normalizarClasificacion($resultado, $esMinisterio);
    }

    private function catalogo(bool $esMinisterio): array
    {
        if ($esMinisterio) {
            return [
                'temas_ministerio' => TemaMinisterio::select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->toArray(),
            ];
        }

        return [
            'tipos_noticia' => TipoNoticia::select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray(),

            'temas' => Tema::select('id', 'name', 'tiponoticia_id')
                ->orderBy('tiponoticia_id')
                ->orderBy('name')
                ->get()
                ->toArray(),

            'temas_especificos' => TemaEspecifico::select('id', 'name', 'tema_id')
                ->orderBy('tema_id')
                ->orderBy('name')
                ->get()
                ->toArray(),

            'subtemas_especificos' => SubTema_Especifico::select('id', 'name', 'temaespecifico_id')
                ->orderBy('temaespecifico_id')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ];
    }

    private function normalizarClasificacion(array $data, bool $esMinisterio): array
    {
        if ($esMinisterio) {
            $temasMinisterio = collect($data['temas_ministerio'] ?? [])
                ->take(3)
                ->map(function ($item) {
                    $temaMinisterioId = $item['tema_ministerio_id'] ?? null;

                    if ($temaMinisterioId && ! TemaMinisterio::where('id', $temaMinisterioId)->exists()) {
                        $temaMinisterioId = null;
                    }

                    return [
                        'tema_ministerio_id' => $temaMinisterioId,
                        'confianza' => $item['confianza'] ?? 0,
                        'justificacion' => $item['justificacion'] ?? '',
                    ];
                })
                ->filter(fn ($item) => !empty($item['tema_ministerio_id']))
                ->values()
                ->toArray();

            return [
                'es_ministerio' => true,
                'temas' => [],
                'temas_ministerio' => $temasMinisterio,
            ];
        }

        $temas = collect($data['temas'] ?? [])
            ->take(3)
            ->map(function ($item) {
                $tipoNoticiaId = $item['tipo_noticia_id'] ?? null;
                $temaId = $item['tema_id'] ?? null;
                $temaEspecificoId = $item['tema_especifico_id'] ?? null;
                $subtemaEspecificoId = $item['subtema_especifico_id'] ?? null;

                if ($temaId && ! Tema::where('id', $temaId)
                    ->where('tiponoticia_id', $tipoNoticiaId)
                    ->exists()) {

                    $temaId = null;
                    $temaEspecificoId = null;
                    $subtemaEspecificoId = null;
                }

                if ($temaEspecificoId && ! TemaEspecifico::where('id', $temaEspecificoId)
                    ->where('tema_id', $temaId)
                    ->exists()) {

                    $temaEspecificoId = null;
                    $subtemaEspecificoId = null;
                }

                if ($subtemaEspecificoId && ! SubTema_Especifico::where('id', $subtemaEspecificoId)
                    ->where('temaespecifico_id', $temaEspecificoId)
                    ->exists()) {

                    $subtemaEspecificoId = null;
                }

                return [
                    'tipo_noticia_id' => $tipoNoticiaId,
                    'tema_id' => $temaId,
                    'tema_especifico_id' => $temaEspecificoId,
                    'subtema_especifico_id' => $subtemaEspecificoId,
                    'confianza' => $item['confianza'] ?? 0,
                    'justificacion' => $item['justificacion'] ?? '',
                ];
            })
            ->filter(fn ($item) => !empty($item['tipo_noticia_id']) && !empty($item['tema_id']))
            ->values()
            ->toArray();

        return [
            'es_ministerio' => false,
            'temas' => $temas,
            'temas_ministerio' => [],
        ];
    }
}