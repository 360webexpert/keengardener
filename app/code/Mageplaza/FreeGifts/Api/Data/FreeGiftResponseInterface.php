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

namespace Mageplaza\FreeGifts\Api\Data;

/**
 * Interface FreeGiftResponseInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface FreeGiftResponseInterface
{
    const STATUS          = 'status';
    const MESSAGE         = 'message';
    const RULE_ID         = 'rule_id';
    const QUOTE_ID        = 'quote_id';
    const QUOTE_ITEM_ID   = 'quote_item_id';
    const PRODUCT_GIFT_ID = 'product_gift_id';
    const STATUS_ERROR    = 'error';
    const STATUS_SUCCESS  = 'success';

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage($value);

    /**
     * @return string
     */
    public function getRuleId();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setRuleId($value);

    /**
     * @return string
     */
    public function getQuoteId();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setQuoteId($value);

    /**
     * @return string
     */
    public function getQuoteItemId();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setQuoteItemId($value);

    /**
     * @return string
     */
    public function getProductGiftId();

    /**
     * @param string|int $value
     *
     * @return $this
     */
    public function setProductGiftId($value);
}
