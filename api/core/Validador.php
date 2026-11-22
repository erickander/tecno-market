<?php
/**
 * TECNO-MARKET | Validaciones del lado del servidor
 *
 * Uso:
 *   $errores = Validador::validar($datos, [
 *       'nombre' => ['requerido', 'texto:3,100'],
 *       'precio' => ['requerido', 'numero', 'min:0.01'],
 *   ]);
 *   if ($errores !== []) { Response::error(422, 'Datos invalidos', $errores); }
 */

class Validador
{
    public static function validar(array $datos, array $reglas): array
    {
        $errores = [];

        foreach ($reglas as $campo => $lista) {
            $valor = $datos[$campo] ?? null;
            $vacio = $valor === null || $valor === '' || (is_array($valor) && $valor === []);
            $etiqueta = self::etiqueta($campo);

            foreach ($lista as $regla) {
                [$nombre, $argumento] = array_pad(explode(':', $regla, 2), 2, null);

                if ($nombre === 'requerido') {
                    if ($vacio) {
                        $errores[$campo][] = "El campo {$etiqueta} es obligatorio";
                    }
                    continue;
                }

                if ($vacio) {
                    continue;
                }

                switch ($nombre) {
                    case 'texto':
                        [$min, $max] = array_pad(array_map('intval', explode(',', (string) $argumento)), 2, 0);

                        if (!is_string($valor) && !is_numeric($valor)) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser texto";
                            break;
                        }

                        $longitud = mb_strlen((string) $valor);

                        if ($longitud < $min) {
                            $errores[$campo][] = "El campo {$etiqueta} debe tener al menos {$min} caracteres";
                        }

                        if ($max > 0 && $longitud > $max) {
                            $errores[$campo][] = "El campo {$etiqueta} no puede superar {$max} caracteres";
                        }
                        break;

                    case 'entero':
                        if (filter_var($valor, FILTER_VALIDATE_INT) === false) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser un numero entero";
                        }
                        break;

                    case 'numero':
                        if (!is_numeric($valor)) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser numerico";
                        }
                        break;

                    case 'min':
                        if (is_numeric($valor) && (float) $valor < (float) $argumento) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser mayor o igual a {$argumento}";
                        }
                        break;

                    case 'max':
                        if (is_numeric($valor) && (float) $valor > (float) $argumento) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser menor o igual a {$argumento}";
                        }
                        break;

                    case 'email':
                        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser un correo valido";
                        }
                        break;

                    case 'url_o_archivo':
                        $texto = (string) $valor;

                        if (preg_match('#^https?://#i', $texto)) {
                            if (!filter_var($texto, FILTER_VALIDATE_URL)) {
                                $errores[$campo][] = "El campo {$etiqueta} debe ser una URL valida";
                            }
                        } elseif (!preg_match('#^[A-Za-z0-9._-]+$#', $texto)) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser un nombre de archivo o una URL http(s)";
                        }
                        break;

                    case 'en':
                        $opciones = explode(',', (string) $argumento);

                        if (!in_array((string) $valor, $opciones, true)) {
                            $errores[$campo][] = "El campo {$etiqueta} debe ser uno de: " . implode(', ', $opciones);
                        }
                        break;
                }
            }
        }

        return $errores;
    }

    private static function etiqueta(string $campo): string
    {
        return ucfirst(str_replace('_', ' ', $campo));
    }
}
