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
use Mageplaza\FreeGifts\Api\Data\FreeGiftButtonInterface;

/**
 * Class FreeGiftButton
 * @package Mageplaza\FreeGifts\Model\Api\Data
 */
class FreeGiftButton extends AbstractExtensibleModel implements FreeGiftButtonInterface
{
    /**
     * @inheritDoc
     */
    public function getIsShowButton()
    {
        return $this->getData(self::IS_SHOW_BUTTON);
    }

    /**
     * @inheritDoc
     */
    public function setIsShowButton($value)
    {
        return $this->setData(self::IS_SHOW_BUTTON, $value);
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
    public function getButtonLabel()
    {
        return $this->getData(self::BUTTON_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function setButtonLabel($value)
    {
        return $this->setData(self::BUTTON_LABEL, $value);
    }

    /**
     * @inheritDoc
     */
    public function getButtonColor()
    {
        return $this->getData(self::BUTTON_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setButtonColor($value)
    {
        return $this->setData(self::BUTTON_COLOR, $value);
    }

    /**
     * @inheritDoc
     */
    public function getTextColor()
    {
        return $this->getData(self::TEXT_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setTextColor($value)
    {
        return $this->setData(self::TEXT_COLOR, $value);
    }
}
