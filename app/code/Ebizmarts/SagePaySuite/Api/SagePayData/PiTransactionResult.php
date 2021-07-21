<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/25/17
 * Time: 4:05 PM
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiTransactionResult extends AbstractExtensibleObject implements PiTransactionResultInterface
{
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
    public function getTransactionType()
    {
        return $this->_get(self::TRANSACTION_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setTransactionType($transactionType)
    {
        $this->setData(self::TRANSACTION_TYPE, $transactionType);
    }

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
    public function getStatusCode()
    {
        return $this->_get(self::STATUS_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setStatusCode($statusCode)
    {
        $this->setData(self::STATUS_CODE, $statusCode);
    }

    /**
     * @inheritDoc
     */
    public function getStatusDetail()
    {
        return $this->_get(self::STATUS_DETAIL);
    }

    /**
     * @inheritDoc
     */
    public function setStatusDetail($statusDetail)
    {
        $this->setData(self::STATUS_DETAIL, $statusDetail);
    }

    /**
     * @inheritDoc
     */
    public function setRetrievalReference($ref)
    {
        $this->setData(self::RETRIEVAL_REFERENCE, $ref);
    }

    /**
     * @inheritDoc
     */
    public function getRetrievalReference()
    {
        return $this->_get(self::RETRIEVAL_REFERENCE);
    }

    /**
     * @inheritDoc
     */
    public function setBankResponseCode($code)
    {
        $this->setData(self::BANK_RESPONSE_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getBankResponseCode()
    {
        return $this->_get(self::BANK_RESPONSE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setBankAuthCode($code)
    {
        $this->setData(self::BANK_AUTH_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getBankAuthCode()
    {
        return $this->_get(self::BANK_AUTH_CODE);
    }

    /**
     * @inheritDoc
     */
    public function getTxAuthNo()
    {
        return $this->_get(self::TX_AUTH_NO);
    }

    /**
     * @inheritDoc
     */
    public function setTxAuthNo($code)
    {
        $this->setData(self::TX_AUTH_NO, $code);
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
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($currencyCode)
    {
        $this->setData(self::CURRENCY, $currencyCode);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod()
    {
        return $this->_get(self::PAYMENT_METHOD);
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
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface
     */
    public function getThreeDSecure()
    {
        return $this->_get(self::THREED_SECURE);
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultThreeDInterface $threed
     * @return void
     */
    public function setThreeDSecure($threed)
    {
        $this->setData(self::THREED_SECURE, $threed);
    }

    /**
     * @param $creq
     * @return void
     */
    public function setCReq($creq)
    {
        $this->setData(self::C_REQ, $creq);
    }

    /**
     * @return string
     */
    public function getCReq()
    {
        return $this->_get(self::C_REQ);
    }
    
    /**
     * @return \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface
     */
    public function getAvsCvcCheck()
    {
        return $this->_get(self::AVS_CVC_CHECK);
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\SagePayData\PiTransactionResultAvsCvcCheckInterface $avsCvcCheck
     * @return void
     */
    public function setAvsCvcCheck($avsCvcCheck)
    {
        $this->setData(self::AVS_CVC_CHECK, $avsCvcCheck);
    }
}
