<?php
namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiTransactionResultInterface
{
    const TRANSACTION_ID      = 'transaction_id';
    const TRANSACTION_TYPE    = 'transaction_type';
    const STATUS              = 'status';
    const STATUS_CODE         = 'status_code';
    const STATUS_DETAIL       = 'status_detail';
    const RETRIEVAL_REFERENCE = 'retrieval_reference';
    const BANK_RESPONSE_CODE  = 'bank_response_code';
    const BANK_AUTH_CODE      = 'bank_auth_code';
    const AMOUNT              = 'amount';
    const CURRENCY            = 'currency';
    const PAYMENT_METHOD      = 'payment_method';
    const THREED_SECURE       = 'threed_secure';
    const ACS_URL             = 'acs_url';
    const PAR_EQ              = 'par_eq';
    const C_REQ              = 'c_req';
    const TX_AUTH_NO          = 'tx_auth_no';
    const AVS_CVC_CHECK       = 'avs_cvc_check';

    /**
     * Sage Pay's unique reference for this transaction.
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * @param $transactionId
     * @return void
     */
    public function setTransactionId($transactionId);

    /**
     * The type of the transaction (e.g. Payment, Repeat etc.)
     * @return string
     */
    public function getTransactionType();

    /**
     * @param $transactionType
     * @return void
     */
    public function setTransactionType($transactionType);


    /**
     * Result of transaction registration.
     * Ok, NotAuthed, Rejected, Malformed, Invalid, Error.
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return void
     */
    public function setStatus($status);

    /**
     * Code related to the status of the transaction.
     * Successfully authorised transactions will have the statusCode of 0000.
     * @return string
     */
    public function getStatusCode();

    /**
     * @param $statusCode
     * @return void
     */
    public function setStatusCode($statusCode);

    /**
     * A detailed reason for the status of the transaction.
     * @return string
     */
    public function getStatusDetail();


    /**
     * @param $statusDetail
     * @return void
     */
    public function setStatusDetail($statusDetail);

    /**
     * @param $ref
     * @return void
     */
    public function setRetrievalReference($ref);

    /**
     * Sage Pay unique Authorisation Code for a successfully authorised transaction.
     * Only present if status is Ok.
     * @return string
     */
    public function getRetrievalReference();

    /**
     * @param $code
     * @return void
     */
    public function setBankResponseCode($code);

    /**
     * Also known as the decline code, these are codes that are specific to your merchant bank.
     *
     * This is only returned for transaction type Payment
     * @return string
     */
    public function getBankResponseCode();

    /**
     * @param $code
     * @return void
     */
    public function setBankAuthCode($code);

    /**
     * The authorisation code returned from your merchant bank.
     * @return string
     */
    public function getBankAuthCode();

    /**
     * The VPS authorisation code.
     * @return string
     */
    public function getTxAuthNo();

    /**
     * @param $code
     * @return void
     */
    public function setTxAuthNo($code);


    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmountInterface $amount
     * @return void
     */
    public function setAmount($amount);

    /**
     * Provides information regarding the transaction amount.
     * This is only returned in GET requests
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAmountInterface
     */
    public function getAmount();

    /**
     * @param $currencyCode
     * @return void
     */
    public function setCurrency($currencyCode);

    /**
     * The currency of the amount in 3 letter ISO 4217 format.
     * This is only returned in GET requests
     * @return string
     */
    public function getCurrency();

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethodInterface $paymentMethod
     * @return void
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * The currency of the amount in 3 letter ISO 4217 format.
     * This is only returned in GET requests
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultPaymentMethodInterface
     */
    public function getPaymentMethod();

    /**
     * @return string
     */
    public function getAcsUrl();

    /**
     * @param $url
     * @return void
     */
    public function setAcsUrl($url);

    /**
     * @return string
     */
    public function getParEq();

    /**
     * @param $creq
     * @return void
     */
    public function setCReq($creq);

    /**
     * @return string
     */
    public function getCReq();

    /**
     * @param $pareq
     * @return void
     */
    public function setParEq($pareq);

    /**
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface
     */
    public function getThreeDSecure();

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface $threed
     * @return void
     */
    public function setThreeDSecure($threed);

    /**
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface
     */
    public function getAvsCvcCheck();

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface $avsCvcCheck
     * @return void
     */
    public function setAvsCvcCheck($avsCvcCheck);
}
