<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Legacy P12 signer using the sri.jar Java application.
 * Temporary rollback signer. Activated credentials never enter this path.
 */
final class LegacyP12XmlSigner
{
    /** @return array{status: bool, response?: string} */
    public function sign(string $xmlPath, string $signedXmlPath, string $p12Path, string $password): array
    {
        $jarPath = public_path('firmador/sri.jar');
        foreach ([[$jarPath, 'Signer JAR'], [$p12Path, 'Signature file (.p12)'], [$xmlPath, 'Unsigned XML file']] as [$path, $label]) {
            if (! file_exists($path)) {
                return ['status' => false, 'response' => $label.' not found.'];
            }
        }

        $argumentFile = tempnam(sys_get_temp_dir(), 'sri-java-args-');
        if ($argumentFile === false || ! chmod($argumentFile, 0600)) {
            if (is_string($argumentFile) && is_file($argumentFile)) {
                unlink($argumentFile);
            }

            return ['status' => false, 'response' => 'No se pudo preparar el proceso de firma.'];
        }

        try {
            $arguments = ['-jar', $jarPath, $p12Path, $password, $xmlPath, dirname($signedXmlPath), basename($signedXmlPath)];
            if (file_put_contents($argumentFile, $this->buildJavaArgumentFile($arguments), LOCK_EX) === false) {
                return ['status' => false, 'response' => 'No se pudo preparar el proceso de firma.'];
            }
            $process = new Process(['java', '@'.$argumentFile]);
            $process->setTimeout(120);
            $process->run();
            $output = $process->getOutput().$process->getErrorOutput();
        } finally {
            if (is_file($argumentFile)) {
                unlink($argumentFile);
            }
        }

        Log::warning('sri.legacy_signer.used', ['output_length' => strlen($output)]);
        if (str_contains($output, 'Nombre del archivo salido')) {
            return ['status' => true];
        }

        return ['status' => false, 'response' => str_replace($password, '[redacted]', $output)];
    }

    /** @param array<int, string> $arguments */
    private function buildJavaArgumentFile(array $arguments): string
    {
        return implode("\n", array_map(
            static fn (string $argument): string => '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $argument).'"',
            $arguments,
        ))."\n";
    }
}
