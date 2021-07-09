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

namespace Mageplaza\Shopbybrand\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;

/**
 * Class Router
 *
 * @package Mageplaza\Shopbybrand\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var BrandHelper
     */
    protected $_helper;

    /**
     * Router constructor.
     *
     * @param ActionFactory $actionFactory
     * @param BrandHelper $helper
     */
    public function __construct(
        ActionFactory $actionFactory,
        BrandHelper $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->_helper = $helper;
    }

    /**
     * Validate and Match Brand Page and modify request
     *
     * @param RequestInterface $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix = $this->_helper->getUrlSuffix();
        if ($urlSuffix) {
            $pos = strpos($identifier, $urlSuffix);
            if ($pos) {
                $identifier = substr($identifier, 0, $pos);
            }
        }

        $routePath = explode('/', $identifier);
        $routeSize = count($routePath);
        if (!$this->_helper->isBrandRoute($routePath, $routeSize)) {
            return null;
        }

        $request->setModuleName('mpbrand')->setAlias(
            Url::REWRITE_REQUEST_PATH_ALIAS,
            trim($request->getPathInfo(), '/')
        );

        if ($routeSize === 1) {
            $request->setControllerName('index')
                ->setActionName('index')
                ->setPathInfo('/mpbrand/index/index');
        } elseif ($routePath[1] === BrandHelper::CATEGORY) {
            $catId = $this->_helper->getCategoryByUrlKey($routePath[2]);
            $request->setControllerName('category')
                ->setActionName('view')
                ->setParam('cat_id', $catId)
                ->setPathInfo('/mpbrand/category/view');
        } else {
            $request->setControllerName('index')
                ->setActionName('view')
                ->setParam('brand_key', $routePath[1])
                ->setPathInfo('/mpbrand/index/view');
        }

        return $this->actionFactory->create(Forward::class);
    }
}
