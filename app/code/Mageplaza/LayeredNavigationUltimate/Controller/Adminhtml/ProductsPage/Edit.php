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

namespace Mageplaza\LayeredNavigationUltimate\Controller\Adminhtml\ProductsPage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\LayeredNavigationUltimate\Model\ProductsPageFactory;

/**
 * Class Edit
 * @package Mageplaza\LayeredNavigationUltimate\Controller\Adminhtml\ProductsPage
 */
class Edit extends Action
{
    /**
     * @var \Mageplaza\LayeredNavigationUltimate\Helper\Data
     */
    public $helper;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var ProductsPageFactory
     */
    public $productPageFactory;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * @var Data
     */
    public $jsonHelper;

    /**
     * Edit constructor.
     *
     * @param \Mageplaza\LayeredNavigationUltimate\Helper\Data $data
     * @param Data $jsonHelper
     * @param PageFactory $pageFactory
     * @param Registry $registry
     * @param ProductsPageFactory $productPageFactory
     * @param Context $context
     */
    public function __construct(
        \Mageplaza\LayeredNavigationUltimate\Helper\Data $data,
        Data $jsonHelper,
        PageFactory $pageFactory,
        Registry $registry,
        ProductsPageFactory $productPageFactory,
        Context $context
    ) {
        $this->helper = $data;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->resultPageFactory = $pageFactory;
        $this->productPageFactory = $productPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     * @var PageFactory
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $attCode = $this->getRequest()->getParam('attributeCode');
            $options = $this->helper->getAttributeOptions($attCode);
            if (!empty($options)) {
                return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($options));
            }
        }

        $page = $this->productPageFactory->create();
        if ($id = $this->getRequest()->getParam('page_id')) {
            $page->load($id);
            if (!$page->getId()) {
                $this->messageManager->addErrorMessage(__('The page doesnot exist.'));
                $this->_redirect('*/*/');

                return;
            }
        }

        //Set entered data if was error when we do save
        $data = $this->_session->getProductFormData(true);
        if (!empty($data)) {
            $page->setData($data);
        }

        $this->registry->register('current_page', $page);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($page->getId() ? $page->getName() : __('New Page'));

        return $resultPage;
    }
}
