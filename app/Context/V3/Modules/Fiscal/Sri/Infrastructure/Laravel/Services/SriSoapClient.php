<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\SriSoapClientInterface;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapFault;
use Throwable;

/**
 * SRI SOAP client for reception (validarComprobante) and authorization (autorizacionComprobante).
 */
final class SriSoapClient implements SriSoapClientInterface
{
    public function receptionXml(string $signedXml, string $ambiente): array
    {
        try {
            $wsdl = (string) config("services.sri.wsdl.reception.{$ambiente}");
            $client = self::createClient($wsdl);

            $result = $client->validarComprobante(['xml' => base64_encode($signedXml)]);

            if (! $result || ! property_exists($result, 'RespuestaRecepcionComprobante')) {
                return ['status' => false, 'response' => 'WS Error: unexpected response structure'];
            }

            $reception = $result->RespuestaRecepcionComprobante;
            $state = (string) ($reception->estado ?? 'ERROR');

            return [
                'status' => $state === 'RECIBIDA',
                'estado' => $state,
                'messages' => $state === 'RECIBIDA' ? [] : self::extractReceptionMessages($reception),
            ];
        } catch (Throwable $error) {
            Log::error('SriSoapClient::receptionXml error', ['exception' => $error::class]);

            return ['status' => false, 'response' => 'No fue posible conectar con recepcion SRI.'];
        }
    }

    public function authorize(string $claveAcceso, string $ambiente): array
    {
        try {
            $wsdl = (string) config("services.sri.wsdl.authorization.{$ambiente}");
            $client = self::createClient($wsdl);

            $result = $client->autorizacionComprobante(['claveAccesoComprobante' => $claveAcceso]);

            if (! $result || ! property_exists($result, 'RespuestaAutorizacionComprobante')) {
                return [
                    'status' => false,
                    'estado' => 'ERROR',
                    'response' => 'No fue posible consultar la autorizacion del SRI.',
                ];
            }

            $autorizacion = $result->RespuestaAutorizacionComprobante;

            if (! property_exists($autorizacion, 'autorizaciones') || ! property_exists($autorizacion->autorizaciones, 'autorizacion')) {
                return [
                    'status' => false,
                    'estado' => 'ERROR',
                    'response' => 'El SRI no devolvio autorizaciones.',
                ];
            }

            $auth = $autorizacion->autorizaciones->autorizacion;
            if (is_array($auth)) {
                $auth = $auth[0];
            }

            $state = (string) ($auth->estado ?? 'UNKNOWN');
            Log::info('SRI authorization query completed.', [
                'environment' => $ambiente,
                'state' => $state,
            ]);

            if ($state === 'AUTORIZADO') {
                return [
                    'status' => true,
                    'estado' => 'AUTORIZADO',
                    'fechaAutorizacion' => $auth->fechaAutorizacion ?? null,
                    'numeroAutorizacion' => $auth->numeroAutorizacion ?? null,
                    'comprobante' => $auth->comprobante ?? null,
                ];
            }

            return [
                'status' => false,
                'estado' => $state,
                'messages' => self::extractAuthorizationMessages($auth),
            ];
        } catch (SoapFault $e) {
            return self::authorizationTransportFailure($ambiente, $e);
        } catch (Throwable $e) {
            return self::authorizationTransportFailure($ambiente, $e);
        }
    }

    private static function createClient(string $wsdl): SoapClient
    {
        return new SoapClient($wsdl, [
            'cache_wsdl' => WSDL_CACHE_NONE,
            'trace' => false,
            'connection_timeout' => 15,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ]),
        ]);
    }

    /** @return list<string> */
    private static function extractReceptionMessages(object $reception): array
    {
        $messages = [];
        if (property_exists($reception, 'comprobantes') && property_exists($reception->comprobantes, 'comprobante')) {
            $comprobantes = $reception->comprobantes->comprobante;
            if (! is_array($comprobantes)) {
                $comprobantes = [$comprobantes];
            }
            foreach ($comprobantes as $comprobante) {
                if (property_exists($comprobante, 'mensajes') && property_exists($comprobante->mensajes, 'mensaje')) {
                    $mensajes = $comprobante->mensajes->mensaje;
                    if (! is_array($mensajes)) {
                        $mensajes = [$mensajes];
                    }
                    foreach ($mensajes as $mensaje) {
                        $messages[] = (string) ($mensaje->mensaje ?? '').': '.(string) ($mensaje->informacionAdicional ?? '');
                    }
                }
            }
        }

        return $messages;
    }

    /** @return list<string> */
    private static function extractAuthorizationMessages(object $auth): array
    {
        $messages = [];
        if (property_exists($auth, 'mensajes') && property_exists($auth->mensajes, 'mensaje')) {
            $mensajes = $auth->mensajes->mensaje;
            if (! is_array($mensajes)) {
                $mensajes = [$mensajes];
            }
            foreach ($mensajes as $mensaje) {
                $messages[] = (string) ($mensaje->mensaje ?? '').': '.(string) ($mensaje->informacionAdicional ?? '');
            }
        }

        return $messages;
    }

    private static function authorizationTransportFailure(string $ambiente, Throwable $e): array
    {
        Log::error('SRI authorization transport failure', [
            'environment' => $ambiente,
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);

        return [
            'status' => false,
            'estado' => 'ERROR',
            'error_code' => 'sri_authorization_unavailable',
            'response' => 'No fue posible consultar la autorizacion del SRI.',
        ];
    }
}
