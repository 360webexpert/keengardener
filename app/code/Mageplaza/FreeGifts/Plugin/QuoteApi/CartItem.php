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

use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class CartItem
 * @package Mageplaza\FreeGifts\Plugin\QuoteApi
 */
class CartItem extends AbstractCartItem
{
    /**
     * @param CartItemRepositoryInterface $subject
     * @param CartItemInterface[] $result
     *
     * @return CartItemInterface[] Array of items
     * @SuppressWarnings("Unused")
     */
    public function afterGetList(CartItemRepositoryInterface $subject, $result)
    {
        return $this->getList($result);
    }

    /**
     * @param CartItemRepositoryInterface $subject
     * @param CartItemInterface $result
     * @param CartItemInterface $cartItem
     *
     * @return CartItemInterface
     * @SuppressWarnings("Unused")
     */
    public function afterSave(CartItemRepositoryInterface $subject, $result, $cartItem)
    {
        return $this->save($result, $cartItem);
    }
}
