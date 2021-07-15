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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\System;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class FeatureDisplay
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\System
 */
class FeatureDisplay extends Field
{
    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = '<div class="control-value" style="padding-top: 8px">';
        $html .= '<p>' . __('Use following code to show featured brand block in any place which you want.') . '</p>';

        $html .= '<strong>' . __('CMS Page/Static Block') . '</strong><br />';
        $html .= '<pre style="background-color: #f5f5dc"><code>{{block class="Mageplaza\Shopbybrand\Block\Brand\Featured"}}</code></pre>';

        $html .= '<strong>' . __('Template .phtml file') . '</strong><br />';
        $html .= '<pre style="background-color: #f5f5dc"><code>' . $this->_escaper->escapeHtml('<?php echo $block->getLayout()->createBlock("Mageplaza\Shopbybrand\Block\Brand\Featured")->toHtml();?>') . '</code></pre>';

        $html .= '<strong>' . __('Layout file') . '</strong><br />';
        $html .= '<pre style="background-color: #f5f5dc"><code>' . $this->_escaper->escapeHtml('<block class="Mageplaza\Shopbybrand\Block\Brand\Featured" name="featured_brand" />') . '</code></pre>';

        $html .= '</div>';

        return $html;
    }
}
