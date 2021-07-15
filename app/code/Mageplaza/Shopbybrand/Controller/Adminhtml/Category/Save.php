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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filter\FilterManager;
use Mageplaza\Shopbybrand\Controller\Adminhtml\Category;
use Mageplaza\Shopbybrand\Model\CategoryFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Category
 */
class Save extends Category
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @type FilterManager
     */
    protected $_filter;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param CategoryFactory $categoryFactory
     * @param FilterManager $filter
     */
    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        FilterManager $filter
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->_filter = $filter;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $catId = $this->getRequest()->getParam('cat_id');
        $data = $this->getRequest()->getParams();
        if ($data) {
            $this->prepareData($data);
            $cat = $this->categoryFactory->create();
            if ($catId) {
                $cat->load($catId);
            }

            $errors = $this->validateData($data);
            if (count($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addErrorMessage($error);
                }

                return $catId ? $this->_redirect('*/*/edit', ['cat_id' => $catId]) : $this->_redirect('*/*/new');
            }

            $cat->setData($data);

            try {
                $cat->save();
                $this->messageManager->addSuccessMessage(__('The category has been saved successfully.'));
                $this->_session->setProductFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['cat_id' => $cat->getId()]);

                    return;
                }

                $this->_redirect('*/*/');

                return;
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the category.'));
            }

            $this->_redirect('*/*/edit', ['cat_id' => $this->getRequest()->getParam('cat_id')]);

            return;
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function prepareData(&$data)
    {
        $data['url_key'] = $this->formatUrlKey($data['url_key']);

        $this->_getSession()->setProductFormData($data);

        return $data;
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     *
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * Validate input data
     *
     * @param array $data
     *
     * @return array
     */
    public function validateData(array $data)
    {
        $errors = [];

        if (!isset($data['name'])) {
            $errors[] = __('Please enter the category name.');
        }

        if (!isset($data['url_key'])) {
            $errors[] = __('Please enter the category url key.');
        } else {
            $pages = $this->categoryFactory->create()->getCollection()
                ->addFieldToFilter('url_key', $data['url_key']);
            if (count($pages)) {
                if (!isset($data['cat_id'])) {
                    $errors[] = __('The url key "%1" has been used.', $data['url_key']);
                } else {
                    foreach ($pages as $page) {
                        if ($page->getId() != $data['cat_id']) {
                            $errors[] = __('The url key "%1" has been used.', $data['url_key']);
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get input data function
     * @return array
     */
    public function getData()
    {
        return $this->getRequest()->getParams();
    }
}
