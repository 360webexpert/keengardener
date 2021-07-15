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
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Shopbybrand\Controller\Adminhtml\Category;

/**
 * Class MassDelete
 * @package Mageplaza\Shopbybrand\Controller\Adminhtml\Category
 */
class MassDelete extends Category
{
    /**
     * @return void
     * @var PageFactory
     */
    public function execute()
    {
        $ids = $this->getRequest()->getParam('cat_id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select category.'));
        } else {
            $numOfSuccess = 0;
            foreach ($ids as $id) {
                try {
                    /** @var \Mageplaza\Shopbybrand\Model\Category $cat */
                    $cat = $this->_objectManager->create(\Mageplaza\Shopbybrand\Model\Category::class)->load($id);
                    $cat->delete();
                    $numOfSuccess++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('Cannot delete category with ID %1', $id));
                }
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $numOfSuccess));
        }

        $this->_redirect('*/*/');
    }
}
