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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mageplaza\LayeredNavigationUltimate\Helper\Data as LayerHelper;

/**
 * Class CheckSeoUrlObserver
 * @package Mageplaza\LayeredNavigationUltimate\Observer
 */
class CheckSeoUrlObserver implements ObserverInterface
{
    /** @var \Mageplaza\LayeredNavigationUltimate\Helper\Data */
    protected $_helper;

    /**
     * CheckSeoUrlObserver constructor.
     *
     * @param \Mageplaza\LayeredNavigationUltimate\Helper\Data $helper
     */
    public function __construct(LayerHelper $helper)
    {
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isEnabled()) {
            return $this;
        }

        $object = $observer->getEvent()->getObject();
        $pathInfo = trim($object->getData('pathInfo'), '/');

        $urlSuffix = $this->_helper->getUrlSuffix();
        if ($urlSuffix && ($urlSuffix != '/')) {
            $pos = strpos($pathInfo, $urlSuffix);
            if ($pos) {
                $pathInfo = substr($pathInfo, 0, $pos);
            } else {
                return $this;
            }
        }

        $page = $this->_helper->getPageByRoute($pathInfo);
        if ($page && $page->getId()) {
            $object->setData('rewrite', true);
        }

        return $this;
    }
}
