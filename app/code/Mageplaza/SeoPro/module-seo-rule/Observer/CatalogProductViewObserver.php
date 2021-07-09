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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SeoRule\Model\Rule\Source\Type;

/**
 * Class CatalogProductViewObserver
 * @package Mageplaza\SeoRule\Observer
 */
class CatalogProductViewObserver extends SeoRuleAbstract implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helperData->isEnableSeoRule()) {
            $product = $observer->getEvent()->getProduct();
            $this->setMetaData($product, 'product_id', Type::PRODUCTS);
        }

        return $this;
    }
}
