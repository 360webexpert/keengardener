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

use Magento\Framework\App\Action\Action;

/**
 * Class Index
 * @package Mageplaza\LayeredNavigationUltimate\Controller\ProductsPage
 */
class View extends Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $_pageFactory;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $_jsonHelper;

    /** @var \Mageplaza\LayeredNavigationUltimate\Helper\Data */
    protected $_layerHelper;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Framework\Registry */
    protected $_coreRegistry;

    /** @var \Magento\Catalog\Api\CategoryRepositoryInterface */
    protected $_categoryRepository;

    /**
     * View constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Mageplaza\LayeredNavigationUltimate\Helper\Data $layerHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Mageplaza\LayeredNavigationUltimate\Helper\Data $layerHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Registry $coreRegistry
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
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
                'products'   => $layout->getBlock('layerultimate.productspage.view')->toHtml(),
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
