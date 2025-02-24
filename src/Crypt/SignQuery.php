<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Crypt;

use Cube43\Component\Ebics\DOMDocument;
use Cube43\Component\Ebics\KeyRing;
use Cube43\Component\Ebics\Version;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Exception;
use RuntimeException;

use function assert;
use function base64_encode;
use function hash;
use function sprintf;
use function trim;

class SignQuery
{
    private static string $canonicalizationPath            = '//AuthSignature/*';
    private static string $signaturePath                   = "//*[@authenticate='true']";
    private static string $signatureMethodAlgorithm        = 'sha256';
    private static string $digestMethodAlgorithm           = 'sha256';
    private static string $canonicalizationMethodAlgorithm = 'REC-xml-c14n-20010315';
    private static string $digestTransformAlgorithm        = 'REC-xml-c14n-20010315';

    private readonly EncrytSignatureValueWithUserPrivateKey $cryptStringWithPasswordAndCertificat;

    public function __construct(
        EncrytSignatureValueWithUserPrivateKey|null $cryptStringWithPasswordAndCertificat = null,
    ) {
        $this->cryptStringWithPasswordAndCertificat = $cryptStringWithPasswordAndCertificat ?? new EncrytSignatureValueWithUserPrivateKey();
    }

    public function __invoke(DOMDocument $request, KeyRing $keyRing, Version $version): DOMDocument
    {
        $request = new DOMDocument($request->toString());
        // Add AuthSignature to request.
        $xmlAuthSignature = $request->createElement('AuthSignature');

        $xmlRequestHeader = self::safeItem($request, "//*[local-name()='header']");

        //$xmlRequestHeader = self::safeItem($request->prepareXPath()->query('//header', null, false));

        $this->insertAfter($xmlAuthSignature, $xmlRequestHeader);

        // Add ds:SignedInfo to AuthSignature.
        $xmlSignedInfo = $request->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignedInfo');
        $xmlAuthSignature->appendChild($xmlSignedInfo);

        // Add ds:CanonicalizationMethod to ds:SignedInfo.
        $xmlCanonicalizationMethod = $request->createElement('ds:CanonicalizationMethod');
        $xmlCanonicalizationMethod->setAttribute(
            'Algorithm',
            sprintf('http://www.w3.org/TR/2001/%s', self::$canonicalizationMethodAlgorithm),
        );
        $xmlSignedInfo->appendChild($xmlCanonicalizationMethod);

        // Add ds:SignatureMethod to ds:SignedInfo.
        $xmlSignatureMethod = $request->createElement('ds:SignatureMethod');
        $xmlSignatureMethod->setAttribute(
            'Algorithm',
            sprintf('http://www.w3.org/2001/04/xmldsig-more#rsa-%s', self::$signatureMethodAlgorithm),
        );
        $xmlSignedInfo->appendChild($xmlSignatureMethod);

        // Add ds:Reference to ds:SignedInfo.
        $xmlReference = $request->createElement('ds:Reference');
        $xmlReference->setAttribute('URI', sprintf('#xpointer(%s)', self::$signaturePath));
        $xmlSignedInfo->appendChild($xmlReference);

        // Add ds:Transforms to ds:Reference.
        $xmlTransforms = $request->createElement('ds:Transforms');
        $xmlReference->appendChild($xmlTransforms);

        // Add ds:Transform to ds:Transforms.
        $xmlTransform = $request->createElement('ds:Transform');
        $xmlTransform->setAttribute('Algorithm', sprintf('http://www.w3.org/TR/2001/%s', self::$digestTransformAlgorithm));
        $xmlTransforms->appendChild($xmlTransform);

        // Add ds:DigestMethod to ds:Reference.
        $xmlDigestMethod = $request->createElement('ds:DigestMethod');
        $xmlDigestMethod->setAttribute(
            'Algorithm',
            sprintf('http://www.w3.org/2001/04/xmlenc#%s', self::$digestMethodAlgorithm),
        );
        $xmlReference->appendChild($xmlDigestMethod);

        // Add ds:DigestValue to ds:Reference.
        $xmlDigestValue      = $request->createElement('ds:DigestValue');
        $canonicalizedHeader = $this->calculateC14N(
            $this->prepareH00XXPath($request, $version),
            self::$signaturePath,
            self::$canonicalizationMethodAlgorithm,
        );

        $canonicalizedHeaderHash = hash(self::$digestMethodAlgorithm, $canonicalizedHeader, true);
        $digestValueNodeValue    = base64_encode($canonicalizedHeaderHash);

        $xmlDigestValue->nodeValue = $digestValueNodeValue;
        $xmlReference->appendChild($xmlDigestValue);

        // Add ds:SignatureValue to AuthSignature.
        $xmlSignatureValue       = $request->createElement('ds:SignatureValue');
        $canonicalizedSignedInfo = $this->calculateC14N(
            $this->prepareH00XXPath($request, $version),
            self::$canonicalizationPath,
            self::$canonicalizationMethodAlgorithm,
        );

        $canonicalizedSignedInfoHash          =  hash(self::$signatureMethodAlgorithm, $canonicalizedSignedInfo, true);
        $canonicalizedSignedInfoHashEncrypted = $this->cryptStringWithPasswordAndCertificat->__invoke(
            $keyRing,
            $keyRing->getUserCertificateX()->getPrivateKey(),
            $canonicalizedSignedInfoHash,
        );
        $signatureValueNodeValue              = base64_encode($canonicalizedSignedInfoHashEncrypted);

        $xmlSignatureValue->nodeValue = $signatureValueNodeValue;
        $xmlAuthSignature->appendChild($xmlSignatureValue);

        return $request;
    }

