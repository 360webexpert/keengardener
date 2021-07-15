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
 * @package     Mageplaza_SeoCrosslinks
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term;

/**
 * Class Delete
 * @package Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term
 */
class Delete extends Term
{
    /**
     * execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->_resultRedirectFactory->create();
        $id             = $this->getRequest()->getParam('term_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Mageplaza\SeoCrosslinks\Model\Term $term */
                $term = $this->_termFactory->create();
                $term->load($id);
                $name = $term->getName();
                $term->delete();
                $this->messageManager->addSuccess(__('The Term has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_seocrosslinks_term_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('seo/*/');

                return $resultRedirect;
            } catch (Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_seocrosslinks_term_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('seo/*/edit', ['term_id' => $id]);

                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Term to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('seo/*/');

        return $resultRedirect;
    }
}
