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
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Shopbybrand\Helper\Data;

/**
 * Class AddBrandProductStructuredData
 * @package Mageplaza\Shopbybrand\Observer
 */
class AddBrandProductStructuredData implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * AddBrandProductStructuredData constructor.
     *
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $objectStructuredData = $observer->getData('structured_data');
        $productStructuredData = $objectStructuredData->getMpdata();

        if ($brand_product = $this->_dataHelper->getProductBrand()) {
            $productStructuredData['brand']['@type'] = 'Thing';
            $productStructuredData['brand']['name'] = $brand_product->getValue();
            $objectStructuredData->setMpdata($productStructuredData);
        }
    }
}
