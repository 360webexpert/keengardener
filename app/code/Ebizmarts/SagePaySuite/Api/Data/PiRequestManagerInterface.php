<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface PiRequestManagerInterface extends PiRequestInterface
{
    const MODE           = 'mode';
    const QUOTE          = 'quote';
    const VENDOR_NAME    = 'vendor_name';
    const VENDOR_TX_CODE = 'vendor_tx_code';
    const PAYMENT_ACTION = 'payment_action';
    const PAR_ES         = 'par_es';
    const CRES           = 'cres';
    const TRANSACTION_ID = 'transaction_id';

    /**
     * @return string
     */
    public function getParEs();

    /**
     * @param string $parEs
     * @return void
     */
    public function setParEs($parEs);

    /**
     * @return string
     */
    public function getCres();

    /**
     * @param string $parEs
     * @return void
     */
    public function setCres($cRes);

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * Transaction mode: test or live.
     * @return string
     */
    public function getMode();

    /**
     * @param string $mode
     * @return void
     */
    public function setMode($mode);

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote();

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return void
     */
    public function setQuote($quote);

    /**
     * @return string
     */
    public function getVendorName();

    /**
     * @param string $vendorName
     * @return void
     */
    public function setVendorName($vendorName);

    /**
     * @return string
     */
    public function getVendorTxCode();

    /**
     * @param string $vendorTxCode
     * @return void
     */
    public function setVendorTxCode($vendorTxCode);

    /**
     * @return string
     */
    public function getPaymentAction();

    /**
     * @param string $paymentAction
     * @return void
     */
    public function setPaymentAction($paymentAction);
}
