<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Observer;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use WeSupply\Toolbox\Helper\Data as WsHelper;

/**
 * Class ModifyCsp
 * @package WeSupply\Toolbox\Observer
 */
class ModifyCsp implements ObserverInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var WsHelper
     */
    private $helper;

    /**
     * @var string
     */
    private $cspHeaderType;

    /**
     * @param ProductMetadataInterface $productMetaData
     * @param WsHelper $wsHelper
     */
    public function __construct(
        ProductMetadataInterface $productMetaData,
        WsHelper $wsHelper
)
    {
        $this->productMetaData = $productMetaData;
        $this->helper = $wsHelper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var HttpResponse $response */
        $response = $observer->getEvent()->getData('response');

        if ($this->helper->weSupplyHasDomainAlias()) {
            $cspHeader = $this->getCspHeader($response);
            if (!$cspHeader) {
                // nothing to modify
                return $this;
            }

            $this->replaceCspHeader($cspHeader);
            $this->cspToString($cspHeader);

            $response->setHeader($this->cspHeaderType, $cspHeader, true);
        }

        return $this;
    }

    /**
     * @param $response
     * @return false
     */
    private function getCspHeader($response)
    {
        $this->cspHeaderType = 'Content-Security-Policy';
        $cspHeader = $response->getHeader($this->cspHeaderType);
        if (!$cspHeader) { // switch SCP type
            $this->cspHeaderType = 'Content-Security-Policy-Report-Only';
            $cspHeader = $response->getHeader($this->cspHeaderType);
        }

        return $cspHeader ?? false;
    }

    /**
     * @param $cspHeader
     */
    private function replaceCspHeader(&$cspHeader): void
    {
        $cspHeaderArr = explode(';', $cspHeader->getFieldValue());
        $cspFrame = $this->extractCsp($cspHeader);

        if (isset($cspFrame['value']) && isset($cspFrame['key'])) {
            $weSupplyFullDomain = trim($this->helper->getWesupplyFullDomain(), '/');
            if (strpos($cspFrame['value'], $weSupplyFullDomain) === false) {
                $pattern = '/(.frame-src*)([[:space:]])(.+$)/i';
                $replacement = '$1 ' . $weSupplyFullDomain . ' $3';
                $value = preg_replace($pattern, $replacement, $cspFrame['value'], -1 );

                $cspHeaderArr[$cspFrame['key']] = $value;
                $cspHeader = implode(';', $cspHeaderArr);
            }
        }
    }

    /**
     * @param $headerTag
     * @return array
     */
    private function extractCsp($headerTag): array
    {
        $frameSrcData = [];
        $headerVal = explode(';', $headerTag->getFieldValue());
        $frameSrc = array_filter($headerVal, function($value) {
            return strpos($value, 'frame-src') !== false;
        });

        if ($frameSrc) {
            $frameSrcData['key'] = (array_keys($frameSrc))[0];
            $frameSrcData['value'] = reset($frameSrc);
        }

        return $frameSrcData;
    }

    /**
     * @param $cspHeader
     */
    private function cspToString(&$cspHeader)
    {
        if (!is_string($cspHeader)) {
            $cspArr = explode(':', $cspHeader->toString());
            $cspHeader = end($cspArr);
        }
    }
}
