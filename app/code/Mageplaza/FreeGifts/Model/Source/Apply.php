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

namespace Mageplaza\FreeGifts\Model\Source;

/**
 * Class Apply
 * @package Mageplaza\FreeGifts\Model
 */
class Apply extends OptionArray
{
    const CART = 'cart';
    const ITEM = 'item';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::CART => __('Cart Rule'),
            self::ITEM => __('Item Rule'),
        ];
    }
}
