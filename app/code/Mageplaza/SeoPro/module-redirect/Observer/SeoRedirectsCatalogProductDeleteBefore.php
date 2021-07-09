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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Observer;

use Magento\Backend\Model\Session;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Redirects\Helper\Data as HelperData;

/**
 * Class SeoRedirectsCatalogProductDeleteBefore
 * @package Mageplaza\Redirects\Observer
 */
class SeoRedirectsCatalogProductDeleteBefore implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var HelperData
     */
    protected $helperConfig;

    /**
     * @var Product
     */
    protected $product;

    /**
     * SeoRedirectsCatalogProductDeleteBefore constructor.
     *
     * @param Session $backendSession
     * @param HelperData $helperConfig
     * @param Product $product
     */
    public function __construct(
        Session $backendSession,
        HelperData $helperConfig,
        Product $product
    ) {
        $this->backendSession = $backendSession;
        $this->helperConfig   = $helperConfig;
        $this->product        = $product;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helperConfig->isRedirectEnabled()) {
            /** @var $product Product */
            $product = $this->product->load($observer->getEvent()->getProduct()->getId());
            $urlKey  = $product->getUrlKey();
            $url     = $this->product->formatUrlKey($urlKey === '' || $urlKey === null ? $product->getName() : $urlKey);
            $this->backendSession->setData('product_tmp', $url);
        }
    }
}
