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

namespace Mageplaza\SeoCrosslinks\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Framework\Registry;
use Mageplaza\SeoCrosslinks\Model\TermFactory;

/**
 * Class Term
 * @package Mageplaza\SeoCrosslinks\Controller\Adminhtml
 */
abstract class Term extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_SeoCrosslinks::term';

    /**
     * Term Factory
     *
     * @var TermFactory
     */
    protected $_termFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Result redirect factory
     *
     * @var RedirectFactory
     */
    protected $_resultRedirectFactory;

    /**
     * constructor
     *
     * @param TermFactory $termFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        TermFactory $termFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->_termFactory           = $termFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_resultRedirectFactory = $context->getResultRedirectFactory();

        parent::__construct($context);
    }

    /**
     * Init Term
     *
     * @return \Mageplaza\SeoCrosslinks\Model\Term
     */
    protected function _initTerm()
    {
        $termId = (int) $this->getRequest()->getParam('term_id');
        /** @var \Mageplaza\SeoCrosslinks\Model\Term $term */
        $term = $this->_termFactory->create();
        if ($termId) {
            $term->load($termId);
        }
        $this->_coreRegistry->register('mageplaza_seocrosslinks_term', $term);

        return $term;
    }
}
