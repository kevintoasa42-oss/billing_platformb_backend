<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\ElectronicSignature;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\XmlSigningServiceInterface;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Orchestrates XML signing by choosing between OpenBao Transit and legacy P12.
 */
final class SriCredentialSigningService
{
    public function __construct(
        private readonly XmlSigningServiceInterface $openBaoSigner,
        private readonly LegacyP12XmlSigner $legacySigner,
    ) {}

    public function sign(string $xml, ElectronicSignature $credential): string
    {
        if ($credential->usesOpenBao()) {
            return $this->openBaoSigner->sign($xml, $credential);
        }

        if ($credential->canUseLegacySigner()) {
            return $this->signWithLegacyP12($xml, $credential);
        }

        throw new RuntimeException('La credencial no tiene un backend de firma configurado.', 422);
    }

    private function signWithLegacyP12(string $xml, ElectronicSignature $credential): string
    {
        if (! $credential->fileName || ! $credential->password) {
            throw new RuntimeException('La credencial legacy no tiene archivo P12 o password.', 422);
        }

        $p12Path = Storage::disk('local')->path($credential->fileName);
        $xmlPath = tempnam(sys_get_temp_dir(), 'sri-xml-');
        $signedXmlPath = tempnam(sys_get_temp_dir(), 'sri-signed-');

        try {
            file_put_contents($xmlPath, $xml);
            $result = $this->legacySigner->sign($xmlPath, $signedXmlPath, $p12Path, $credential->password);

            if (! $result['status']) {
                throw new RuntimeException('Firma legacy P12 fallida: '.($result['response'] ?? 'error desconocido'), 422);
            }

            $signedXml = file_get_contents($signedXmlPath);
            if ($signedXml === false) {
                throw new RuntimeException('No se pudo leer el XML firmado.', 500);
            }

            return $signedXml;
        } finally {
            if (is_string($xmlPath) && is_file($xmlPath)) {
                unlink($xmlPath);
            }
            if (is_string($signedXmlPath) && is_file($signedXmlPath)) {
                unlink($signedXmlPath);
            }
        }
    }
}
