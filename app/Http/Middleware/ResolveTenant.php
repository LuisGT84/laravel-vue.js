<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Resuelve el “tenant” por subdominio y cambia la BD en caliente.
 * - empresa1.midominio.com  -> DB: laravel_taller_empresa1
 * - empresa2.midominio.com  -> DB: laravel_taller_empresa2
 *
 * Para desarrollo local, puedes usar hosts como:
 *   empresa1.localhost
 *   empresa2.localhost
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Subdominio actual
        $host = $request->getHost();                  // p.ej. empresa1.localhost
        $sub  = explode('.', $host)[0] ?? null;       // "empresa1"

        // 2) Mapa de subdominio -> nombre_de_base
        $map = config('tenants.map');                 // ['empresa1' => 'laravel_taller_empresa1', ...]

        // 3) Si no hay subdominio conocido, usa la BD por defecto (config/env)
        $database = $map[$sub] ?? config('database.connections.mysql.database');

        // 4) Cambia la base de datos en runtime y reconecta
        Config::set('database.connections.mysql.database', $database);
        DB::purge('mysql');
        DB::reconnect('mysql');

        // 5) Continúa la petición
        return $next($request);
    }
}
