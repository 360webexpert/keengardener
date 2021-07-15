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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Shopbybrand\Model\Config\Source\MetaRobots;

/**
 * Class Category
 *
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Category\Edit\Tab
 */
class Category extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Enabledisable
     */
    protected $_booleanOptions;

    /**
     * @var MetaRobots
     */
    protected $metaRobotsOptions;

    /**
     * Category constructor.
     *
     * @param MetaRobots $metaRobotsOptions
     * @param Enabledisable $booleanOptions
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        MetaRobots $metaRobotsOptions,
        Enabledisable $booleanOptions,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        array $data = []
    ) {
        $this->_booleanOptions = $booleanOptions;
        $this->_systemStore = $systemStore;
        $this->metaRobotsOptions = $metaRobotsOptions;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model \Mageplaza\Shopbybrand\Model\Category */
        $model = $this->_coreRegistry->registry('current_brand_category');
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('cat_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Category Information')]);

        if ($model->getId()) {
            $fieldset->addField('cat_id', 'hidden', ['name' => 'cat_id']);
        }

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Name'),
            'title'    => __('Name'),
            'required' => true
        ]);

        $fieldset->addField('url_key', 'text', [
            'name'     => 'url_key',
            'label'    => __('Url key'),
            'title'    => __('Url key'),
            'required' => true
        ]);

        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', [
                'name'   => 'store_ids',
                'label'  => __('Stores view'),
                'title'  => __('Stores view'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, true)
            ]);
        }

        $fieldset->addField('status', 'select', [
            'name'   => 'status',
            'label'  => __('Status'),
            'title'  => __('Status'),
            'values' => $this->_booleanOptions->toOptionArray()
        ]);
        $fieldset->addField('meta_title', 'text', [
            'name'  => 'meta_title',
            'label' => __('Meta Title'),
            'title' => __('Meta Title')
        ]);
        $fieldset->addField('meta_keywords', 'text', [
            'name'  => 'meta_keywords',
            'label' => __('Meta Keywords'),
            'title' => __('Meta Keywords')
        ]);
        $fieldset->addField('meta_description', 'textarea', [
            'name'  => 'meta_description',
            'label' => __('Meta Description'),
            'title' => __('Meta Description')
        ]);
        $fieldset->addField('meta_robots', 'select', [
            'name'   => 'meta_robots',
            'label'  => __('Meta Robots'),
            'title'  => __('Meta Robots'),
            'values' => $this->metaRobotsOptions->toOptionArray()
        ]);
        if (!$model->getId()) {
            $model->addData(['status' => 1, 'store_ids' => '0']);
        }

        $savedData = $model->getData();
        $form->setValues($savedData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Category');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Category');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
