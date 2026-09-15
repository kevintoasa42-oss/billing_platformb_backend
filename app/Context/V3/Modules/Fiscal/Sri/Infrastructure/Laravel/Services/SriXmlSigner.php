<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\ElectronicSignature;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\DocumentKeyCustodianInterface;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\XmlSigningServiceInterface;
use DOMDocument;
use DOMElement;
use RuntimeException;

/**
 * Signs XML documents with XAdES-EPES using DOMDocument.
 * Delegates the actual cryptographic signing to the DocumentKeyCustodianInterface.
 */
final class SriXmlSigner implements XmlSigningServiceInterface
{
    private const DS_NS = 'http://www.w3.org/2000/09/xmldsig#';
    private const XADES_NS = 'http://uri.etsi.org/01903/v1.3.2#';

    public function __construct(
        private readonly DocumentKeyCustodianInterface $custodian,
    ) {}

    public function sign(string $xml, ElectronicSignature $credential): string
    {
        $this->assertCredentialReady($credential);

        $document = new DOMDocument;
        $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        $root = $document->documentElement;
        if ($root === null) {
            throw new RuntimeException('El XML no tiene raiz.', 422);
        }

        $signatureId = 'Signature';
        $signedInfoId = 'SignedInfo';
        $keyInfoId = 'KeyInfo';
        $signedPropertiesId = 'SignedProperties';
        $documentReferenceId = 'Reference';
        $signatureValueId = 'SignatureValue';

        $signature = $document->createElementNS(self::DS_NS, 'ds:Signature');
        $signature->setAttribute('Id', $signatureId);

        $signedInfo = $document->createElementNS(self::DS_NS, 'ds:SignedInfo');
        $signedInfo->setAttribute('Id', $signedInfoId);
        $canonicalMethod = $signedInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:CanonicalizationMethod'));
        $canonicalMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signatureMethod = $signedInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:SignatureMethod'));
        $signatureMethod->setAttribute('Algorithm', self::DS_NS.'rsa-sha1');

        $this->appendDocumentReference($document, $signedInfo, $documentReferenceId);
        $this->appendSignedPropertiesReference($document, $signedInfo, $signedPropertiesId, null);
        $this->appendKeyInfoReference($document, $signedInfo, $keyInfoId, null);

        $signature->appendChild($signedInfo);

        $signatureValue = $document->createElementNS(self::DS_NS, 'ds:SignatureValue');
        $signatureValue->setAttribute('Id', $signatureValueId);
        $signature->appendChild($signatureValue);

        $keyInfo = $document->createElementNS(self::DS_NS, 'ds:KeyInfo');
        $keyInfo->setAttribute('Id', $keyInfoId);
        $x509Data = $keyInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:X509Data'));
        $x509Certificate = $x509Data->appendChild($document->createElementNS(self::DS_NS, 'ds:X509Certificate'));
        $x509Certificate->nodeValue = $this->extractCertificateBase64((string) $credential->certificatePem);
        $signature->appendChild($keyInfo);

        // XAdES QualifyingProperties
        $object = $document->createElementNS(self::DS_NS, 'ds:Object');
        $qualifyingProperties = $object->appendChild($document->createElementNS(self::XADES_NS, 'xades:QualifyingProperties'));
        $qualifyingProperties->setAttribute('Target', '#'.$signatureId);
        $signedProperties = $qualifyingProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:SignedProperties'));
        $signedProperties->setAttribute('Id', $signedPropertiesId);
        $signedSignatureProperties = $signedProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:SignedSignatureProperties'));

        $signingTime = $signedSignatureProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:SigningTime'));
        $signingTime->nodeValue = gmdate('Y-m-d\TH:i:s\Z');

        $signingCertificate = $signedSignatureProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:SigningCertificate'));
        $cert = $signingCertificate->appendChild($document->createElementNS(self::XADES_NS, 'xades:Cert'));
        $certDigest = $cert->appendChild($document->createElementNS(self::XADES_NS, 'xades:CertDigest'));
        $certDigestMethod = $certDigest->appendChild($document->createElementNS(self::DS_NS, 'ds:DigestMethod'));
        $certDigestMethod->setAttribute('Algorithm', self::DS_NS.'sha1');
        $certDigestValue = $certDigest->appendChild($document->createElementNS(self::DS_NS, 'ds:DigestValue'));
        $certDigestValue->nodeValue = base64_encode(sha1($this->pemToDer((string) $credential->certificatePem), true));

        $signedDataObjectProperties = $signedProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:SignedDataObjectProperties'));
        $dataObjectFormat = $signedDataObjectProperties->appendChild($document->createElementNS(self::XADES_NS, 'xades:DataObjectFormat'));
        $dataObjectFormat->setAttribute('ObjectReference', '#'.$documentReferenceId);
        $description = $dataObjectFormat->appendChild($document->createElementNS(self::XADES_NS, 'xades:Description'));
        $description->nodeValue = 'contenido comprobante';
        $mimeType = $dataObjectFormat->appendChild($document->createElementNS(self::XADES_NS, 'xades:MimeType'));
        $mimeType->nodeValue = 'text/xml';

        $signature->appendChild($object);
        $root->appendChild($signature);

        // Compute signature
        $signedInfoBytes = $signedInfo->C14N(false, false);
        if ($signedInfoBytes === false) {
            throw new RuntimeException('No fue posible canonicalizar SignedInfo.', 500);
        }

        $result = $this->custodian->sign(
            (string) $credential->keyReference,
            $signedInfoBytes,
            $credential->keyVersion,
        );

        $signatureValue->nodeValue = base64_encode($result['signature']);

        $signedXml = $document->saveXML();
        if ($signedXml === false) {
            throw new RuntimeException('No fue posible serializar el XML firmado.', 500);
        }

        return $signedXml;
    }

