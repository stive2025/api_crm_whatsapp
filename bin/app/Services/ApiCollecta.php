<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiCollecta
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function obtenerAsignacion()
    {
        $response = Http::get(env('URL_SYNC_CONTACTS'));

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Error al consumir la API externa.');
    }
}
