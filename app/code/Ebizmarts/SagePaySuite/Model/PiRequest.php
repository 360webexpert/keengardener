<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

class PiRequest
{
    /** @var \Magento\Quote\Model\Quote */
    private $cart;

    /** @var  \Ebizmarts\SagePaySuite\Helper\Request */
    private $requestHelper;

    /** @var \Ebizmarts\SagePaySuite\Model\Config */
    private $sagepayConfig;

    /** @var string The merchant session key used to generate the cardIdentifier. */
    private $merchantSessionKey;

    /** @var string The unique reference of the card you want to charge. */
    private $cardIdentifier;

    /** @var string Your unique reference for this transaction. Maximum of 40 characters. */
    private $vendorTxCode;

    /** @var bool */
    private $isMoto;

    /** @var \Ebizmarts\SagePaySuite\Api\Data\PiRequest */
    private $requestInfo;

    public function __construct(
        \Ebizmarts\SagePaySuite\Helper\Request $requestHelper,
        \Ebizmarts\SagePaySuite\Model\Config $sagepayConfig
    ) {
    
        $this->requestHelper = $requestHelper;
        $this->sagepayConfig = $sagepayConfig;
    }

    /**
     * @return array
     */
    public function getRequestData()
    {
        $billingAddress  = $this->getCart()->getBillingAddress();
        $shippingAddress = $this->getCart()->getIsVirtual() ? $billingAddress : $this->getCart()->getShippingAddress();

        $data = [
            'transactionType' => $this->sagepayConfig->getSagepayPaymentAction(),
            'paymentMethod'   => [
                'card'        => [
                    'merchantSessionKey' => $this->getMerchantSessionKey(),
                    'cardIdentifier'     => $this->getCardIdentifier(),
                ]
            ],
            'vendorTxCode'      => $this->getVendorTxCode(),
            'description'       => $this->requestHelper->getOrderDescription($this->getIsMoto()),
            'customerFirstName' => substr(trim($billingAddress->getFirstname()), 0, 20),
            'customerLastName'  => substr(trim($billingAddress->getLastname()), 0, 20),
            'applyAvsCvcCheck'  => $this->sagepayConfig->getAvsCvc(),
            'referrerId'        => $this->requestHelper->getReferrerId(),
            'customerEmail'     => $billingAddress->getEmail(),
            'customerPhone'     => substr(trim($billingAddress->getTelephone()), 0, 20),
        ];

        if ($this->getIsMoto()) {
            $data['entryMethod'] = 'TelephoneOrder';
        } else {
            $data['entryMethod'] = 'Ecommerce';
            $data['apply3DSecure'] = $this->sagepayConfig->get3Dsecure();
        }

        $data['billingAddress'] = [
            'address1'      => substr(trim($billingAddress->getStreetLine(1)), 0, 100),
            'city'          => substr(trim($billingAddress->getCity()), 0, 40),
            'postalCode'    => substr(trim($this->sanitizePostcode($billingAddress->getPostcode())), 0, 10),
            'country'       => substr(trim($billingAddress->getCountryId()), 0, 2)
        ];
        if ($data['billingAddress']['country'] == 'US') {
            $data['billingAddress']['state'] = substr($billingAddress->getRegionCode(), 0, 2);
        } else {
            if ($data['billingAddress']['country'] == 'IE' &&
                $data['billingAddress']['postalCode'] == '') {
                $data['billingAddress']['postalCode'] = "000";
            } else {
                if ($data['billingAddress']['country'] == 'HK' &&
                    $data['billingAddress']['postalCode'] == '') {
                    $data['billingAddress']['postalCode'] = "000";
                }
            }
        }

        $data['shippingDetails'] = [
            'recipientFirstName' => substr(trim($shippingAddress->getFirstname()), 0, 20),
            'recipientLastName'  => substr(trim($shippingAddress->getLastname()), 0, 20),
            'shippingAddress1'   => substr(trim($shippingAddress->getStreetLine(1)), 0, 100),
            'shippingCity'       => substr(trim($shippingAddress->getCity()), 0, 40),
            'shippingPostalCode' => substr(trim($this->sanitizePostcode($shippingAddress->getPostcode())), 0, 10),
            'shippingCountry'    => substr(trim($shippingAddress->getCountryId()), 0, 2)
        ];
        if ($data['shippingDetails']['shippingCountry'] == 'US') {
            $data['shippingDetails']['shippingState'] = substr($shippingAddress->getRegionCode(), 0, 2);
        } else {
            if ($data['shippingDetails']['shippingCountry'] == 'IE' &&
                $data['shippingDetails']['shippingPostalCode'] == '') {
                $data['shippingDetails']['shippingPostalCode'] = "000";
            } else {
                if ($data['shippingDetails']['shippingCountry'] == 'HK' &&
                    $data['shippingDetails']['shippingPostalCode'] == '') {
                    $data['shippingDetails']['shippingPostalCode'] = "000";
                }
            }
        }



        //populate payment amount information
        $data = array_merge($data, $this->requestHelper->populatePaymentAmountAndCurrency($this->getCart(), true));

        return $data;
    }

    /**
     * @param \Ebizmarts\SagePaySuite\Api\Data\PiRequest $data
     * @return $this
     */
    public function setRequest(\Ebizmarts\SagePaySuite\Api\Data\PiRequest $data)
    {
        $this->requestInfo = $data;
        return $this;
    }

    public function getRequest()
    {
        return $this->requestInfo;
    }

    /**
     * @return string
     */
    public function getMerchantSessionKey()
    {
        return $this->merchantSessionKey;
    }

    /**
     * @param string $merchantSessionKey
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setMerchantSessionKey($merchantSessionKey)
    {
        $this->merchantSessionKey = $merchantSessionKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardIdentifier()
    {
        return $this->cardIdentifier;
    }

    /**
     * @param string $cardIdentifier
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setCardIdentifier($cardIdentifier)
    {
        $this->cardIdentifier = $cardIdentifier;
        return $this;
    }

    /**
     * @param string $vendorTxCode
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setVendorTxCode($vendorTxCode)
    {
        $this->vendorTxCode = $vendorTxCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getVendorTxCode()
    {
        return $this->vendorTxCode;
    }

    /**
     * @return bool
     */
    public function getIsMoto()
    {
        return $this->isMoto;
    }

    /**
     * @param bool $isMoto
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setIsMoto($isMoto)
    {
        $this->isMoto = $isMoto;
        return $this;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $cart
     * @return \Ebizmarts\SagePaySuite\Model\PiRequest
     */
    public function setCart(\Magento\Quote\Api\Data\CartInterface $cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @param $text
     * @return string
     */
    private function sanitizePostcode($postCode)
    {
        return preg_replace("/[^a-zA-Z0-9-\s]/", "", $postCode);
    }
}
