<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Adapters;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\DocumentKeyCustodianInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OpenBao Transit signing client adapter.
 * Signs data via the OpenBao Transit secret engine without exposing private keys.
 */
final class OpenBaoTransitSigningClient implements DocumentKeyCustodianInterface
{
    private ?string $token = null;

    public function sign(string $reference, string $data, ?int $version = null): array
    {
        $address = (string) config('services.openbao.address');
        if ($address === '') {
            throw new RuntimeException('OpenBao no esta configurado (OPENBAO_ADDR).', 503);
        }

        $token = $this->resolveToken();
        $mount = (string) config('services.openbao.transit_mount', 'transit');

        $payload = [
            'input' => base64_encode($data),
            'hash_algorithm' => 'SHA1',
        ];
        if ($version !== null) {
            $payload['key_version'] = $version;
        }

        $response = $this->request($token)
            ->post("{$address}/v1/{$mount}/sign/{$reference}", $payload);

        if (! $response->successful()) {
            Log::error('OpenBao sign failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new RuntimeException('OpenBao no pudo firmar los datos.', 503);
        }

        $signatureB64 = (string) $response->json('data.signature', '');
        $signatureB64 = preg_replace('/^vault:v\d+:/', '', $signatureB64) ?? $signatureB64;
        $signature = base64_decode($signatureB64, true);
        if ($signature === false) {
            throw new RuntimeException('OpenBao devolvio una firma invalida.', 503);
        }

        $usedVersion = (int) $response->json('data.key_version', $version ?? 1);

        return [
            'reference' => $reference,
            'version' => $usedVersion,
            'signature' => $signature,
        ];
    }

    private function resolveToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $credentialsDir = (string) config('services.openbao.credentials_directory');
        $tokenFile = rtrim($credentialsDir, '/').'/token';
        if (! is_file($tokenFile)) {
            throw new RuntimeException('OpenBao token no encontrado.', 503);
        }

        $token = file_get_contents($tokenFile);
        if ($token === false || trim($token) === '') {
            throw new RuntimeException('OpenBao token vacio.', 503);
        }

        $this->token = trim($token);

        return $this->token;
    }

    private function request(string $token): PendingRequest
    {
        $timeout = (int) config('services.openbao.timeout_seconds', 10);
        $connectTimeout = (int) config('services.openbao.connect_timeout_seconds', 3);

        return Http::withToken($token)
            ->timeout($timeout)
            ->connectTimeout($connectTimeout);
    }
}
