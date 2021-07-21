<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Helper;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmount;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Request extends AbstractHelper
{

    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config
     */
    private $sagepaySuiteConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /** @var \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmount */
    private $transactionAmountFactory;

    /** @var \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPost */
    private $transactionAmountPostFactory;

    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /**
     * Request constructor.
     * @param Config $config
     * @param ObjectManager $objectManager
     */
    public function __construct(
        Config $config,
        ObjectManager $objectManager,
        \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountFactory $transactionAmountFactory,
        \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPostFactory $transactionAmountPostFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->sagepaySuiteConfig = $config;
        $this->objectManager      = $objectManager;
        $this->transactionAmountFactory  = $transactionAmountFactory;
        $this->transactionAmountPostFactory  = $transactionAmountPostFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function populateAddressInformation($quote)
    {
        /** @var \Magento\Quote\Model\Quote\Address $billing_address */
        $billing_address = $quote->getBillingAddress();

        /** @var \Magento\Quote\Model\Quote\Address $shipping_address */
        $shipping_address = $quote->isVirtual() ? $billing_address : $quote->getShippingAddress();

        $data = [];

        //customer email
        $emptyEmail = $billing_address->getEmail();
        if ($emptyEmail == null || $emptyEmail == "") {
            throw new LocalizedException(__('The email is null or empty.'));
        }

        $data["CustomerEMail"] = $billing_address->getEmail();

        $data['BillingSurname']    = substr($billing_address->getLastname(), 0, 20);
        $data['BillingFirstnames'] = substr($billing_address->getFirstname(), 0, 20);
        $data['BillingAddress1']   = substr($billing_address->getStreetLine(1), 0, 100);
        $data['BillingAddress2']   = substr($billing_address->getStreetLine(2), 0, 100);
        $data['BillingCity']       = substr($billing_address->getCity(), 0, 40);
        $data['BillingPostCode']   = substr($billing_address->getPostcode(), 0, 10);
        $data['BillingCountry']    = substr($billing_address->getCountryId(), 0, 2);

        //only send state if US due to Sage Pay 2 char restriction
        if ($data['BillingCountry'] == 'US') {
            $data['BillingState'] = substr($billing_address->getRegionCode(), 0, 2);
        } else {
            if ($data['BillingCountry'] == 'IE' && $data['BillingPostCode'] == '') {
                $data['BillingPostCode'] = "000";
            } else {
                if ($data['BillingCountry'] == 'HK' && $data['BillingPostCode'] == '') {
                    $data['BillingPostCode'] = "000";
                }
            }
        }

        $data['BillingPhone'] = substr($billing_address->getTelephone(), 0, 20);

        //mandatory
        $data['DeliverySurname']    = substr($shipping_address->getLastname(), 0, 20);
        $data['DeliveryFirstnames'] = substr($shipping_address->getFirstname(), 0, 20);
        $data['DeliveryAddress1']   = substr($shipping_address->getStreetLine(1), 0, 100);
        $data['DeliveryAddress2']   = substr($shipping_address->getStreetLine(2), 0, 100);
        $data['DeliveryCity']       = substr($shipping_address->getCity(), 0, 40);
        $data['DeliveryPostCode']   = substr($shipping_address->getPostcode(), 0, 10);
        $data['DeliveryCountry']    = substr($shipping_address->getCountryId(), 0, 2);
        //only send state if US due to Sage Pay 2 char restriction
        if ($data['DeliveryCountry'] == 'US') {
            $data['DeliveryState'] = substr($shipping_address->getRegionCode(), 0, 2);
        } else {
            if ($data['DeliveryCountry'] == 'IE' && $data['DeliveryPostCode'] == '') {
                $data['DeliveryPostCode'] = "000";
            } else {
                if ($data['DeliveryCountry'] == 'HK' && $data['DeliveryPostCode'] == '') {
                    $data['DeliveryPostCode'] = "000";
                }
            }
        }

        $data['DeliveryPhone'] = substr($shipping_address->getTelephone(), 0, 20);

        return $data;
    }

    /**
     * Remove BasketXML from request if amounts don't match.
     *
     * @param array $data
     * @return array
     */
    public function unsetBasketXMLIfAmountsDontMatch(array $data)
    {
        if (isset($data['BasketXML']) && isset($data['Amount'])) {
            $basketTotal = $this->getBasketXmlTotalAmount($data['BasketXML']);

            if (!$this->floatsEqual($data['Amount'], $basketTotal)) {
                unset($data['BasketXML']);
            }
        }

        return $data;
    }

    /**
     * @param string $basket
     * @return float
     */
    public function getBasketXmlTotalAmount($basket)
    {
        //amount = Sum of totalGrossAmount + deliveryGrossAmount - Sum of fixed (discounts)
        $xml    = null;
        $amount = 0;

        try {
            $xml = $this->objectManager->create('\SimpleXMLElement', ['data' => $basket]);
        } catch (\Exception $ex) {
            return $amount;
        }

        $amount += $this->getBasketXmlItemsTotalAmount($xml->children()->item);

        $amount += (float)$xml->children()->deliveryGrossAmount;

        if (isset($xml->children()->discounts)) {
            $amount -= $this->getBasketXmlDiscountTotalAmount($xml->children()->discounts->children());
        }

        return $amount;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool|false $isRestRequest
     * @return array
     */
    public function populatePaymentAmountAndCurrency($quote, $isRestRequest = false)
    {
        $this->sagepaySuiteConfig->setConfigurationScopeId($quote->getStoreId());

        $amount = $this->sagepaySuiteConfig->getQuoteAmount($quote);
        $roundedAmount = $this->priceCurrency->round($amount);

        $storeCurrencyCode = $this->sagepaySuiteConfig->getQuoteCurrencyCode($quote);

        $data = [];
        if ($isRestRequest) {
            /** @var \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmount  $transactionAmount */
            $transactionAmount = $this->transactionAmountFactory->create(['amount' => $roundedAmount]);
            $data["amount"]   = $transactionAmount->getCommand($storeCurrencyCode)->execute();
            $data["currency"] = $storeCurrencyCode;
        } else {
            /** @var \Ebizmarts\SagePaySuite\Model\PiRequestManagement\TransactionAmountPost  $transactionAmount */
            $transactionAmountPost = $this->transactionAmountPostFactory->create(['amount' => $roundedAmount]);
            $data["Amount"]   = $transactionAmountPost->getCommand($storeCurrencyCode)->execute();
            $data["Currency"] = $storeCurrencyCode;
        }

        return $data;
    }

    public function populateBasketInformation($quote, $force_xml = false)
    {
        $data = [];

        $basketFormat = $this->sagepaySuiteConfig->getBasketFormat();

        if ($basketFormat == Config::BASKETFORMAT_XML || $force_xml == true) {
            $_basket = $this->getBasketXml($quote);
            if ($this->validateBasketXml($_basket)) {
                $data['BasketXML'] = $_basket;
            }
        } elseif ($basketFormat == Config::BASKETFORMAT_SAGE50) {
            $data['Basket'] = $this->getBasketSage50($quote);
        }

        return $data;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return string
     */
    private function getBasketSage50($quote)
    {
        $BASKET_SEP = ':';
        $BASKET_SEP_ESCAPE = '-';

        $basketArray = [];

        $itemsCollection = $quote->getItemsCollection();

        foreach ($itemsCollection as $item) {
            //Avoid configurable products.
            if ($item->getParentItem()) {
                continue;
            }

            $itemQty = $item->getQty();
            if (!is_numeric($itemQty) || $itemQty <= 0) {
                continue;
            }

            $taxAmount = $item->getTaxAmount() / $itemQty;
            $itemValue = ($item->getRowTotal() - $item->getDiscountAmount()) / $itemQty;

            $itemTotal = $itemValue + $taxAmount;

            $_options = '';

            $newItem = [
                "item" => "",
                "qty" => 0,
                "item_value" => 0,
                "item_tax" => 0,
                "item_total" => 0,
                "line_total" => 0
            ];

            //[SKU] Name @codingStandardsIgnoreLine
            $newItem["item"] = str_replace(
                $BASKET_SEP,
                $BASKET_SEP_ESCAPE,
                $this->productDescSage50Basket($item, $_options)
            );

            //Quantity
            $newItem["qty"] = $itemQty;

            //Item value
            $newItem["item_value"] = number_format($itemValue, 2);

            //Item tax
            $newItem["item_tax"] = number_format($taxAmount, 3);

            //Item total
            $newItem["item_total"] = number_format($itemTotal, 2);

            //Line total
            $newItem["line_total"] = number_format($itemTotal * $itemQty, 2);

            //add item to array
            $basketArray[] = $newItem;
        }

        $shippingAddress = $quote->getShippingAddress();
        $shippingDescription = $shippingAddress->getShippingDescription();
        $deliveryName = $shippingDescription ? $shippingDescription : 'Delivery';

        $deliveryValue  = $shippingAddress->getShippingAmount();
        $deliveryTax    = $shippingAddress->getShippingTaxAmount();
        $deliveryAmount = $deliveryValue + $deliveryTax;

        //delivery item
        $deliveryItem = [
            "item"=>str_replace($BASKET_SEP, $BASKET_SEP_ESCAPE, $this->cleanSage50BasketString($deliveryName)),
            "qty"=>1,
            "item_value"=>$deliveryValue,
            "item_tax"=>$deliveryTax,
            "item_total"=>$deliveryAmount,
            "line_total"=>$deliveryAmount];

        $basketArray[] = $deliveryItem;

        //create basket string
        $basketString = '';
        foreach ($basketArray as $item) {
            $basketString .= $BASKET_SEP . implode($BASKET_SEP, $item);
        }

        //add total rows
        $basketString = count($basketArray) . $basketString;
        
        return $basketString;
    }

    /**
     * Adds a CDATA property to an XML document.
     *
     * @param string $name
     *   Name of property that should contain CDATA.
     * @param string $value
     *   Value that should be inserted into a CDATA child.
     * @param object $parent
     *   Element that the CDATA child should be attached too.
     */
    private function addChildCData($name, $value, &$parent)
    {
        $child = $parent->addChild($name);

        if ($child !== null) {
            $child_node = dom_import_simplexml($child);
            $child_owner = $child_node->ownerDocument;
            $child_node->appendChild($child_owner->createCDATASection($value));
        }
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote
     * @return string
     */
    private function getBasketXml($quote)
    {
        /** @var \SimpleXMLElement $basket */
        $basket = $this->objectManager
            ->create('\SimpleXMLElement', ['data' => '<?xml version="1.0" encoding="utf-8" ?><basket />']);

        $shippingAdd = $quote->getShippingAddress();
        $billingAdd  = $quote->getBillingAddress();

        $itemsCollection = $quote->getItemsCollection();

        foreach ($itemsCollection as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $node = $basket->addChild('item', '');

            $this->basketXmlProductDescription($item, $node);

            $this->basketXmlProductSku($item, $node);

            //<productCode>
            $node->addChild('productCode', $item->getProductId());

            $itemQty = $item->getQty();

            $unitTaxAmount    = $this->formatPrice($item->getTaxAmount() / $itemQty);
            $unitNetAmount    = $this->formatPrice($item->getPrice());
            $unitGrossAmount  = $this->formatPrice($unitNetAmount + $unitTaxAmount);
            $totalGrossAmount = $this->formatPrice($unitGrossAmount * $itemQty);

            //<quantity>
            $node->addChild('quantity', $itemQty);
            //<unitNetAmount>
            $node->addChild('unitNetAmount', $unitNetAmount);
            //<unitTaxAmount>
            $node->addChild('unitTaxAmount', $unitTaxAmount);
            //<unitGrossAmount>
            $node->addChild('unitGrossAmount', $unitGrossAmount);
            //<totalGrossAmount>
            $node->addChild('totalGrossAmount', $totalGrossAmount);

            //<recipientFName>
            $this->basketXmlRecipientFName($shippingAdd, $node);

            //<recipientLName>
            $this->basketXmlRecipientLName($shippingAdd, $node);

            //<recipientMName>
            $this->basketXmlMiddleName($shippingAdd, $node);

            //<recipientSal>
            $this->basketXmlRecipientSalutation($shippingAdd, $node);

            //<recipientEmail>
            $this->basketXmlRecipientEmail($shippingAdd, $node);

            //<recipientPhone>
            $this->basketXmlRecipientPhone($shippingAdd, $node);

            //<recipientAdd1>
            $address1 = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getStreetLine(1)), 0, 100));
            if (!empty($address1)) {
                $this->addChildCData('recipientAdd1', $address1, $node);
            }

            //<recipientAdd2>
            if ($shippingAdd->getStreet(2)) {
                $recipientAdd2 = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getStreetLine(2)), 0, 100));
                if (!empty($recipientAdd2)) {
                    $this->addChildCData('recipientAdd2', $recipientAdd2, $node);
                }
            }

            //<recipientCity>
            $recipientCity = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getCity()), 0, 40));
            if (!empty($recipientCity)) {
                $node->addChild('recipientCity', $recipientCity);
            }

            //<recipientState>
            $this->basketXmlRecipientState($quote, $shippingAdd, $node, $billingAdd);

            //<recipientCountry>
            $node->addChild('recipientCountry', $this->stringToSafeXMLChar(
                substr(trim($shippingAdd->getCountry()), 0, 2)
            ));

            //<recipientPostCode>
            $_postCode = '000';
            if ($shippingAdd->getPostcode()) {
                $_postCode = $shippingAdd->getPostcode();
            }
            $node->addChild('recipientPostCode', $this->stringToSafeXMLChar(
                $this->sanitizePostcode(substr(trim($_postCode), 0, 9))
            ));
        }

        //Sum up shipping totals when using SERVER with MAC
        if ($quote->getIsMultiShipping() && ($quote->getPayment()->getMethod() == 'sagepayserver')) {
            $shippingInclTax = $shippingTaxAmount = 0.00;

            $addresses = $quote->getAllAddresses();
            foreach ($addresses as $address) {
                $shippingTaxAmount += $address->getShippingTaxAmount();
                $shippingInclTax   += $address->getShippingAmount() + $shippingTaxAmount;
            }
        } else {
            $shippingTaxAmount = $shippingAdd->getShippingTaxAmount();
            $shippingInclTax   = $shippingAdd->getShippingAmount() + $shippingTaxAmount;
        }

        //<deliveryNetAmount>
        $basket->addChild('deliveryNetAmount', $this->formatPrice($shippingAdd->getShippingAmount()));

        //<deliveryTaxAmount>
        $basket->addChild('deliveryTaxAmount', $this->formatPrice($shippingTaxAmount));

        //<deliveryGrossAmount>
        $basket->addChild('deliveryGrossAmount', $this->formatPrice($shippingInclTax));

        //<shippingFaxNo>
        $this->basketXmlFaxNumber($shippingAdd, $basket);

        $xmlBasket = str_replace("\n", "", trim($basket->asXml()));

        return $xmlBasket;
    }

    /**
     * @param $value
     * @return string
     */
    private function formatPrice($value)
    {
        return number_format($value, 2, '.', '');
    }

    private function cleanSage50BasketString($text)
    {
        $pattern = '|[^a-zA-Z0-9\-\._\s]+|';
        $text = preg_replace($pattern, '', $text);
        return $text;
    }

    /**
     * @param $string
     * @return string
     */
    private function stringToSafeXMLChar($string)
    {

        $safe_regex = '/([a-zA-Z\s\d\+\'\"\/\\\&\:\,\.\-\{\}\@])/';
        $safe_string = "";

        $length = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (preg_match($safe_regex, substr($string, $i, 1)) != false) {
                $safe_string .= substr($string, $i, 1);
            } else {
                $safe_string .= '';
            }
        }

        return $safe_string;
    }

    /**
     * @param string $text
     * @return mixed
     */
    private function sanitizePostcode($text)
    {
        return preg_replace("/[^a-zA-Z0-9-\s]/", "", $text);
    }


    private function sanitizeRecipientName($text)
    {
        return preg_replace('/[0-9\d\"\&\:\,\{\}\@]/', "", $text);
    }

    /**
     * Check if basket is OKay to be sent to Sage Pay.
     *
     * @param string $basket
     * @return boolean
     */
    private function validateBasketXml($basket)
    {
        //Validate max length
        $validLength  = $this->validateBasketXmlLength($basket);

        $validAmounts = $this->validateBasketXmlAmounts($basket);

        return $validLength && $validAmounts;
    }

    /**
     * @param bool $isMailOrderTelephoneOrder
     * @return \Magento\Framework\Phrase
     */
    public function getOrderDescription($isMailOrderTelephoneOrder = false)
    {
        return $isMailOrderTelephoneOrder ? __("Online MOTO transaction.") : __("Online transaction.");
    }

    public function getReferrerId()
    {
        return "01bf51f9-0dcd-49dd-a07a-3b1f918c77d7";
    }

    /**
     * @param $basket
     * @return bool
     */
    public function validateBasketXmlAmounts($basket)
    {
        $valid = true;

        /**
         * unitGrossAmount = unitNetAmount + unitTaxAmount
         * totalGrossAmount = unitGrossAmount * quantity
         */

        $xml = null;

        try {
            $xml = $this->objectManager->create('\SimpleXMLElement', ['data' => $basket]);
        } catch (\Exception $ex) {
            $valid = false;
        }

        $items = $xml->children()->item;

        $totalItems = count($items);

        $i = 0;
        while ($valid && $i < $totalItems) {
            $unitGrossAmount  = (float)$items[$i]->unitNetAmount + (float)$items[$i]->unitTaxAmount;
            $validUnit        = $this->floatsEqual((float)$items[$i]->unitGrossAmount, $unitGrossAmount);

            $totalGrossAmount = (float)$items[$i]->unitGrossAmount * (float)$items[$i]->quantity;
            $validTotal       = $this->floatsEqual((float)$items[$i]->totalGrossAmount, $totalGrossAmount);

            $valid = $validTotal && $validUnit;

            $i++;
        }

        return $valid;
    }

    /**
     * @param $f1
     * @param $f2
     * @return bool
     */
    public function floatsEqual($f1, $f2)
    {
        $floatsValue = $f1;
        if ($f2 > 0) {
            $floatsValue = abs(($f1-$f2)/$f2);
        }
        return $floatsValue < 0.00001;
    }

    /**
     * @param $basket
     * @return bool
     */
    public function validateBasketXmlLength($basket)
    {
        $valid = true;
        if (strlen($basket) > 20000) {
            $valid = false;
        }
        return $valid;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlRecipientFName($shippingAdd, $node)
    {
        $validFName = preg_match_all('/([a-zA-Z\s\+\'\/\\\.\-\(\)]+)/', $shippingAdd->getFirstname(), $matchesFName);
        if ($validFName > 0) {
            $this->addChildCData(
                'recipientFName',
                substr(
                    $this->sanitizeRecipientName($this->stringToSafeXMLChar($shippingAdd->getFirstname())),
                    0,
                    20
                ),
                $node
            );
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlRecipientLName($shippingAdd, $node)
    {
        $validFName = preg_match_all('/([a-zA-Z\s\+\'\/\\\.\-\(\)]+)/', $shippingAdd->getLastname(), $matchesFName);
        if ($validFName > 0) {
            $this->addChildCData(
                'recipientLName',
                substr(
                    $this->sanitizeRecipientName($this->stringToSafeXMLChar($shippingAdd->getLastname())),
                    0,
                    20
                ),
                $node
            );
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlMiddleName($shippingAdd, $node)
    {
        if ($shippingAdd->getMiddlename()) {
            $recipientMName = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getMiddlename()), 0, 1));
            if (!empty($recipientMName)) {
                $node->addChild('recipientMName', $recipientMName);
            }
        }
    }

    /**
     * @param $item
     * @param \SimpleXMLElement $node
     */
    private function basketXmlProductSku($item, $node)
    {
        $validSku = preg_match_all("/[\p{L}0-9\s\-]+/", $item->getSku(), $matchesSku);
        if ($validSku === 1) {
            //<productSku>
            $this->addChildCData(
                'productSku',
                substr(
                    $this->sanitizePostcode($this->stringToSafeXMLChar($item->getSku())),
                    0,
                    12
                ),
                $node
            );
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $basket
     */
    private function basketXmlFaxNumber($shippingAdd, $basket)
    {
        $validFax = preg_match_all("/[a-zA-Z0-9\-\s\(\)\+]+/", trim($shippingAdd->getFax()), $matchesFax);
        if ($validFax === 1) {
            $basket->addChild('shippingFaxNo', substr(trim($shippingAdd->getFax()), 0, 20));
        }
    }

    private function getBasketXmlDiscountTotalAmount($discounts)
    {
        $amount = 0;

        $totalDiscounts = count($discounts);

        $i = 0;
        while ($i < $totalDiscounts) {
            $amount += (float)$discounts[$i]->fixed;
            $i++;
        }

        return $amount;
    }

    /**
     * @param $items
     * @return float
     */
    private function getBasketXmlItemsTotalAmount($items)
    {
        $amount = 0;
        $totalItems = count($items);

        $i = 0;
        while ($i < $totalItems) {
            $amount += (float)$items[$i]->totalGrossAmount;
            $i++;
        }
        return $amount;
    }

    /**
     * @param $item
     * @param $_options
     * @return string
     */
    private function productDescSage50Basket($item, $_options)
    {
        $nameAdd = $this->cleanSage50BasketString($item->getName()) . $this->cleanSage50BasketString($_options);
        return '[' . $this->cleanSage50BasketString($item->getSku()) . '] ' . $nameAdd;
    }

    /**
     * @param $item
     * @param \SimpleXMLElement $node
     */
    private function basketXmlProductDescription($item, $node)
    {
        $itemDesc = trim(substr($item->getName(), 0, 100));
        $itemDesc = html_entity_decode($itemDesc, ENT_QUOTES, "utf-8"); //@codingStandardsIgnoreLine
        $validDescription = preg_match_all("/.*/", $itemDesc, $matchesDescription);
        if ($validDescription !== 1) {
            //<description>
            $itemDesc = substr(implode("", $matchesDescription[0]), 0, 100);
        }

        $this->addChildCData('description', $this->stringToSafeXMLChar($itemDesc), $node);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlRecipientSalutation($shippingAdd, $node)
    {
        if ($shippingAdd->getPrefix()) {
            $recipientSal = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getPrefix()), 0, 4));
            if (!empty($recipientSal)) {
                $node->addChild('recipientSal', $recipientSal);
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlRecipientEmail($shippingAdd, $node)
    {
        if ($shippingAdd->getEmail()) {
            $recipientEmail = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getEmail()), 0, 45));
            if (!empty($recipientEmail)) {
                $node->addChild('recipientEmail', $recipientEmail);
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     */
    private function basketXmlRecipientPhone($shippingAdd, $node)
    {
        $recipientPhone = $this->stringToSafeXMLChar(substr(trim($shippingAdd->getTelephone()), 0, 20));
        if (!empty($recipientPhone)) {
            $node->addChild('recipientPhone', $recipientPhone);
        }
    }

    /**
     * @param $quote
     * @param \Magento\Quote\Model\Quote\Address $shippingAdd
     * @param \SimpleXMLElement $node
     * @param $billingAdd
     */
    private function basketXmlRecipientState($quote, $shippingAdd, $node, $billingAdd)
    {
        if ($shippingAdd->getCountry() == 'US') {
            if ($quote->getIsVirtual()) {
                $regionCode = substr(trim($billingAdd->getRegionCode()), 0, 2);
                $node->addChild('recipientState', $this->stringToSafeXMLChar($regionCode));
            } else {
                $regionCode = substr(trim($shippingAdd->getRegionCode()), 0, 2);
                $node->addChild('recipientState', $this->stringToSafeXMLChar($regionCode));
            }
        }
    }
}
