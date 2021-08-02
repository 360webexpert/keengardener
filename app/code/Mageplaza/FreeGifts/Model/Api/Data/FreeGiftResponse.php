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
use Mageplaza\FreeGifts\Api\Data\FreeGiftResponseInterface;

/**
 * Class FreeGiftResponse
 * @package Mageplaza\FreeGifts\Model\Api\Data
 */
class FreeGiftResponse extends AbstractExtensibleModel implements FreeGiftResponseInterface
{
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setMessage($value)
    {
        return $this->setData(self::MESSAGE, $value);
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
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($value)
    {
        return $this->setData(self::QUOTE_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteItemId()
    {
        return $this->getData(self::QUOTE_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteItemId($value)
    {
        return $this->setData(self::QUOTE_ITEM_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductGiftId()
    {
        return $this->getData(self::PRODUCT_GIFT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductGiftId($value)
    {
        return $this->setData(self::PRODUCT_GIFT_ID, $value);
    }
}
