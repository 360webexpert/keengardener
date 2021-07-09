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
use Magento\Cms\Model\Page;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\Redirects\Helper\Data as HelperData;

/**
 * Class SeoRedirectsCmsPageDeleteAfter
 * @package Mageplaza\Redirects\Observer
 */
class SeoRedirectsCmsPageDeleteAfter implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var \mageplaza\Redirects\Helper\Data
     */
    protected $helperConfig;

    /**
     * SeoRedirectsCmsPageDeleteAfter constructor.
     *
     * @param Session $backendSession
     * @param \mageplaza\Redirects\Helper\Data $helperConfig
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
            /** @var $cmsPage Page */
            $cmsPage = $observer->getEvent()->getObject();

            $data = $this->backendSession->getData('page_deleted') ?: [];

            $data[] = $cmsPage->getIdentifier();
            $this->backendSession->setData('page_deleted', $data);
        }
    }
}
