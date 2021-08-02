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

namespace Mageplaza\FreeGifts\Api;

/**
 * Interface ProductGiftInterface
 * @package Mageplaza\FreeGifts\Api
 */
interface ProductGiftInterface
{
    /**
     * @return bool
     */
    public function getCollectTotals();

    /**
     * @param bool $value
     * @return $this
     */
    public function setCollectTotals($value);

    /**
     * @param string $sku
     * @return \Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface
     */
    public function getGiftsByProductSku($sku);

    /**
     * @param string $itemId
     * @return \Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface
     */
    public function getGiftsByQuoteItemId($itemId);

    /**
     * @param string $cartId
     * @return \Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface
     */
    public function getGiftsByQuoteId($cartId);

    /**
     * @param string $quoteId
     * @param string $itemId
     * @return \Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface|bool
     */
    public function deleteGiftByQuoteItemId($quoteId, $itemId);

    /**
     * @param \Mageplaza\FreeGifts\Api\Data\AddGiftItemInterface $giftItem
     * @return \Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface
     */
    public function addGift(\Mageplaza\FreeGifts\Api\Data\AddGiftItemInterface $giftItem);

    /**
     * @return \Mageplaza\FreeGifts\Api\Data\ConfigInterface
     */
    public function getConfig();
}
