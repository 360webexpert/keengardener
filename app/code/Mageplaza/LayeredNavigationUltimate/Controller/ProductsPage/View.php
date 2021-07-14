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

namespace Mageplaza\LayeredNavigationUltimate\Controller\ProductsPage;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\LayeredNavigationUltimate\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\LayeredNavigationUltimate\Controller\ProductsPage
 */
class View extends Action
{
    /** @var PageFactory */
    protected $_pageFactory;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $_jsonHelper;

    /** @var Data */
    protected $_layerHelper;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var Registry */
    protected $_coreRegistry;

    /** @var CategoryRepositoryInterface */
    protected $_categoryRepository;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param Data $layerHelper
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        Data $layerHelper,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Registry $coreRegistry
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->_layerHelper = $layerHelper;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_categoryRepository = $categoryRepository;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page|void
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $pageId = $this->getRequest()->getParam('page_id');
        $page = $this->_layerHelper->getPageById($pageId);
        if (!$page || !$page->getId()) {
            $this->_forward('noroute');

            return;
        }

        $this->_initParam($page);

        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
        $this->_request->setParams(['id' => $rootCategoryId]);

        $category = $this->_categoryRepository->get($rootCategoryId);
        $this->_coreRegistry->register('current_category', $category);

        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->addBodyClass('page-products');

        if ($this->getRequest()->isAjax()) {
            $layout = $resultPage->getLayout();
            $result = [
                'products' => $layout->getBlock('layerultimate.productspage.view')->toHtml(),
                'navigation' => $layout->getBlock('catalog.leftnav')->toHtml()
            ];

            return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
        }

        return $resultPage;
    }

    /**
     * @param $page
     *
     * @return $this
     */
    protected function _initParam($page)
    {
        $params = [];

        if ($page->getDefaultAttributes()) {
            $defaultAttrs = $this->_jsonHelper->jsonDecode($page->getDefaultAttributes());
            foreach ($defaultAttrs as $attr) {
                $attributeOption = explode('=', $attr);
                $params[$attributeOption[0]][] = $attributeOption[1];
            }

            $defaultParams = $this->getRequest()->getParams();
            foreach ($params as $key => $value) {
                if (isset($defaultParams[$key])) {
                    $value = array_merge($value, explode(',', $defaultParams[$key]));
                }
                $params[$key] = implode(',', array_unique($value));
            }

            $this->getRequest()->setParams($params);
        }

        $this->_coreRegistry->register('current_product_page', $page);
        $this->_coreRegistry->register('current_product_page_params', $params);

        return $this;
    }
}
