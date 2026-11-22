<?php
/**
 * TECNO-MARKET | Respuestas uniformes en JSON
 *
 * Exito:  { "ok": true,  "data": {...}, "mensaje": "..." }
 * Error:  { "ok": false, "error": { "codigo": 404, "mensaje": "...", "errores": {...} } }
 */

class Response
{
    public static function json(mixed $payload, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function ok(
        mixed $data = null,
        string $mensaje = 'Operacion realizada correctamente',
        int $codigo = 200
    ): void {
        self::json([
            'ok'      => true,
            'data'    => $data,
            'mensaje' => $mensaje,
        ], $codigo);
    }

    public static function error(int $codigo, string $mensaje, ?array $errores = null): void
    {
        $error = [
            'codigo'  => $codigo,
            'mensaje' => $mensaje,
        ];

        if ($errores !== null && $errores !== []) {
            $error['errores'] = $errores;
        }

        self::json([
            'ok'    => false,
            'error' => $error,
        ], $codigo);
    }

    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }
}
