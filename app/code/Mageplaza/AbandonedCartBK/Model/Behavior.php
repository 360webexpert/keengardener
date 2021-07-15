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
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;
use Mageplaza\AbandonedCart\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class Behavior
 * @package Mageplaza\AbandonedCart\Model
 */
class Behavior
{
    /**
     * @var QuoteCollection
     */
    private $quoteCollection;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var array
     */
    protected $dateRange = ['from' => null, 'to' => null];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Behavior constructor.
     *
     * @param QuoteCollection $quoteCollection
     * @param Data $helperData
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteCollection $quoteCollection,
        Data $helperData,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->quoteCollection = $quoteCollection;
        $this->helperData      = $helperData;
        $this->request         = $request;
        $this->logger          = $logger;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getData()
    {
        $chartData = [
            'emails'          => $this->totalAbandonedEmails(),
            'carts'           => $this->totalAbandonedCarts(),
            'shipping_fields' => $this->addressFieldsData('shipping'),
            'billing_fields'  => $this->addressFieldsData('billing'),
            'more_fields'     => $this->moreFields()
        ];

        return $chartData;
    }

    /**
     * @param null $from
     * @param null $toD
     *
     * @return QuoteResource\Collection
     * @throws Exception
     */
    public function quoteIsActives($from = null, $toD = null)
    {
        try {
            $dateRange = $this->helperData->getDateRangeFilter($from, $toD);
            $from      = $dateRange[0];
            $toD       = $dateRange[1];
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $quoteCollection = $this->quoteCollection->create();
        $quotes          = $quoteCollection->addFieldToFilter('is_active', 1);
        $customerGroup   = $this->helperData->getCustomerGroup();
        $store           = $this->helperData->getStoreFilter();

        if ($store) {
            $quotes->addFieldToFilter('store_id', ['eq' => $store]);
        }
        if ((int) $customerGroup !== 32000) {
            $quotes->addFieldToFilter('customer_group_id', ['eq' => $customerGroup]);
        }
        if ($from !== null) {
            $startDateValue = $this->helperData->getStartDateUTC($from);
            $quotes->addFieldToFilter('main_table.created_at', ['gteq' => $startDateValue]);
        }
        if ($toD !== null) {
            $endDateValue = $this->helperData->getEndDateUTC($toD);
            $quotes->addFieldToFilter('main_table.created_at', ['lteq' => $endDateValue]);
        }

        return $quotes;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function totalAbandonedCarts()
    {
        return $this->quoteIsActives()->getSize();
    }

    /**
     * @return int
     * @throws Exception
     */
    public function totalAbandonedEmails()
    {
        $emails = $this->quoteIsActives()->addFieldToFilter('customer_email', ['notnull' => true])->getSize();

        return $emails;
    }

    /**
     * @param $addressType
     *
     * @return array
     * @throws Exception
     */
    public function addressFieldsData($addressType)
    {
        $data = [];
        foreach ($this->getAddressFields() as $key => $value) {
            $data[$value] = $this->addressSelect($addressType)->addFieldToFilter(
                $value,
                ['notnull' => true]
            )->getSize();
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getAddressFields()
    {
        return [
            AddressInterface::FIRSTNAME,
            AddressInterface::LASTNAME,
            AddressInterface::TELEPHONE,
            AddressInterface::STREET,
            AddressInterface::CITY,
            AddressInterface::REGION_ID,
            AddressInterface::POSTCODE,
            AddressInterface::COUNTRY_ID
        ];
    }

    /**
     * @param $addressType
     *
     * @return QuoteResource\Collection
     * @throws Exception
     */
    public function addressSelect($addressType)
    {
        $quoteCollection   = $this->quoteCollection->create();
        $quoteAddressTable = $quoteCollection->getTable('quote_address');
        $address           = $this->quoteIsActives()->join(
            ['qa' => $quoteAddressTable],
            'qa.quote_id = main_table.entity_id'
        )->addFieldToFilter('address_type', ['eq' => $addressType]);

        return $address;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function moreFields()
    {
        return [
            'shipping_methods' => $this->totalShippingMethod(),
            'payment_methods'  => $this->totalPaymentMethod(),
            'coupons'          => $this->totalCoupon(),
            'giftwraps'        => $this->totalGiftWrap()
        ];
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function totalShippingMethod()
    {
        return $this->addressSelect('shipping')->addFieldToFilter('shipping_method', ['notnull' => true])->getSize();
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function totalPaymentMethod()
    {
        $quoteCollection   = $this->quoteCollection->create();
        $quotePaymentTable = $quoteCollection->getTable('quote_payment');
        $payments          = $this->quoteIsActives()->join(
            ['qp' => $quotePaymentTable],
            'qp.quote_id = main_table.entity_id'
        )->getSize();

        return $payments;
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function totalCoupon()
    {
        return $this->quoteIsActives()->addFieldToFilter('coupon_code', ['notnull' => true])->getSize();
    }

    /**
     * @return int|null
     * @throws Exception
     */
    protected function totalGiftWrap()
    {
        return $this->helperData->isModuleOutputEnabled('Mageplaza_Osc')
            ? $this->addressSelect('shipping')->addFieldToFilter(
                'used_gift_wrap',
                ['notnull' => true]
            )->getSize() : null;
    }
}
