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

use Magento\Framework\DataObject;

/**
 * Class FreeShipping
 * @package Mageplaza\FreeGifts\Block\Adminhtml\Rule\Grid\Renderer
 */
class FreeShipping extends AbstractRenderer
{
    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $giftId = (int)$row->getId();
        $gifts = $this->getGiftArray();

        if (isset($gifts[$giftId])) {
            $freeShipping = (int)$gifts[$giftId]['free_ship'];
            $row->setData('free_shipping', $freeShipping);

            return $freeShipping ? __('Yes') : __('No');
        }

        return '';
    }
}
