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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Controller\Adminhtml\Category;
use Mageplaza\Shopbybrand\Helper\Data as HelperData;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class Edit
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Category
 */
class Edit extends Category
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * Edit constructor.
     *
     * @param HelperData $data
     * @param Data $jsonHelper
     * @param PageFactory $pageFactory
     * @param Registry $registry
     * @param CategoryFactory $categoryFactory
     * @param Context $context
     */
    public function __construct(
        HelperData $data,
        Data $jsonHelper,
        PageFactory $pageFactory,
        Registry $registry,
        CategoryFactory $categoryFactory,
        Context $context
    ) {
        $this->helper = $data;
        $this->jsonHelper = $jsonHelper;
        $this->registry = $registry;
        $this->resultPageFactory = $pageFactory;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $cat = $this->categoryFactory->create();
        if ($id = $this->getRequest()->getParam('cat_id')) {
            $cat->load($id);
            if (!$cat->getId()) {
                $this->messageManager->addErrorMessage(__('The category does not exist.'));

                return $this->_redirect('*/*/');
            }
        }

        //Set entered data if was error when we do save
        $data = $this->_session->getProductFormData(true);
        if (!empty($data)) {
            $cat->setData($data);
        }

        $this->registry->register('current_brand_category', $cat);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($cat->getId() ? $cat->getName() : __('New Category'));

        return $resultPage;
    }
}
