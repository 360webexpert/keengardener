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

namespace Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Shopbybrand\Helper\Data as HelperData;
use Mageplaza\Shopbybrand\Model\BrandFactory;

/**
 * Class Update
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Attribute
 */
class Update extends Action
{
    /**
     * @type Data
     */
    protected $_jsonHelper;

    /**
     * @type HelperData
     */
    protected $_brandHelper;

    /**
     * @type Repository
     */
    protected $_productAttributeRepository;

    /**
     * @type BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @type StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Update constructor.
     *
     * @param Context $context
     * @param Data $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param Repository $productRespository
     * @param PageFactory $resultPageFactory
     * @param HelperData $brandHelper
     * @param BrandFactory $brandFactory
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        StoreManagerInterface $storeManager,
        Repository $productRespository,
        PageFactory $resultPageFactory,
        HelperData $brandHelper,
        BrandFactory $brandFactory
    ) {
        parent::__construct($context);

        $this->_jsonHelper = $jsonHelper;
        $this->_brandHelper = $brandHelper;
        $this->_productAttributeRepository = $productRespository;
        $this->_brandFactory = $brandFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = ['success' => false];
        $optionId = (int) $this->getRequest()->getParam('id');
        $attributeCode = $this->_brandHelper->getAttributeCode();
        $options = $this->_productAttributeRepository->get($attributeCode)->getOptions();
        foreach ($options as $option) {
            if ($option->getValue() == $optionId) {
                $result = ['success' => true];
                break;
            }
        }

        if (!$result['success']) {
            $result['message'] = __('Attribute option does not exist.');
        } else {
            $store = $this->getRequest()->getParam('store') ?: 0;
            $brand = $this->_brandFactory->create()->loadByOption($optionId, $store);
            if (!$brand->getUrlKey()) {
                $brand->setUrlKey($this->_brandHelper->formatUrlKey($brand->getDefaultValue()));

                $defaultBlock = $this->_brandHelper->getBrandDetailConfig('default_block', $store);
                if ($defaultBlock) {
                    $brand->setStaticBlock($defaultBlock);
                }
            }

            /** @var Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();

            $result['html'] = $resultPage->getLayout()->getBlock('brand.attribute.html')
                ->setOptionData($brand->getData())
                ->toHtml();
            $result['switcher'] = $resultPage->getLayout()->getBlock('brand.store.switcher')
                ->toHtml();
        }

        $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
    }
}
