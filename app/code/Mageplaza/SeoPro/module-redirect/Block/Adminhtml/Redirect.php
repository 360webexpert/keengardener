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
 * @package     Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Redirect
 * @package Mageplaza\Redirects\Block\Adminhtml
 */
class Redirect extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var $_typeProcess
     */
    protected $_typeProcess;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Do not add form button
     */
    protected function _construct()
    {
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->setTypeProcess();
        $this->setTemplate('redirect.phtml');

        $this->setChild('form', $this->getLayout()->createBlock(
            'Mageplaza\Redirects\Block\Adminhtml\Redirect\Form',
            '',
            ['data' => []]
        ));

        return $this;
    }

    /**
     * Get redirect url
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpredirect/redirect/save');
    }

    /**
     * Set type process
     * @return string
     */
    public function setTypeProcess()
    {
        $dataDeleted = '';
        switch ($this->_request->getFullActionName()) {
            case 'catalog_product_index':
                $this->_typeProcess = 'product';
                $dataDeleted        = $this->_backendSession->getProductDeleted();
                break;
            case 'catalog_category_edit':
                $dataDeleted        = $this->_backendSession->getCategoryDeleted();
                $this->_typeProcess = 'category';
                break;
            case 'cms_page_index':
                $dataDeleted        = $this->_backendSession->getPageDeleted();
                $this->_typeProcess = 'page';
                break;
        }
        $this->_backendSession->setSeoRedirectDataDeleted($dataDeleted);

        return $dataDeleted;
    }

    /**
     * Get type process
     * @return mixed
     */
    public function getTypeProcess()
    {
        return $this->_typeProcess;
    }
}
