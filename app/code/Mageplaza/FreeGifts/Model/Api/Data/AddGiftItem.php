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
use Magento\Quote\Api\Data\ProductOptionInterface;
use Mageplaza\FreeGifts\Api\Data\AddGiftItemInterface;

/**
 * Class AddGiftData
 * @package Mageplaza\FreeGifts\Model\Api\Data
 */
class AddGiftItem extends AbstractExtensibleModel implements AddGiftItemInterface
{
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
    public function getGiftId()
    {
        return $this->getData(self::GIFT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setGiftId($value)
    {
        return $this->setData(self::GIFT_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductOption()
    {
        return $this->getData(self::PRODUCT_OPTION);
    }

    /**
     * @inheritDoc
     */
    public function setProductOption(ProductOptionInterface $value)
    {
        return $this->setData(self::PRODUCT_OPTION, $value);
    }
}
