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

namespace Mageplaza\LayeredNavigationUltimate\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RouterInterface;
use Mageplaza\LayeredNavigationUltimate\Helper\Data;

/**
 * Class Router
 * @package Mageplaza\LayeredNavigationUltimate\Controller
 */
class Router implements RouterInterface
{
    /** @var ActionFactory */
    protected $actionFactory;

    /** @var Data */
    protected $_helper;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param Data $helper
     */
    public function __construct(
        ActionFactory $actionFactory,
        Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->_helper = $helper;
    }

    /**
     * Validate and Match Layer Products Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $routePath = explode('/', $identifier);

        if (!$this->_helper->isEnabled() || (sizeof($routePath) != 1)) {
            return null;
        }

        $route = $routePath[0];
        $urlSuffix = $this->_helper->getUrlSuffix();
        if ($urlSuffix) {
            $pos = strpos($route, $urlSuffix);
            if ($pos !== false) {
                $route = substr($route, 0, $pos);
            } else {
                return null;
            }
        }

        $page = $this->_helper->getPageByRoute($route);
        if (!$page || !$page->getId()) {
            return null;
        }

        $request->setModuleName('mplayer')
            ->setControllerName('productspage')
            ->setActionName('view')
            ->setParam('page_id', $page->getId())
            ->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier)
            ->setPathInfo('/mplayer/productspage/view');

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
