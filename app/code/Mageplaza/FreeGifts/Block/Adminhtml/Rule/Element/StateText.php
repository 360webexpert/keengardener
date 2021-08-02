<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_FreeGifts
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\FreeGifts\Helper\Rule as HelperRule;

/**
 * Class StateText
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Element
 */
class StateText extends AbstractElement
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $state = $this->getDataByKey('text') ?: $this->getDataByKey('stateText');
        $html = '<div></div>';

        switch ($state) {
            case HelperRule::STATE_RUNNING:
                $html = '<div id="rule_state" class="control-value admin__field-value mpfreegifts-state-running">';
                $html .= __('Running');
                $html .= '</div>';
                break;
            case HelperRule::STATE_SCHEDULE:
                $html = '<div id="rule_state" class="control-value admin__field-value mpfreegifts-state-schedule">';
                $html .= __('Schedule');
                $html .= '</div>';
                break;
            case HelperRule::STATE_FINISHED:
                $html = '<div id="rule_state" class="control-value admin__field-value mpfreegifts-state-finished">';
                $html .= __('Finished');
                $html .= '</div>';
                break;
        }

        return $html;
    }
}
