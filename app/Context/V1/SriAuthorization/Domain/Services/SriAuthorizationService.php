<?php

namespace App\Context\V1\SriAuthorization\Domain\Services;

/**
 * Queries the SRI authorization web service (AutorizacionComprobantesOffline).
 */
class SriAuthorizationService
{
    /**
     * Query the SRI for the authorization status of a document.
     *
     * @param  string  $accessKey  49-digit access key
     * @param  string  $environment  "1"=pruebas, "2"=produccion
     * @return array{status: bool, sri_state: ?string, message: ?string, authorization_date: ?string, raw: ?array}
     */
    public function query(string $accessKey, string $environment): array
    {
        $url = $this->getUrl($environment);

        try {
            $options = [
                'cache_wsdl' => 0,
                'trace' => 1,
                'connection_timeout' => 15,
                'stream_context' => stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ]),
            ];

            $client = new \SoapClient($url, $options);
            $result = $client->autorizacionComprobante(['claveAccesoComprobante' => $accessKey]);

            if (!$result || !property_exists($result, 'RespuestaAutorizacionComprobante')) {
                return $this->error('WS Error: No response from SRI');
            }

            $autorizacion = $result->RespuestaAutorizacionComprobante;

            if (!property_exists($autorizacion, 'autorizaciones')) {
                return $this->error('WS Error: No autorizaciones');
            }

            $autorizaciones = $autorizacion->autorizaciones;

            if (!property_exists($autorizaciones, 'autorizacion')) {
                return $this->error('WS Error: No autorizacion');
            }

            $auth = $autorizaciones->autorizacion;
            $estado = $auth->estado ?? null;

            if ($estado === 'AUTORIZADO') {
                return [
                    'status' => true,
                    'sri_state' => 'AUTORIZADO',
                    'message' => null,
                    'authorization_date' => $auth->fechaAutorizacion ?? null,
                    'raw' => $this->objectToArray($result),
                ];
            }

            // Not authorized - extract error messages
            $message = 'WS Error: No hay mensaje de respuesta';

            if (property_exists($auth, 'mensajes')) {
                $mensajes = $auth->mensajes;

                if (property_exists($mensajes, 'mensaje')) {
                    $msg = $mensajes->mensaje;

                    if (is_array($msg)) {
                        $message = $msg[0]->mensaje;
                    } else {
                        $message = $msg->mensaje;
                    }
                }
            }

            return [
                'status' => false,
                'sri_state' => $estado,
                'message' => $message,
                'authorization_date' => null,
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
        return config("sri.wsdl.authorization.{$key}");
    }

    private function error(string $message): array
    {
        return [
            'status' => false,
            'sri_state' => null,
            'message' => $message,
            'authorization_date' => null,
            'raw' => null,
        ];
    }

    private function objectToArray(object $obj): array
    {
        return json_decode(json_encode($obj), true);
    }
}
