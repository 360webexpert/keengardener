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

namespace Mageplaza\FreeGifts\Model\Api\Data;

use Magento\Framework\Model\AbstractExtensibleModel;
use Mageplaza\FreeGifts\Api\Data\FreeGiftItemInterface;

/**
 * Class FreeGiftData
 * @package Mageplaza\FreeGifts\Model\Api\Data
 */
class FreeGiftItem extends AbstractExtensibleModel implements FreeGiftItemInterface
{
    /**
     * @inheritDoc
     */
    public function getIsFreeGift()
    {
        return $this->getData(self::IS_FREE_GIFT);
    }

    /**
     * @inheritDoc
     */
    public function setIsFreeGift($value)
    {
        return $this->setData(self::IS_FREE_GIFT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getFreeGiftMessage()
    {
        return $this->getData(self::FREE_GIFT_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setFreeGiftMessage($value)
    {
        return $this->setData(self::FREE_GIFT_MESSAGE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRuleId($value)
    {
        return $this->setData(self::RULE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getAllowNotice()
    {
        return $this->getData(self::ALLOW_NOTICE);
    }

    /**
     * @inheritDoc
     */
    public function setAllowNotice($value)
    {
        return $this->setData(self::ALLOW_NOTICE, $value);
    }
}
