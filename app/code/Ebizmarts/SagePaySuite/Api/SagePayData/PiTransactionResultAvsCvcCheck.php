<?php
/**
 * Copyright © 2020 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Api\SagePayData;

use Magento\Framework\Api\AbstractExtensibleObject;

class PiTransactionResultAvsCvcCheck extends AbstractExtensibleObject implements PiTransactionResultAvsCvcCheckInterface
{

    /**
     * @param string $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @param string $addressStatus
     * @return void
     */
    public function setAddress($addressStatus)
    {
        $this->setData(self::ADDRESS, $addressStatus);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->_get(self::ADDRESS);
    }

    /**
     * @param string $postalCodeStatus
     * @return void
     */
    public function setPostalCode($postalCodeStatus)
    {
        $this->setData(self::POSTAL_CODE, $postalCodeStatus);
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->_get(self::POSTAL_CODE);
    }

    /**
     * @param string $securityCodeStatus
     * @return void
     */
    public function setSecurityCode($securityCodeStatus)
    {
        $this->setData(self::SECURITY_CODE, $securityCodeStatus);
    }

    /**
     * @return string|null
     */
    public function getSecurityCode()
    {
        return $this->_get(self::SECURITY_CODE);
    }
}
