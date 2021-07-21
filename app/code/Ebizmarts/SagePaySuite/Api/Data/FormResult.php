<?php

namespace Ebizmarts\SagePaySuite\Api\Data;

class FormResult extends Result implements FormResultInterface
{

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_get(self::REDIRECT_URL);
    }

    /**
     * @param $url
     * @return void
     */
    public function setRedirectUrl($url)
    {
        $this->setData(self::REDIRECT_URL, $url);
    }

    /**
     * @return string
     */
    public function getVpsProtocol()
    {
        return $this->_get(self::VPS_PROTOCOL);
    }

    /**
     * @param string $protocolVersion
     * @return void
     */
    public function setVpsProtocol($protocolVersion)
    {
        $this->setData(self::VPS_PROTOCOL, $protocolVersion);
    }

    /**
     * @return string
     */
    public function getTxType()
    {
        return $this->_get(self::TX_TYPE);
    }

    /**
     * @param string $txType
     * @return void
     */
    public function setTxType($txType)
    {
        $this->setData(self::TX_TYPE, $txType);
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->_get(self::VENDOR);
    }

    /**
     * @param string $vendorname
     * @return void
     */
    public function setVendor($vendorname)
    {
        $this->setData(self::VENDOR, $vendorname);
    }

    /**
     * @return string
     */
    public function getCrypt()
    {
        return $this->_get(self::CRYPT);
    }

    /**
     * @param string $crypt
     * @return void
     */
    public function setCrypt($crypt)
    {
        $this->setData(self::CRYPT, $crypt);
    }
}
