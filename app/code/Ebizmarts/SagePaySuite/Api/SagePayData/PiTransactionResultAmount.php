<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/26/17
 * Time: 11:34 AM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiTransactionResultAmount extends AbstractExtensibleObject implements PiTransactionResultAmountInterface
{

    /**
     * @inheritDoc
     */
    public function getTotalAmount()
    {
        return $this->_get(self::TOTAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setTotalAmount($amount)
    {
        $this->setData(self::TOTAL_AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getSaleAmount()
    {
        return $this->_get(self::SALE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setSaleAmount($amount)
    {
        $this->setData(self::SALE_AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getSurchargeAmount()
    {
        return $this->_get(self::SURCHARGE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setSurchargeAmount($amount)
    {
        $this->setData(self::SURCHARGE_AMOUNT, $amount);
    }
}
