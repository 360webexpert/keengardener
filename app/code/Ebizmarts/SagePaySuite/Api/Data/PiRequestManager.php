<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 1/27/17
 * Time: 5:29 PM
 */

namespace Ebizmarts\SagePaySuite\Api\Data;

class PiRequestManager extends \Ebizmarts\SagePaySuite\Api\Data\PiRequest implements PiRequestManagerInterface
{
    /**
     * @inheritDoc
     */
    public function getMode()
    {
        return $this->_get(self::MODE);
    }

    /**
     * @inheritDoc
     */
    public function setMode($mode)
    {
        $this->setData(self::MODE, $mode);
    }

    /**
     * @inheritDoc
     */
    public function getQuote()
    {
        return $this->_get(self::QUOTE);
    }

    /**
     * @inheritDoc
     */
    public function setQuote($quote)
    {
        $this->setData(self::QUOTE, $quote);
    }

    /**
     * @inheritDoc
     */
    public function getVendorName()
    {
        return $this->_get(self::VENDOR_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setVendorName($vendorName)
    {
        $this->setData(self::VENDOR_NAME, $vendorName);
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
    public function getPaymentAction()
    {
        return $this->_get(self::PAYMENT_ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentAction($paymentAction)
    {
        $this->setData(self::PAYMENT_ACTION, $paymentAction);
    }

    /**
     * @return string
     */
    public function getParEs()
    {
        return $this->_get(self::PAR_ES);
    }

    /**
     * @param string $parEs
     * @return void
     */
    public function setParEs($parEs)
    {
        $this->setData(self::PAR_ES, $parEs);
    }

    /**
     * @param string $transactionId
     * @return void
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * @return string
     */
    public function getCres()
    {
        return $this->_get(self::CRES);
    }

    /**
     * @param string $parEs
     * @return void
     */
    public function setCres($cRes)
    {
        $this->setData(self::CRES, $cRes);
    }
}
