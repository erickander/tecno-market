<?php
/**
 * TECNO-MARKET | Enrutador
 *
 * Cada ruta declara los roles que debera exigir el middleware de seguridad.
 * Mientras P4 no implemente Auth::requerir(), las rutas quedan abiertas.
 */

class Router
{
    /** @var array<int, array{metodo:string, patron:string, handler:mixed, roles:array}> */
    private array $rutas = [];

    public function get(string $patron, mixed $handler, array $roles = []): void
    {
        $this->agregar('GET', $patron, $handler, $roles);
    }

    public function post(string $patron, mixed $handler, array $roles = []): void
    {
        $this->agregar('POST', $patron, $handler, $roles);
    }

    public function put(string $patron, mixed $handler, array $roles = []): void
    {
        $this->agregar('PUT', $patron, $handler, $roles);
    }

    public function patch(string $patron, mixed $handler, array $roles = []): void
    {
        $this->agregar('PATCH', $patron, $handler, $roles);
    }

    public function delete(string $patron, mixed $handler, array $roles = []): void
    {
        $this->agregar('DELETE', $patron, $handler, $roles);
    }

    private function agregar(string $metodo, string $patron, mixed $handler, array $roles): void
    {
        $this->rutas[] = [
            'metodo'  => $metodo,
            'patron'  => '/' . trim($patron, '/'),
            'handler' => $handler,
            'roles'   => $roles,
        ];
    }

    public function dispatch(): void
    {
        $metodoActual = Request::metodo();
        $rutaActual   = Request::ruta();
        $permitidos   = [];

        foreach ($this->rutas as $ruta) {
            $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $ruta['patron']) . '$#';

            if (!preg_match($regex, $rutaActual, $coincidencias)) {
                continue;
            }

            $permitidos[] = $ruta['metodo'];

            if ($ruta['metodo'] !== $metodoActual) {
                continue;
            }

            $parametros = array_slice($coincidencias, 1);

            // P4: aqui se aplicara el control de roles real
            Auth::requerir($ruta['roles']);

            $handler = $ruta['handler'];

            if (is_callable($handler)) {
                call_user_func_array($handler, $parametros);
            } else {
                [$clase, $accion] = $handler;
                call_user_func_array([new $clase(), $accion], $parametros);
            }

            return;
        }

        if ($permitidos !== []) {
            header('Allow: ' . implode(', ', array_unique($permitidos)));
            Response::error(405, 'El metodo HTTP no esta permitido para esta ruta');
        }

        Response::error(404, 'Ruta no encontrada');
    }
}
