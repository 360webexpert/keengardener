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
use Magento\Catalog\Model\Category;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Redirects\Helper\Data as HelperData;

/**
 * Class SeoRedirectsCatalogCategoryDeleteAfter
 * @package Mageplaza\Redirects\Observer
 */
class SeoRedirectsCatalogCategoryDeleteAfter implements ObserverInterface
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
     * @var CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * SeoRedirectsCatalogCategoryDeleteAfter constructor.
     *
     * @param Session $backendSession
     * @param HelperData $helperConfig
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     */
    public function __construct(
        Session $backendSession,
        HelperData $helperConfig,
        CategoryUrlPathGenerator $categoryUrlPathGenerator
    ) {
        $this->backendSession           = $backendSession;
        $this->helperConfig             = $helperConfig;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helperConfig->isRedirectEnabled()) {
            /** @var $category Category */
            $category = $observer->getEvent()->getCategory();

            $data = $this->backendSession->getData('category_deleted') ?: [];

            $data[] = $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category);
            $this->backendSession->setData('category_deleted', $data);
        }
    }
}
