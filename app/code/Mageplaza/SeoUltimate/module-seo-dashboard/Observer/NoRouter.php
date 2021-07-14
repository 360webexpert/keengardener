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
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\SeoDashboard\Model\NoRouteFactory;
use Mageplaza\SeoDashboard\Model\NoRouterFactory;

/**
 * Class NoRouter
 * @package Mageplaza\SeoDashboard\Observer
 */
class NoRouter implements ObserverInterface
{
    /**
     * @type NoRouterFactory
     */
    protected $_noRouteFactory;

    /**
     * Constructor
     *
     * @param NoRouteFactory $noRouterFactory
     */
    function __construct(NoRouteFactory $noRouterFactory)
    {
        $this->_noRouteFactory = $noRouterFactory;
    }

    /**
     * Get all 404 routers
     *
     * @param Observer $observer
     *
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @type Http $request */
        $request = $observer->getEvent()->getRequest();
        $uri     = $request->getUri();

        $noRoute = $this->_noRouteFactory->create();
        if (!$noRoute->getCollection()->addFieldToFilter('uri', $uri)->getSize()) {
            $noRoute->setData([
                'uri' => $uri
            ])->save();
        }

        return $this;
    }
}
