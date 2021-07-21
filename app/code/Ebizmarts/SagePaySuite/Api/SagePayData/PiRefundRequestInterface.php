<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiRefundRequestInterface
{
    const TRANSACTION_TYPE   = 'transactionType';
    const REF_TRANSACTION_ID = 'referenceTransactionId';
    const VENDOR_TX_CODE     = 'vendorTxCode';
    const AMOUNT             = 'amount';
    const DESCRIPTION        = 'description';

    /**
     * The type of the transaction Refund.
     * @return string
     */
    public function getTransactionType();

    /**
     * Sets transaction type to Refund.
     * @return void
     */
    public function setTransactionType();

    /**
     * The transactionId of the referenced transaction.
     * @return string
     */
    public function getReferenceTransactionId();

    /**
     * @param string $transactionId
     * @return void
     */
    public function setReferenceTransactionId($transactionId);

    /**
     * Your unique reference for this transaction. Maximum of 40 characters.
     * @return string
     */
    public function getVendorTxCode();

    /**
     * @param string $vendorTxCode
     * @return void
     */
    public function setVendorTxCode($vendorTxCode);

    /**
     * The amount charged to the customer in the smallest currency unit.
     * (e.g 100 pence to charge £1.00, or 1 to charge ¥1 (0-decimal currency).
     * @return integer
     */
    public function getAmount();

    /**
     * @param integer $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * A brief description of the goods or services purchased.
     * @return string
     */
    public function getDescription();

    /**
     * @param string $desc
     * @return void
     */
    public function setDescription($desc);
}
