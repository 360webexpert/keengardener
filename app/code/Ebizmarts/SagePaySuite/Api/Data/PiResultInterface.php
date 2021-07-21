<?php
namespace Ebizmarts\SagePaySuite\Api\Data;

interface PiResultInterface extends ResultInterface
{
    const STATUS         = 'status';
    const TRANSACTION_ID = 'transaction_id';
    const ORDER_ID       = 'order_id';
    const QUOTE_ID       = 'quote_id';
    const ACS_URL        = 'acs_url';
    const PAR_EQ         = 'par_eq';
    const CREQ         = 'creq';

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId($transactionId);

    /**
     * @return string
     */
    public function getOrderId();

    /**
     * @param string $orderId
     * @return void
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getQuoteId();

    /**
     * @param string $quoteId
     * @return void
     */
    public function setQuoteId($quoteId);

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
     * @param $pareq
     * @return void
     */
    public function setParEq($pareq);

    /**
     * @return string
     */
    public function getCreq();

    /**
     * @param $creq
     * @return void
     */
    public function setCreq($creq);
}