    protected function insertAfter(DOMNode $newNode, DOMNode $afterNode): void
    {
        $nextSibling = $afterNode->nextSibling;
        if ($newNode !== $nextSibling) {
            $afterNode->parentNode->insertBefore($newNode, $nextSibling);
        } else {
            $afterNode->parentNode->appendChild($newNode);
        }
    }

    public static function safeItem(DOMDocument $document, string $query): DOMNode
    {
        $domNodeList = $document->prepareXPath()->query($query);

        if ($domNodeList === false) {
            throw new RuntimeException(sprintf('Unable to find "%s" in "%s"', $query, $document->toString()));
        }

        $domNode = $domNodeList->item(0);
        if ($domNode === null) {
            throw new RuntimeException(sprintf('Unable to find "%s" in "%s"', $query, $document->toString()));
        }

        return $domNode;
    }

    private function calculateC14N(
        DOMXPath $xpath,
        string $path = '/',
        string $algorithm = 'REC-xml-c14n-20010315',
    ): string {
        if ($algorithm !== 'REC-xml-c14n-20010315') {
            throw new Exception(sprintf('Define algo for %s', $algorithm));
        }

        $nodes  = $xpath->query($path);
        $result = '';

        if (! ($nodes instanceof DOMNodeList)) {
            return $result;
        }

        foreach ($nodes as $node) {
            assert($node instanceof DOMNode);
            $result .= $node->C14N(false, false);
        }

        return trim($result);
    }

    protected function prepareH00XXPath(DOMDocument $xml, Version $version): DOMXPath
    {
        return $this->prepareH004XPathV2($xml, $version);
    }

    protected function prepareH004XPathV2(DOMDocument $xml, Version $version): DOMXPath
    {
        $xpath = $xml->prepareXPath();
        if ($version->is(Version::v24())) {
            $xpath->registerNamespace('H003', 'http://www.ebics.org/H003');
            $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        }

        if ($version->is(Version::v25())) {
            $xpath->registerNamespace('H004', 'urn:org:ebics:H004');
            $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        }

        if ($version->is(Version::v30())) {
            $xpath->registerNamespace('H005', 'urn:org:ebics:H005');
            $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        }

        return $xpath;
    }
}
