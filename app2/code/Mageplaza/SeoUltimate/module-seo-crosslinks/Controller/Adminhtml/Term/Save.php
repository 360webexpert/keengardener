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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term
 */
class Save extends Term
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $data              = $this->getRequest()->getPost('term');
        $data['stores']    = implode(',', $data['stores']);
        $data['apply_for'] = implode(',', $data['apply_for']);
        $resultRedirect    = $this->resultRedirectFactory->create();
        if ($data) {
            $term = $this->_initTerm();
            $term->setData($data);
            $this->_eventManager->dispatch('mageplaza_seocrosslinks_term_prepare_save', [
                'term'    => $term,
                'request' => $this->getRequest()
            ]);
            try {
                $term->save();
                $this->messageManager->addSuccess(__('The Term has been saved.'));
                $this->_session->setMageplazaSeoCrosslinksTermData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('seo/*/edit', [
                        'term_id'  => $term->getId(),
                        '_current' => true
                    ]);

                    return $resultRedirect;
                }
                $resultRedirect->setPath('seo/*/');

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Term.'));
            }
            $this->_getSession()->setMageplazaSeoCrosslinksTermData($data);

            $resultRedirect->setPath('seo/*/edit', [
                'term_id'  => $term->getId(),
                '_current' => true
            ]);

            return $resultRedirect;
        }
        $resultRedirect->setPath('seo/*/');

        return $resultRedirect;
    }
}