    private function assertCredentialReady(ElectronicSignature $credential): void
    {
        if (! $credential->usesOpenBao() || ! $credential->keyReference || ! $credential->certificatePem) {
            throw new RuntimeException('La credencial no esta activa en el custodio seguro.', 422);
        }
    }

    private function appendDocumentReference(DOMDocument $document, DOMElement $signedInfo, string $referenceId): void
    {
        $reference = $signedInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:Reference'));
        $reference->setAttribute('Id', $referenceId);
        $reference->setAttribute('URI', '#comprobante');
        $transforms = $reference->appendChild($document->createElementNS(self::DS_NS, 'ds:Transforms'));
        $transform = $transforms->appendChild($document->createElementNS(self::DS_NS, 'ds:Transform'));
        $transform->setAttribute('Algorithm', self::DS_NS.'enveloped-signature');
        $this->appendDigest($document, $reference, $this->canonicalDocumentWithoutSignature($document));
    }

    private function appendKeyInfoReference(DOMDocument $document, DOMElement $signedInfo, string $id, ?DOMElement $keyInfo): void
    {
        $reference = $signedInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:Reference'));
        $reference->setAttribute('Id', $id.'-Reference');
        $reference->setAttribute('URI', '#'.$id);
        $canonical = $keyInfo !== null ? $keyInfo->C14N(false, false) : '';
        if ($canonical === false) {
            $canonical = '';
        }
        $this->appendDigest($document, $reference, $canonical);
    }

    private function appendSignedPropertiesReference(DOMDocument $document, DOMElement $signedInfo, string $id, ?DOMElement $properties): void
    {
        $reference = $signedInfo->appendChild($document->createElementNS(self::DS_NS, 'ds:Reference'));
        $reference->setAttribute('Id', $id.'-Reference');
        $reference->setAttribute('URI', '#'.$id);
        $reference->setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
        $canonical = $properties !== null ? $properties->C14N(false, false) : '';
        if ($canonical === false) {
            $canonical = '';
        }
        $this->appendDigest($document, $reference, $canonical);
    }

    private function appendDigest(DOMDocument $document, DOMElement $reference, string $canonical): void
    {
        $method = $reference->appendChild($document->createElementNS(self::DS_NS, 'ds:DigestMethod'));
        $method->setAttribute('Algorithm', self::DS_NS.'sha1');
        $value = $reference->appendChild($document->createElementNS(self::DS_NS, 'ds:DigestValue'));
        $value->nodeValue = base64_encode(sha1($canonical, true));
    }

    private function canonicalDocumentWithoutSignature(DOMDocument $document): string
    {
        $clone = clone $document;
        $signatures = $clone->getElementsByTagNameNS(self::DS_NS, 'Signature');
        while ($signatures->length > 0) {
            $sig = $signatures->item(0);
            $sig?->parentNode?->removeChild($sig);
        }
        $result = $clone->documentElement?->C14N(false, false);

        return $result === false ? '' : $result;
    }

    private function pemToDer(string $pem): string
    {
        $cleaned = preg_replace('/-----.*-----/', '', $pem);
        $cleaned = preg_replace('/\s+/', '', $cleaned);

        return base64_decode((string) $cleaned, true) ?: '';
    }

    private function extractCertificateBase64(string $pem): string
    {
        $cleaned = preg_replace('/-----.*-----/', '', $pem);
        $cleaned = preg_replace('/\s+/', '', $cleaned);

        return (string) $cleaned;
    }
}
