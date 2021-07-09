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
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * @var \Mageplaza\LayeredNavigationUltimate\Model\ProductsPageFactory
     */
    public $productPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * Edit constructor.
     *
     * @param \Mageplaza\LayeredNavigationUltimate\Helper\Data $data
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\LayeredNavigationUltimate\Model\ProductsPageFactory $productPageFactory
     * @param Action\Context $context
     */
    public function __construct(
        \Mageplaza\LayeredNavigationUltimate\Helper\Data $data,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Registry $registry,
        \Mageplaza\LayeredNavigationUltimate\Model\ProductsPageFactory $productPageFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->helper = $data;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->resultPageFactory = $pageFactory;
        $this->productPageFactory = $productPageFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     * @var \Magento\Framework\View\Result\PageFactory
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
