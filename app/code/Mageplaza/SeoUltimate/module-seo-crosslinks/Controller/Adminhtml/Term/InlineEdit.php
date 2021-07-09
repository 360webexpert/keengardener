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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\SeoCrosslinks\Model\Term;
use Mageplaza\SeoCrosslinks\Model\TermFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\SeoCrosslinks\Controller\Adminhtml\Term
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    protected $_jsonFactory;

    /**
     * Term Factory
     *
     * @var TermFactory
     */
    protected $_termFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param TermFactory $termFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        TermFactory $termFactory
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_termFactory = $termFactory;

        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->_jsonFactory->create();
        $error      = false;
        $messages   = [];
        $postItems  = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error'    => true,
            ]);
        }
        foreach (array_keys($postItems) as $termId) {
            /** @var Term $term */
            $term = $this->_termFactory->create()->load($termId);
            try {
                $termData = $postItems[$termId];//todo: handle dates
                $term->addData($termData);
                $term->save();
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithTermId($term, $e->getMessage());
                $error      = true;
            } catch (RuntimeException $e) {
                $messages[] = $this->getErrorWithTermId($term, $e->getMessage());
                $error      = true;
            } catch (Exception $e) {
                $messages[] = $this->getErrorWithTermId(
                    $term,
                    __('Something went wrong while saving the Term.')
                );
                $error      = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => $error
        ]);
    }

    /**
     * Add Term id to error message
     *
     * @param Term $term
     * @param string $errorText
     *
     * @return string
     */
    protected function getErrorWithTermId(Term $term, $errorText)
    {
        return '[Term ID: ' . $term->getId() . '] ' . $errorText;
    }
}
