<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 5:35 PM
 */

namespace Ebizmarts\SagePaySuite\Api\Data;

class PiResult extends Result implements PiResultInterface
{
    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId()
    {
        return $this->_get(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getAcsUrl()
    {
        return $this->_get(self::ACS_URL);
    }

    /**
     * @inheritDoc
     */
    public function setAcsUrl($url)
    {
        $this->setData(self::ACS_URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getParEq()
    {
        return $this->_get(self::PAR_EQ);
    }

    /**
     * @inheritDoc
     */
    public function setParEq($pareq)
    {
        $this->setData(self::PAR_EQ, $pareq);
    }

    /**
     * @return string
     */
    public function getCreq()
    {
        return $this->_get(self::CREQ);
    }

    /**
     * @param $creq
     * @return void
     */
    public function setCreq($creq)
    {
        $this->setData(self::CREQ, $creq);
    }
}
