<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Ebizmarts\SagePaySuite\Helper\AdditionalInformation;

/**
 * grid block action item renderer
 */
class Provider extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    /** @var AdditionalInformation */
    private $information;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Ebizmarts\SagePaySuite\Helper\AdditionalInformation $information
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        AdditionalInformation $information,
        array $data = []
    ) {
        $this->information = $information;
        parent::__construct($context, $data);
    }

    /**
     * Render grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $additionalInfo = $this->information->getUnserializedData($row->getData("additional_information"));

        $provider = isset($additionalInfo["fraudprovidername"]) ? $additionalInfo["fraudprovidername"] : "";

        if ($provider === "ReD") {
            $html = '<img style="height: 20px;" src="';
            $html .= $this->getFraudProviderLogo('red') . '">';
        } else {
            $html = '<span><img style="height: 20px;vertical-align: text-top;" src="' . $this->getFraudProviderLogo('t3m') . '"> T3M</span>';
        }

        return $html;
    }

    private function getFraudProviderLogo($name)
    {
        return $this->getViewFileUrl('Ebizmarts_SagePaySuite::images/' . $name . '_logo.png');
    }
}
