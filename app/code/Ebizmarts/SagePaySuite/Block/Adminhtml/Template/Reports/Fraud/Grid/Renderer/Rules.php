<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\Template\Reports\Fraud\Grid\Renderer;

use Ebizmarts\SagePaySuite\Helper\AdditionalInformation;

/**
 * grid block action item renderer
 */
class Rules extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
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
        $serializedData = $row->getData('additional_information');
        $additionalInfo = $this->information->getUnserializedData($serializedData);

        return $this->processAdditionalInformation($additionalInfo);
    }

    /**
     * @param array $info
     * @return string
     */
    private function processAdditionalInformation(array $info)
    {
        if (isset($info['fraudrules'])) {
            $rules = $info['fraudrules'];

            return $this->processRules($rules);
        }
    }

    /**
     * @param $rules
     * @return string
     */
    private function processRules($rules)
    {
        if (!\is_array($rules)) {
            return $rules;
        }

        return $this->processMultipleRulesData($rules);
    }

    /**
     * @param $rules
     * @return string
     */
    private function processMultipleRulesData($rules)
    {
        $return = '<ul>';
        foreach ($rules as $rule) {
            $return .= __('<li>%1 <strong>(score: %2)</strong></li>', $rule['description'], $rule['score']);
        }
        $return .= '</ul>';

        return $return;
    }
}
