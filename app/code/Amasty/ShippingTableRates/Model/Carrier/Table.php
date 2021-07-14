<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model\Carrier;

use Amasty\ShippingTableRates\Model\Rate\Provider;
use Amasty\ShippingTableRates\Model\ResourceModel\Label\Collection as LabelCollection;
use Amasty\ShippingTableRates\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Amasty\ShippingTableRates\Model\ResourceModel\Method\Collection as MethodCollection;
use Amasty\ShippingTableRates\Model\ResourceModel\Method\CollectionFactory as MethodCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Shipping Table Rate implementation
 */
class Table extends AbstractCarrier implements CarrierInterface
{
    const VARIABLE_DAY = '{day}';
    const VARIABLE_DLIVERY_NAME = '{name}';

    /**
     * @var string
     */
    protected $_code = 'amstrates';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var LabelCollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var MethodCollectionFactory
     */
    private $methodCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * @var Provider
     */
    private $rateProvider;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        LabelCollectionFactory $labelCollectionFactory,
        MethodCollectionFactory $methodCollectionFactory,
        StoreManagerInterface $storeManager,
        State $state,
        Provider $rateProvider,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->methodCollectionFactory = $methodCollectionFactory;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->rateProvider = $rateProvider;
    }

    /**
     * @param RateRequest $request
     *
     * @return bool|DataObject|Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        /** @var Result $result */
        $result = $this->rateResultFactory->create();
        /** @var LabelCollection $customLabelCollection */
        $customLabelCollection = $this->labelCollectionFactory->create();
        /** @var MethodCollection $methodCollection */
        $methodCollection = $this->methodCollectionFactory->create();

        $storeId = $this->getStoreId($request);

        $methodCollection
            ->addFieldToFilter('is_active', 1)
            ->addStoreFilter($storeId)
            ->addCustomerGroupFilter($this->getCustomerGroupId($request))
            ->addOrder('main_table.sort_order');

        $rates = $this->rateProvider->getRates($request, $methodCollection);
        $countOfRates = 0;

        foreach ($methodCollection as $customMethod) {
            $customLabelData = $customLabelCollection
                ->addFiltersByMethodIdStoreId($customMethod->getId(), $storeId)
                ->getLastItem();

            /** @var Method $method */
            $method = $this->rateMethodFactory->create();
            // record carrier information
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            if (isset($rates[$customMethod->getId()]['cost'])) {
                // record method information
                $method->setMethod($this->_code . $customMethod->getId());
                $label = $customLabelData->getLabel();

                if ($label === null || $label === '') {
                    $methodTitle = __($customMethod->getName());
                } else {
                    $methodTitle = __($label);
                }
                $methodTitle = str_replace(static::VARIABLE_DAY, $rates[$customMethod->getId()]['time'], $methodTitle);
                $methodTitle = str_replace(
                    static::VARIABLE_DLIVERY_NAME,
                    $rates[$customMethod->getId()]['name_delivery'],
                    $methodTitle
                );
                $method->setMethodTitle($methodTitle);

                $method->setCost($rates[$customMethod->getId()]['cost']);
                $method->setPrice($rates[$customMethod->getId()]['cost']);
                $method->setSortOrder($customMethod->getSortOrder());

                $method->setPos($customMethod->getPos());

                // add this rate to the result
                $result->append($method);
                $countOfRates++;
            }
        }

        if (($countOfRates == 0) && ($this->getConfigData('showmethod') == 1)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        }

        return $result;
    }

    /**
     * @param RateRequest $request
     *
     * @return int
     */
    private function getStoreId(RateRequest $request)
    {
        try {
            if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                return $this->getStoreIdFromQuoteItem($request);
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            //webapi fix
        }

        return $this->storeManager->getStore()->getId();
    }

    public function getAllowedMethods()
    {
        /** @var MethodCollection $collection */
        $collection = $this->methodCollectionFactory->create();
        $collection
            ->addFieldToFilter('is_active', 1);
        $arr = [];
        /** @var \Amasty\ShippingTableRates\Model\Method $method */
        foreach ($collection->getItems() as $method) {
            $methodCode = 'amstrates' . $method->getId();
            $arr[$methodCode] = $method->getName();
        }

        return $arr;
    }

    /**
     * @param $request
     * @return int
     */
    public function getCustomerGroupId($request)
    {
        $allItems = $request->getAllItems();

        if (!$allItems) {
            return 0;
        }

        foreach ($allItems as $item) {
            return $item->getProduct()->getCustomerGroupId();
        }
    }

    /**
     * @param $request
     * @return int
     */
    public function getStoreIdFromQuoteItem($request)
    {
        $allItems = $request->getAllItems();

        if (!$allItems) {
            return (int)true;
        }

        foreach ($allItems as $item) {
            return $item->getStoreId();
        }
    }
}
