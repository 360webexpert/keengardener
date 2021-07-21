<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 3:48 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiTransactionResultCard extends AbstractExtensibleObject implements PiTransactionResultCardInterface
{

    /**
     * @inheritDoc
     */
    public function getCardType()
    {
        return $this->_get(self::CARD_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setCardType($ccType)
    {
        $this->setData(self::CARD_TYPE, $ccType);
    }

    /**
     * @inheritDoc
     */
    public function getLastFourDigits()
    {
        return $this->_get(self::LAST_FOUR_DIGITS);
    }

    /**
     * @inheritDoc
     */
    public function setLastFourDigits($digits)
    {
        $this->setData(self::LAST_FOUR_DIGITS, $digits);
    }

    /**
     * @inheritDoc
     */
    public function getExpiryDate()
    {
        return $this->_get(self::EXPIRY_DATE);
    }

    public function getExpiryMonth()
    {
        return substr($this->getExpiryDate(), 0, 2);
    }

    public function getExpiryYear()
    {
        return substr($this->getExpiryDate(), 2, 2);
    }

    /**
     * @inheritDoc
     */
    public function setExpiryDate($date)
    {
        $this->setData(self::EXPIRY_DATE, $date);
    }

    /**
     * @inheritDoc
     */
    public function getCardIdentifier()
    {
        return $this->_get(self::CARD_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setCardIdentifier($cardId)
    {
        $this->setData(self::CARD_IDENTIFIER, $cardId);
    }

    /**
     * @inheritDoc
     */
    public function getIsReusable()
    {
        return $this->_get(self::REUSABLE);
    }

    /**
     * @inheritDoc
     */
    public function setIsReusable($usable)
    {
        $this->setData(self::REUSABLE, $usable);
    }
}
