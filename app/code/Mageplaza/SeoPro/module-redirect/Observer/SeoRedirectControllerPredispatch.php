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

use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Mageplaza\Redirects\Helper\Data as HelperData;

/**
 * Class SeoRedirectControllerPredispatch
 * @package Mageplaza\Redirects\Observer
 */
class SeoRedirectControllerPredispatch implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var \mageplaza\Redirects\Helper\Data
     */
    protected $helperConfig;

    /**
     * SeoRedirectControllerPredispatch constructor.
     *
     * @param UrlInterface $url
     * @param Http $http
     * @param \mageplaza\Redirects\Helper\Data $helperConfig
     */
    public function __construct(
        UrlInterface $url,
        Http $http,
        HelperData $helperConfig
    ) {
        $this->url          = $url;
        $this->http         = $http;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helperConfig->isEnableBetter404page() && $observer->getRequest()->getFullActionName() == 'cms_noroute_index') {
            /** Redirect to home page */
            $this->http->setRedirect($this->url->getUrl());
        }
    }
}
