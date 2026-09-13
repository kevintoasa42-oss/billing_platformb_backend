<?php

namespace App\Context\V1\Modules\SriAuthorization\Domain\Services;

/**
 * Sends the XML to the SRI reception web service (RecepcionComprobantesOffline).
 */
class SriReceptionService
{
    /**
     * Send the XML to the SRI reception web service.
     *
     * @param  string  $xml  The signed XML content
     * @param  string  $environment  "1"=pruebas, "2"=produccion
     * @return array{status: bool, sri_state: ?string, message: ?string, raw: ?array}
     */
    public function send(string $xml, string $environment): array
    {
        $url = $this->getUrl($environment);

        try {
            $options = [
                'cache_wsdl' => 0,
                'trace' => 1,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ];

            $client = new \SoapClient($url, $options);
            $result = $client->validarComprobante(['xml' => $xml]);

            if (!$result || !property_exists($result, 'RespuestaRecepcionComprobante')) {
                return $this->error('WS Error: No response from SRI');
            }

            $recepcion = $result->RespuestaRecepcionComprobante;

            if ($recepcion->estado === 'RECIBIDA') {
                return [
                    'status' => true,
                    'sri_state' => 'RECIBIDA',
                    'message' => null,
                    'raw' => $this->objectToArray($result),
                ];
            }

            // Not received - extract error messages
            $message = 'WS Error';
            if (property_exists($recepcion, 'comprobantes')) {
                $comprobantes = $recepcion->comprobantes;
                if (property_exists($comprobantes, 'comprobante')) {
                    $comprobante = $comprobantes->comprobante;
                    $mensajes = is_array($comprobante) ? $comprobante[0]->mensajes : $comprobante->mensajes;

                    if (property_exists($mensajes, 'mensaje')) {
                        $msg = $mensajes->mensaje;
                        if (is_array($msg)) {
                            $message = $msg[0]->mensaje;
                        } else {
                            $message = $msg->mensaje;
                        }

                        // "clave acceso registrada" is treated as success (already received)
                        if (str_contains(strtolower($message), 'clave acceso registrada')) {
                            return [
                                'status' => true,
                                'sri_state' => 'RECIBIDA',
                                'message' => $message,
                                'raw' => $this->objectToArray($result),
                            ];
                        }
                    }
                }
            }

            return [
                'status' => false,
                'sri_state' => $recepcion->estado ?? null,
                'message' => $message,
                'raw' => $this->objectToArray($result),
            ];
        } catch (\SoapFault $e) {
            return $this->error('SOAP Fault: ' . $e->getMessage());
        } catch (\Exception $e) {
            return $this->error('Exception: ' . $e->getMessage());
        }
    }

    private function getUrl(string $environment): string
    {
        $key = $environment === '2' ? 'produccion' : 'pruebas';
        return config("sri.wsdl.reception.{$key}");
    }

    private function error(string $message): array
    {
        return [
            'status' => false,
            'sri_state' => null,
            'message' => $message,
            'raw' => null,
        ];
    }

    private function objectToArray(object $obj): array
    {
        return json_decode(json_encode($obj), true);
    }
}
