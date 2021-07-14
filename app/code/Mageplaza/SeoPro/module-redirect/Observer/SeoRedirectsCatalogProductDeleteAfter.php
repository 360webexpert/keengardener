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
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Redirects\Helper\Data as HelperData;

/**
 * Class SeoRedirectsCatalogProductDeleteAfter
 * @package Mageplaza\Redirects\Observer
 */
class SeoRedirectsCatalogProductDeleteAfter implements ObserverInterface
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
     * @var ProductUrlPathGenerator
     */
    protected $productUrlPathGenerator;

    /**
     * SeoRedirectsCatalogProductDeleteAfter constructor.
     *
     * @param Session $backendSession
     * @param HelperData $helperConfig
     */
    public function __construct(
        Session $backendSession,
        HelperData $helperConfig
    ) {
        $this->backendSession = $backendSession;
        $this->helperConfig   = $helperConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helperConfig->isRedirectEnabled()) {
            $data = $this->backendSession->getData('product_deleted') ?: [];

            $data[] = $this->backendSession->getProductTmp() . $this->helperConfig->getProductUrlSuffix();
            $this->backendSession->setData('product_deleted', $data);
        }
    }
}
