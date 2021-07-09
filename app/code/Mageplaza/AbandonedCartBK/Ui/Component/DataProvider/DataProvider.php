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

namespace Mageplaza\AbandonedCart\Ui\Component\DataProvider;

use Magento\Directory\Model\Currency;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as AbstractProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DataProvider
 * @package Mageplaza\AbandonedCart\Ui\Component\DataProvider
 */
class DataProvider extends AbstractProvider
{
    /**
     * @var FormatInterface
     */
    protected $basePriceFormat;

    /**
     * @var Currency
     */
    protected $baseCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );

        $this->storeManager = $storeManager;
    }

    /**
     * update url
     */
    protected function prepareUpdateUrl()
    {
        if ($period = ($this->request->getParam('period') !== null)) {
            $this->data['config']['filter_url_params']['period'] = $period;
        }
        if ($store = ($this->request->getParam('store') !== null)) {
            $this->data['config']['filter_url_params']['store'] = $store;
        }
        if ($customer_group_id = ($this->request->getParam('customer_group_id') !== null)) {
            $this->data['config']['filter_url_params']['customer_group_id'] = $customer_group_id;
        }

        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }

        foreach ((array) $this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ($paramValue === '*') {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
        }
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        $data                = $this->searchResultToOutput($this->getSearchResult());
        $data['formatPrice'] = $this->getBasePriceFormat();
        foreach ((array) $data['items'] as &$item) {
            $item['base_currency_code'] = $this->getBaseCurrency()->getCode();
        }

        return $data;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getBasePriceFormat()
    {
        if (!$this->basePriceFormat) {
            $code = $this->getBaseCurrency()->getCode();

            $this->basePriceFormat = ObjectManager::getInstance()->get(FormatInterface::class)
                ->getPriceFormat(null, $code);
        }

        return $this->basePriceFormat;
    }

    /**
     * @return Currency
     * @throws NoSuchEntityException
     */
    protected function getBaseCurrency()
    {
        if (!$this->baseCurrency) {
            $code = $this->storeManager->getStore(0)->getBaseCurrencyCode();

            $this->baseCurrency = ObjectManager::getInstance()->get(Currency::class)->load($code);
        }

        return $this->baseCurrency;
    }
}
