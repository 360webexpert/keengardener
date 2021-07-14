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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Block\Adminhtml\Redirect\Render;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Button
 * @package Mageplaza\Redirects\Block\Adminhtml\Redirect\Render
 */
class Button extends AbstractElement
{
    /**
     * Get element html
     * @return string
     */
    public function getElementHtml()
    {
        $html   = '';
        $htmlId = $this->getHtmlId();
        $html   .= '<input  type="button" id="' . $htmlId . '" name="' . $this->getName() . '" ' . $this->_getUiId() . ' value="' .
            $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';
        $html   .= '<input  type="button" id="' . $htmlId . '" name="' . $this->getName() . '" ' . $this->_getUiId() . ' value="' .
            __('Cancel') . '" class="action-primary cancel-redirect" style="margin-left: 30px;"/>';

        return $html;
    }
}
