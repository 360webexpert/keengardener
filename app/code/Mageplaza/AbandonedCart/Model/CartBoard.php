<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Model;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Stdlib\DateTime\DateTime as StdlibDateTime;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;
use Magento\Store\Model\Store;
use Mageplaza\AbandonedCart\Helper\Data;
use Mageplaza\AbandonedCart\Model\ResourceModel\ProductReport;
use Psr\Log\LoggerInterface;

/**
 * Class CartBoard
 * @package Mageplaza\AbandonedCart\Model
 */
class CartBoard
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var array
     */
    protected $dateRange = ['from' => null, 'to' => null];

    /**
     * @var Store
     */
    private $storeManager;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var StdlibDateTime
     */
    private $time;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var ProductReport
     */
    private $productReport;

    /**
     * CartBoard constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $quoteResource
     * @param QuoteCollection $quoteCollection
     * @param Data $helperData
     * @param Store $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param StdlibDateTime $time
     * @param LoggerInterface $logger
     * @param ProductFactory $productFactory
     * @param ProductResource $productResource
     * @param ProductReport $productReport
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $quoteResource,
        QuoteCollection $quoteCollection,
        Data $helperData,
        Store $storeManager,
        CurrencyFactory $currencyFactory,
        StdlibDateTime $time,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        ProductResource $productResource,
        ProductReport $productReport
    ) {
        $this->quoteFactory    = $quoteFactory;
        $this->quoteResource   = $quoteResource;
        $this->quoteCollection = $quoteCollection;
        $this->helperData      = $helperData;
        $this->storeManager    = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->time            = $time;
        $this->logger          = $logger;
        $this->productFactory  = $productFactory;
        $this->productResource = $productResource;
        $this->productReport   = $productReport;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getData()
    {
        $storeId     = $this->helperData->getStoreFilter() ?: null;
        $timeMeasure = $this->helperData->getRealtimeConfig('time_measure', $storeId);
        $data        = [];
        try {
            $data = [
                'realtime'    => $this->getCartData($this->getRealtimeQuote($timeMeasure)),
                'abandoned'   => $this->getCartData($this->getAbandonedQuote($timeMeasure)),
                'recoverable' => $this->getCartData($this->getRecoverableQuote($timeMeasure)),
                'converted'   => $this->getCartData($this->getConvertQuote())
            ];
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $data;
    }

    /**
     * @param $isActive
     * @param null $from
     * @param null $toD
     *
     * @return Collection
     * @throws Exception
     */
    private function filterDateRange($isActive, $from = null, $toD = null)
    {
        try {
            $dateRangeFilter = $this->helperData->getDateRangeFilter($from, $toD);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        $from = $dateRangeFilter[0];
        $toD  = $dateRangeFilter[1];

        $quoteCollection = $this->quoteCollection->create();
        /** @var  Collection $quotes */
        $quotes = $quoteCollection->addFieldToFilter('is_active', $isActive)->setOrder('updated_at');

        $store = $this->helperData->getStoreFilter();

        if ($store) {
            $quotes->addFieldToFilter('store_id', ['eq' => $store]);
        }
        $connection = $quoteCollection->getConnection();
        $dateTime   = $connection->getDatePartSql($this->productReport->getStoreTZOffsetQuery(
            ['main_table' => $quotes->getMainTable()],
            'main_table.updated_at',
            $from,
            $toD
        ));

        if ($from !== null) {
            $quotes->addFieldToFilter($dateTime, ['gteq' => $from]);
        }
        if ($toD !== null) {
            $quotes->addFieldToFilter($dateTime, ['lteq' => $toD]);
        }

        return $quotes;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function quoteIsActives()
    {
        return $this->filterDateRange(1);
    }

    /**
     * @param Collection $quotes
     *
     * @return array
     */
    public function getCartData($quotes)
    {
        $quotes->load();

        $abandonedData = [];

        /** @var Quote $item */
        foreach ($quotes->getItems() as $item) {
            $productImages = [];
            foreach ($this->getProductCollection($item->getId()) as $itemCollection) {
                $productImages[] = $this->getProductImage($itemCollection, $item->getStoreId());
            }
            $abandonedData[] = [
                'customerEmail'  => $item->getCustomerEmail(),
                'customerName'   => $item->getCustomerFirstname(),
                'total'          => $this->getCurrentCurrencySymbol($item->getStoreId()) .
                    round($item->getBaseGrandTotal(), 2),
                'differenceTime' => $this->getDifferenceTime($item->getUpdatedAt()),
                'productImages'  => $productImages
            ];
        }

        return $abandonedData;
    }

    /**
     * @param string $timeMeasure
     *
     * @return Collection
     * @throws Exception
     */
    protected function getRealtimeQuote($timeMeasure)
    {
        $quotes = $this->quoteIsActives();

        if ($timeMeasure) {
            $quotes->getSelect()->where('`updated_at` >= DATE_SUB(NOW(), INTERVAL ' . $timeMeasure . ' MINUTE)');
        }

        return $quotes;
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function getConvertQuote()
    {
        return $this->filterDateRange(0);
    }

    /**
     * @param $timeMeasure
     *
     * @return Collection
     * @throws Exception
     */
    protected function getRecoverableQuote($timeMeasure)
    {
        $quotes = $this->quoteIsActives()->addFieldToFilter('customer_email', ['notnull' => true]);
        if ($timeMeasure) {
            $quotes->getSelect()->where('`updated_at` <= DATE_SUB(NOW(), INTERVAL ' . $timeMeasure . ' MINUTE)');
        }

        return $quotes;
    }

    /**
     *
     * @param string $timeMeasure
     *
     * @return Collection
     * @throws Exception
     */
    protected function getAbandonedQuote($timeMeasure)
    {
        $quotes = $this->quoteIsActives()->addFieldToFilter('customer_email', ['null' => true]);
        if ($timeMeasure) {
            $quotes->getSelect()->where('`updated_at` <= DATE_SUB(NOW(), INTERVAL ' . $timeMeasure . ' MINUTE)');
        }

        return $quotes;
    }

    /**
     * @param $quoteId
     *
     * @return array|Item[]
     */
    public function getProductCollection($quoteId)
    {
        $items = [];

        $quote = $this->quoteFactory->create();
        $this->quoteResource->load($quote, $quoteId);
        if ($quote) {
            return $quote->getAllVisibleItems();
        }

        return $items;
    }

    /**
     * @param $item
     * @param $storeId
     *
     * @return mixed|null
     */
    public function getProductImage($item, $storeId)
    {
        $product = $this->productFactory->create();
        if ($item->getProductType() === 'configurable') {
            $productId = $product->getIdBySku($item->getSku());
        } else {
            $productId = $item->getProductId();
        }

        /** @var  Product $product */
        $product = $this->productFactory->create();
        $this->productResource->load($product, $productId);
        /** @var Store $store */
        $store    = $this->storeManager->load($storeId);
        $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();

        return str_replace('\\', '/', $imageUrl);
    }

    /**
     * @param string $updatedTime
     *
     * @return string
     */
    protected function getDifferenceTime($updatedTime)
    {
        $time = $this->time->date();
        $diff = date_diff(date_create($updatedTime), date_create($time));

        return $diff->format('%d days,%H hours,%i minutes ago');
    }

    /**
     * @param $storeId
     *
     * @return string
     */
    protected function getCurrentCurrencySymbol($storeId)
    {
        $currencyCode = $this->storeManager->load($storeId)->getCurrentCurrencyCode();

        return $this->currencyFactory->create()->load($currencyCode)->getCurrencySymbol();
    }
}
