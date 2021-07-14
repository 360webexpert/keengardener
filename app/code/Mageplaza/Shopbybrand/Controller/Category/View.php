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

namespace Mageplaza\Shopbybrand\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Helper\Data;
use Mageplaza\Shopbybrand\Model\CategoryFactory as BrandCategoryModelFactory;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category as BrandCategoryResourceModel;

/**
 * Class View
 *
 * @package Mageplaza\Shopbybrand\Controller\Category
 */
class View extends Action
{
    /**
     * @type PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @type Data
     */
    protected $helper;

    /**
     * @var BrandCategoryModel
     */
    protected $categoryFactory;

    /**
     * @var BrandCategoryResourceModel
     */
    protected $categoryResource;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param BrandCategoryModelFactory $categoryFactory
     * @param BrandCategoryResourceModel $categoryResource
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        BrandCategoryModelFactory $categoryFactory,
        BrandCategoryResourceModel $categoryResource,
        Data $helper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('cat_id');
        if ($id && $this->helper->isEnabled()) {
            $categoryModel = $this->categoryFactory->create();
            $this->categoryResource->load($categoryModel, $id);
            $this->coreRegistry->register('current_brand_category', $categoryModel);

            return $this->resultPageFactory->create();
        }

        return $this->resultForwardFactory->create()->forward('noroute');
    }
}
