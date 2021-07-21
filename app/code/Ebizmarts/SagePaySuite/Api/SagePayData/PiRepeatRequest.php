<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiRepeatRequest extends AbstractExtensibleObject implements PiRepeatRequestInterface
{
    /**
     * The type of the transaction
     * Repeat
     * @return string
     */
    public function getTransactionType(): string
    {
        return $this->_get(self::TRANSACTION_TYPE);
    }

    /**
     * @param string $transactionType
     * @return void
     */
    public function setTransactionType(string $transactionType)
    {
        $this->setData(self::TRANSACTION_TYPE, $transactionType);
    }

    /**
     * The transactionId of the referenced transaction.
     * @return string
     */
    public function getReferenceTransactionId(): string
    {
        return $this->_get(self::REFERENCE_TRANSACTION_ID);
    }

    /**
     * @param string $referenceTransactionId
     * @return void
     */
    public function setReferenceTransactionId(string $referenceTransactionId)
    {
        $this->setData(self::REFERENCE_TRANSACTION_ID, $referenceTransactionId);
    }

    /**
     * Your unique reference for this transaction. Maximum of 40 characters.
     * @return string
     */
    public function getVendorTxCode(): string
    {
        return $this->_get(self::VENDOR_TX_CODE);
    }

    /**
     * @param string $vendorTxCode
     * @return void
     */
    public function setVendorTxCode(string $vendorTxCode)
    {
        $this->setData(self::VENDOR_TX_CODE, $vendorTxCode);
    }

    /**
     * The amount charged to the customer in the smallest currency unit.
     * (e.g 100 pence to charge £1.00, or 1 to charge ¥1 (0-decimal currency).
     * @return int
     */
    public function getAmount(): int
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * @param int $amount
     * @return void
     */
    public function setAmount(int $amount)
    {
        $this->setData(self::AMOUNT, $amount);
    }

    /**
     * The currency of the amount in 3 letter ISO 4217 format.
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->_get(self::CURRENCY);
    }

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency)
    {
        $this->setData(self::CURRENCY, $currency);
    }

    /**
     * A brief description of the goods or services purchased.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }
}
