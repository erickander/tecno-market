<?php
/**
 * TECNO-MARKET | Excepcion controlada de la API
 */

class ApiException extends Exception
{
    public function __construct(
        public int $codigoHttp,
        string $mensaje,
        public array $errores = []
    ) {
        parent::__construct($mensaje);
    }
}
