<?php

declare(strict_types=1);

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiRepeatRequestInterface
{
    const TRANSACTION_TYPE         = 'transactionType';
    const REFERENCE_TRANSACTION_ID = 'referenceTransactionId';
    const VENDOR_TX_CODE           = 'vendorTxCode';
    const AMOUNT                   = 'amount';
    const CURRENCY                 = 'currency';
    const DESCRIPTION              = 'description';

    /**
     * The type of the transaction
     * Repeat
     * @return string
     */
    public function getTransactionType(): string;

    /**
     * @param string $transactionType
     * @return void
     */
    public function setTransactionType(string $transactionType);

    /**
     * The transactionId of the referenced transaction.
     * @return string
     */
    public function getReferenceTransactionId(): string;

    /**
     * @param string $referenceTransactionId
     * @return void
     */
    public function setReferenceTransactionId(string $referenceTransactionId);

    /**
     * Your unique reference for this transaction. Maximum of 40 characters.
     * @return string
     */
    public function getVendorTxCode(): string;

    /**
     * @param string $vendorTxCode
     * @return void
     */
    public function setVendorTxCode(string $vendorTxCode);

    /**
     * The amount charged to the customer in the smallest currency unit.
     * (e.g 100 pence to charge £1.00, or 1 to charge ¥1 (0-decimal currency).
     * @return int
     */
    public function getAmount(): int;

    /**
     * @param int $amount
     * @return void
     */
    public function setAmount(int $amount);

    /**
     * The currency of the amount in 3 letter ISO 4217 format.
     * @return string
     */
    public function getCurrency(): string;

    /**
     * @param string $currency
     * @return void
     */
    public function setCurrency(string $currency);

    /**
     * A brief description of the goods or services purchased.
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return void
     */
    public function setDescription(string $description);
}
