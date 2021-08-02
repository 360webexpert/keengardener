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
 * Interface FreeGiftDataInterface
 * @package Mageplaza\FreeGifts\Api\Data
 */
interface FreeGiftItemInterface
{
    const IS_FREE_GIFT      = 'is_free_gift';
    const FREE_GIFT_MESSAGE = 'free_gift_message';
    const RULE_ID           = 'rule_id';
    const ALLOW_NOTICE      = 'allow_notice';

    /**
     * @return bool
     */
    public function getIsFreeGift();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsFreeGift($value);

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
    public function getFreeGiftMessage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFreeGiftMessage($value);

    /**
     * @return bool
     */
    public function getAllowNotice();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setAllowNotice($value);
}
