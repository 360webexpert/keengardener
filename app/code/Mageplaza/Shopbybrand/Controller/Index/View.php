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

namespace Mageplaza\Shopbybrand\Controller\Index;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class View
 *
 * @package Mageplaza\Shopbybrand\Controller\Index
 */
class View extends Action
{
    /**
     * @type PageFactory
     */
    protected $resultPageFactory;

    /**
     * @type Helper
     */
    protected $helper;

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @type ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @type Registry
     */
    protected $_coreRegistry;

    /**
     * @type Data
     */
    protected $_jsonHelper;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Helper $helper
     * @param BrandFactory $brandFactory
     * @param Registry $coreRegistry
     * @param ForwardFactory $resultForwardFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param Data $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Helper $helper,
        BrandFactory $brandFactory,
        Registry $coreRegistry,
        ForwardFactory $resultForwardFactory,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandFactory = $brandFactory;
        $this->helper = $helper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if ($this->helper->isEnabled() && $brand = $this->_initBrand()) {
            $this->getRequest()->setParam($this->helper->getAttributeCode(), $brand->getId());
            $page = $this->resultPageFactory->create();
            $page->getConfig()->addBodyClass('page-products');

            $status = '';
            if ($this->helper->showQuickView()) {
                $imageUrl = $this->helper->getBrandImageUrl($brand);
                $brand->setImage($imageUrl);
                $status = 'ok';
            }
            if ($this->getRequest()->isAjax()) {
                $layout = $page->getLayout();
                $result = [
                    'products'   => $layout->getBlock('brand.category.products.list')->toHtml(),
                    'navigation' => $layout->getBlock('catalog.leftnav')->toHtml(),
                    'brand'      => $brand->getData(),
                    'status'     => $status
                ];

                return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
            }

            return $page;
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }

    /**
     * @return bool
     */
    protected function _initBrand()
    {
        $urlKey = $this->getRequest()->getParam('brand_key');
        if (!$urlKey) {
            return false;
        }

        $currentBrand = false;
        try {
            $brandCollection = $this->_brandFactory->create()->getBrandCollection();
            foreach ($brandCollection as $brand) {
                if ($this->helper->processKey($brand) == $urlKey) {
                    $currentBrand = $brand;
                    break;
                }
            }

            $category = $this->categoryRepository->get(
                $this->_storeManager->getStore()->getRootCategoryId()
            );
        } catch (Exception $e) {
            return false;
        }

        $this->_coreRegistry->register('current_category', $category);
        if ($currentBrand) {
            $this->_coreRegistry->register('current_brand', $currentBrand);
        }

        return $currentBrand;
    }
}
