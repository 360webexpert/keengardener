<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Plugin\Carrier;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingMethodExtensionFactory;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use WeSupply\Toolbox\Block\Estimations\Delivery as EstimatesDelivery;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;
use WeSupply\Toolbox\Helper\Estimates as EstimatesHelper;

class AppendEstimations
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ShippingMethodExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var CheckoutSession::getQuote()
     */
    protected $quote;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var EstimatesDelivery
     */
    protected $estimatesDelivery;

    /**
     * @var EstimatesHelper
     */
    private $estimatesHelper;

    /**
     * @var WeSupplyHelper
     */
    private $helper;

    /**
     * @var $estimations
     */
    private $estimations;

    /**
     * @var $carrierCode
     */
    private $carrierCode;

    /**
     * @var $methodCode
     */
    private $methodCode;

    /**
     * AppendEstimations constructor.
     * @param CheckoutSession $checkoutSession
     * @param ShippingMethodExtensionFactory $extensionFactory
     * @param Json $json
     * @param EstimatesDelivery $estimatesDelivery
     * @param EstimatesHelper $estimatesHelper
     * @param WeSupplyHelper $helper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ShippingMethodExtensionFactory $extensionFactory,
        Json $json,
        EstimatesDelivery $estimatesDelivery,
        EstimatesHelper $estimatesHelper,
        WeSupplyHelper $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->extensionFactory = $extensionFactory;
        $this->json = $json;
        $this->estimatesDelivery = $estimatesDelivery;
        $this->estimatesHelper = $estimatesHelper;
        $this->helper = $helper;
    }

    /**
     * @param ShippingMethodConverter $subject
     * @param ShippingMethodInterface $result
     * @return ShippingMethodInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterModelToDataObject(
        ShippingMethodConverter $subject,
        ShippingMethodInterface $result
    ) {
        if (!$this->helper->getWeSupplyEnabled() || !$this->helper->getDeliveryEstimationsEnabled()) {
            return $result;
        }

        $this->carrierCode = strtolower($result->getCarrierCode());
        $this->methodCode = $result->getMethodCode();

        $this->quote = $this->checkoutSession->getQuote();
        $this->estimations = $this->getEstimationQuotes();

        // try to get a new fresh estimations, just in case something has changed meanwhile
        if ($this->estimationsHasErrors() &&
            ($this->estimations['estimation_attempt'] == 'empty' || $this->estimations['estimation_attempt'] == 'observer')
        ) {
            $this->getNewDeliveryEstimations();
        }

        if ($this->estimationsHasErrors()) {
            // has errors - do nothing and exit
            return $result;
        }

        // address has changed? -> get a new fresh estimations
        if (
            (
                $this->quote->getShippingAddress()->getPostcode() &&
                $this->quote->getShippingAddress()->getCountryId()
            ) &&
            (
                $this->quote->getShippingAddress()->getPostcode() != $this->estimations['location_id'] ||
                $this->quote->getShippingAddress()->getCountryId() != $this->estimations['location_country']
            )
        ) {
            $this->getNewDeliveryEstimations();
            if ($this->estimationsHasErrors()) {
                // has errors - do nothing and exit
                return $result;
            }
        }

        if (!$this->estimationExists()) {
            // estimation for the current shipping method is not set
            // do nothing and exit
            return $result;
        }

        $extensibleAttribute = ($result->getExtensionAttributes())
            ? $result->getExtensionAttributes()
            : $this->extensionFactory->create();

        // add estimated delivery time if it shouldn't be hidden
        if (!$this->estimatesHelper->hideEstimations($this->concatShipperMethod())) {
            $estimatedTimestamp = $this->getEstimationTimestamp();
            if ($this->validateTimestamp($estimatedTimestamp)) {

                if ($this->estimatesHelper->estimationRangeEnabled()) {
                    $estimatedTimestamp = $this->addEstimationRange($estimatedTimestamp);
                }

                // date format
                $estimationsFormat = $this->helper->getDeliveryEstimationsFormat();
                if (is_array($estimatedTimestamp)) { // Case of ETA as range
                    $extensibleAttribute->setDeliveryTimestamp(implode(',', $estimatedTimestamp));
                    $estimatedDelivery = date($estimationsFormat, $estimatedTimestamp[0]);
                    if (date('y-m-d', $estimatedTimestamp[0]) != date('y-m-d', $estimatedTimestamp[1])) {
                        $estimatedDelivery .= ' - ' . date($estimationsFormat, $estimatedTimestamp[1]);
                    }
                } else {
                    $extensibleAttribute->setDeliveryTimestamp($estimatedTimestamp);
                    $estimatedDelivery = date($estimationsFormat, $estimatedTimestamp);
                }

                // finally set ETA
                $extensibleAttribute->setDeliveryTime(__('ETA: ') . $estimatedDelivery);
            }
        }

        // add estimation message if any
        if ($message = $this->estimatesHelper->getEstimationsMessageByShipperMethod($this->concatShipperMethod())) {
            $extensibleAttribute->setDeliveryMessage($message);
        }

        // at the end append the extension attributes
        $result->setExtensionAttributes($extensibleAttribute);

        return $result;
    }

    /**
     * @return bool
     */
    private function estimationsHasErrors()
    {
        if (empty($this->estimations) || !isset($this->estimations['location_id']) || isset($this->estimations['error'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if estimation exists for the current shipping method
     * @return bool
     */
    private function estimationExists()
    {
        return isset($this->estimations['estimates'][$this->carrierCode]['methods'][$this->methodCode]['estimated_delivery_date']);
    }

    /**
     * @return array|integer
     */
    private function getEstimationTimestamp()
    {
        $estimations = $this->extractEstimationTimestamps();
        $displayMode = $this->estimatesHelper->getEstimationDisplayMode();
        switch ($displayMode) {
            case 'earliest':
                return reset($estimations);
                break;
            case 'latest':
                return end($estimations);
                break;
            default:
                return [reset($estimations), end($estimations)];
                break;
        }
    }

    /**
     * @return array
     */
    private function extractEstimationTimestamps()
    {
        $timestamps = [];
        $estimations = $this->estimations['estimates'][$this->carrierCode]['methods'][$this->methodCode]['estimated_delivery_date'];
        foreach ($estimations as $timestamp) {
            $timestamps[$timestamp] = $timestamp;
        }

        sort($timestamps);

        return $timestamps;
    }

    /**
     * @param $estimatedTimestamp
     * @return array
     */
    private function addEstimationRange($estimatedTimestamp)
    {
        $shipperMethod = $this->concatShipperMethod();
        $estimationsRange = $this->estimatesHelper->getApplyEstimationRangeTo() === 'all_shipping_methods' ?
            $this->estimatesHelper->getEstimationRange() :
            $this->estimatesHelper->getEstimationsRangeByShipperMethod($shipperMethod);

        if (empty($estimationsRange)) {
            return $estimatedTimestamp;
        }

        // memorize estimated timestamp
        $origEstimatedTimestamp = $estimatedTimestamp;
        // reset estimated timestamp
        $estimatedTimestamp = [];
        if (is_array($origEstimatedTimestamp)) {
            // it is already requested as range (ETA Display Mode When Multiple Products In Cart)
            $estimatedTimestamp[0] = $origEstimatedTimestamp[0];
            if (!isset($origEstimatedTimestamp[1])) { // in case the received timestamp didn't pass the validation
                $origEstimatedTimestamp[1] = $estimatedTimestamp[0];
            }
            $estimatedTimestamp[1] = strtotime('+' . $estimationsRange . ' days', $origEstimatedTimestamp[1]);

            return $estimatedTimestamp;
        }

        $estimatedTimestamp[0] = $origEstimatedTimestamp;
        $estimatedTimestamp[1] = strtotime('+' . $estimationsRange . ' days', $origEstimatedTimestamp);

        return $estimatedTimestamp;
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getNewDeliveryEstimations()
    {
        // clear current estimation
        $this->unsetEstimationQuotes();

        $items = $this->quote->getAllItems();
        $response = $this->estimatesDelivery->getEstimations($items);
        if (!$response) {
            $response = [];
        }

        $response['estimation_attempt'] = 'append';
        $this->estimatesDelivery->removeEmptyEstimates($response);

        // set new estimations
        $this->setEstimationQuotes($response);
    }

    /**
     * @return array
     */
    private function getEstimationQuotes()
    {
        if ($this->checkoutSession->getEstimationQuotes()) {
            return $this->json->unserialize(
                $this->checkoutSession->getEstimationQuotes()
            );
        }
        // Set an empty estimation attempt in case it was skipped (not called in observer)
        return ['estimation_attempt' => 'empty'];
    }

    /**
     * @param $response
     */
    private function setEstimationQuotes($response)
    {
        $this->estimations = $response;
        $this->checkoutSession->setEstimationQuotes(
            $this->json->serialize($response)
        );
    }

    /**
     * void
     */
    private function unsetEstimationQuotes()
    {
        $this->estimations = [];
        $this->checkoutSession->unsEstimationQuotes();
    }

    /**
     * @return string
     */
    private function concatShipperMethod()
    {
        return $this->carrierCode . '_' . $this->methodCode;
    }

    /**
     * @param $estimatedTimestamp
     * @return array|bool
     */
    private function validateTimestamp(&$estimatedTimestamp)
    {
        if (is_array($estimatedTimestamp)) {
            foreach ($estimatedTimestamp as $key => $timestamp) {
                if ((int) $timestamp > 0) {
                    $estimatedTimestamp[$key] = (int) $timestamp;
                    continue;
                }

                unset($estimatedTimestamp[$key]);
            }

            $estimatedTimestamp = array_values($estimatedTimestamp);

            return $estimatedTimestamp;
        }

        return (int) $estimatedTimestamp > 0 ? $estimatedTimestamp : FALSE;
    }
}
