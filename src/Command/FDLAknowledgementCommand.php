<?php

declare(strict_types=1);

namespace Cube43\Component\Ebics\Command;

use Cube43\Component\Ebics\Crypt\SignQuery;
use Cube43\Component\Ebics\EbicsServerCaller;
use Cube43\Component\Ebics\RenderXml;

class FDLAknowledgementCommand
{
    private readonly RenderXml $renderXml;
    private readonly EbicsServerCaller $ebicsServerCaller;
    private readonly SignQuery $signQuery;

    public function __construct(
        EbicsServerCaller|null $ebicsServerCaller = null,
        RenderXml|null $renderXml = null,
        SignQuery|null $signQuery = null,
    ) {
        $this->ebicsServerCaller = $ebicsServerCaller ?? new EbicsServerCaller();
        $this->renderXml         = $renderXml ?? new RenderXml();
        $this->signQuery         = $signQuery ?? new SignQuery();
    }

    public function __invoke(FDLResponse $FDLResponse): void
    {
        $search = [
            '{{TransactionID}}' => $FDLResponse->serverResponse->getLastNodeValue('TransactionID'),
            '{{HostID}}' => $FDLResponse->bank->getHostId(),
        ];

        $this->ebicsServerCaller->__invoke(
            $this->signQuery->__invoke(
                $this->renderXml->__invoke($search, $FDLResponse->bank->getVersion(), 'FDL_acknowledgement.xml'),
                $FDLResponse->keyRing,
                $FDLResponse->bank->getVersion(),
            )->getFormattedContent(),
            $FDLResponse->bank,
            ['0000000', '011000'],
        );
    }
}
