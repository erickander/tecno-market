<?php
/**
 * TECNO-MARKET | JSON Web Token (HS256)
 */

class Jwt
{
    /**
     * Devuelve ['token' => ..., 'expira_en' => segundos]
     */
    public static function generar(array $claims, ?int $segundos = null): array
    {
        $config   = self::config();
        $ahora    = time();
        $vida     = $segundos ?? (int) $config['expiracion'];

        $payload = array_merge($claims, [
            'iss' => $config['emisor'],
            'iat' => $ahora,
            'exp' => $ahora + $vida,
        ]);

        $cabecera  = self::codificar(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $cuerpo    = self::codificar(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $firma     = self::codificar(hash_hmac('sha256', $cabecera . '.' . $cuerpo, $config['secreto'], true));

        return [
            'token'     => $cabecera . '.' . $cuerpo . '.' . $firma,
            'expira_en' => $vida,
        ];
    }

    /**
     * Valida firma, formato, emisor y expiracion.
     * Lanza ApiException(401) si algo no cuadra.
     */
    public static function validar(string $token): array
    {
        $partes = explode('.', trim($token));

        if (count($partes) !== 3) {
            throw new ApiException(401, 'Token invalido o vencido');
        }

        [$cabecera, $cuerpo, $firma] = $partes;
        $config = self::config();

        $esperada = self::codificar(
            hash_hmac('sha256', $cabecera . '.' . $cuerpo, $config['secreto'], true)
        );

        if (!hash_equals($esperada, $firma)) {
            throw new ApiException(401, 'Token invalido o vencido');
        }

        $payload = json_decode(self::decodificar($cuerpo), true);

        if (!is_array($payload)) {
            throw new ApiException(401, 'Token invalido o vencido');
        }

        if (!isset($payload['exp']) || time() >= (int) $payload['exp']) {
            throw new ApiException(401, 'Token invalido o vencido');
        }

        if (($payload['iss'] ?? '') !== $config['emisor']) {
            throw new ApiException(401, 'Token invalido o vencido');
        }

        return $payload;
    }

    private static function config(): array
    {
        $config = require __DIR__ . '/../config/config.php';
        return $config['jwt'];
    }

    private static function codificar(string $texto): string
    {
        return rtrim(strtr(base64_encode($texto), '+/', '-_'), '=');
    }

    private static function decodificar(string $texto): string
    {
        $relleno = strlen($texto) % 4;

        if ($relleno !== 0) {
            $texto .= str_repeat('=', 4 - $relleno);
        }

        return (string) base64_decode(strtr($texto, '-_', '+/'));
    }
}
