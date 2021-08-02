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

namespace Mageplaza\FreeGifts\Plugin\QuoteApi;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;

/**
 * Class GuestCartItem
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
class GuestCartItem extends AbstractCartItem
{
    /**
     * @param GuestCartItemRepositoryInterface $subject
     * @param CartItemInterface[] $result
     *
     * @return CartItemInterface[] Array of items.
     * @SuppressWarnings("Unused")
     */
    public function afterGetList(GuestCartItemRepositoryInterface $subject, $result)
    {
        return $this->getList($result);
    }
}
