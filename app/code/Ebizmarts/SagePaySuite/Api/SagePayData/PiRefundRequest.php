<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiRefundRequest extends AbstractExtensibleObject implements PiRefundRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getTransactionType()
    {
        return $this->_get(self::TRANSACTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionType()
    {
        $this->setData(self::TRANSACTION_TYPE, 'Refund');
    }

    /**
     * @inheritDoc
     */
    public function getReferenceTransactionId()
    {
        return $this->_get(self::REF_TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setReferenceTransactionId($transactionId)
    {
        $this->setData(self::REF_TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getVendorTxCode()
    {
        return $this->_get(self::VENDOR_TX_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->setData(self::VENDOR_TX_CODE, $vendorTxCode);
    }

    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($desc)
    {
        $this->setData(self::DESCRIPTION, $desc);
    }
}
