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

namespace Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;

/**
 * Class EditAction
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer
 */
class EditAction extends Text
{
    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        return '<a href="javascript:void(0)"
            data-gift_id="' . $row->getData('entity_id') . '"
            data-discount_type="' . $row->getData('discount_type') . '"
            data-gift_price="' . $row->getData('gift_price') . '"
            data-product_price="' . (float)$row->getData('price') . '"
            data-free_shipping="' . $row->getData('free_shipping') . '"
            class="mpfreegifts-grid-item-edit-action action-configure">'
            . __('Edit')
            . '</a>';
    }
}
