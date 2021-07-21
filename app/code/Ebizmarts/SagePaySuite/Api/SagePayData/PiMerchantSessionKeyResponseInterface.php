<?php

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

interface PiMerchantSessionKeyResponseInterface
{
    const MERCHANT_SESSION_KEY = 'merchantSessionKey';
    const EXPIRY               = 'expiry';

    /**
     * Unique key used in card identifier creation and transaction registration.
     * @return string
     */
    public function getMerchantSessionKey();

    /**
     * @param string $key
     * @return void
     */
    public function setMerchantSessionKey($key);

    /**
     * Date/Time the merchant session key will expire in ISO 8601 format.
     * @return string
     */
    public function getExpiry();

    /**
     * @param string $dateTime
     * @return void
     */
    public function setExpiry($dateTime);
}
