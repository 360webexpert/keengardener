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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Attribute\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Shopbybrand\Model\Config\Source\StaticBlock;

/**
 * Class Brand
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Attribute\Edit
 * @method getOptionData
 */
class Brand extends Generic
{
    /**
     * @var StaticBlock
     */
    protected $staticBlock;

    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * Brand constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param StaticBlock $staticBlock
     * @param Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        StaticBlock $staticBlock,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->staticBlock = $staticBlock;
        $this->_wysiwygConfig = $wysiwygConfig;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $data = $this->getOptionData();
        $form = $this->_formFactory->create([
            'data' => [
                'id'            => 'brand_attribute_save',
                'action'        => $this->getUrl('mpbrand/attribute/save', ['id' => $data['brand_id']]),
                'method'        => 'post',
                'use_container' => true,
                'enctype'       => 'multipart/form-data'
            ]
        ]);

        $mainfieldset = $form->addFieldset('brand_fieldset', [
            'legend' => __('Brand Information'),
            'class'  => 'fieldset-wide'
        ]);
        $mainfieldset->addField('option_id', 'hidden', [
            'name' => 'option_id'
        ]);
        $mainfieldset->addField('store_id', 'hidden', [
            'name' => 'store_id'
        ]);
        $mainfieldset->addField('page_title', 'text', [
            'name'  => 'page_title',
            'label' => __('Page Title'),
            'title' => __('Page Title'),
            'note'  => __('If empty, option label by store will be used.')
        ]);
        $mainfieldset->addField('url_key', 'text', [
            'name'     => 'url_key',
            'label'    => __('Url Key'),
            'title'    => __('Url Key'),
            'required' => true,
        ]);
        $mainfieldset->addField('image', 'image', [
            'name'  => 'image',
            'label' => __('Brand Image'),
            'title' => __('Brand Image'),
            'note'  => __('If empty, option visual image or default image from configuration will be used.')
        ]);
        $mainfieldset->addField('is_featured', 'select', [
            'name'   => 'is_featured',
            'label'  => __('Featured'),
            'title'  => __('Featured'),
            'values' => ['1' => __('Enabled'), '0' => __('Disabled')],
            'note'   => __('If \'Enabled\', this brand will be displayed on featured brand slider.')
        ]);
        $mainfieldset->addField('short_description', 'editor', [
            'name'   => 'short_description',
            'label'  => __('Short Description'),
            'title'  => __('Short Description'),
            'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
        ]);
        $mainfieldset->addField('description', 'editor', [
            'name'   => 'description',
            'label'  => __('Description'),
            'title'  => __('Description'),
            'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
        ]);
        $mainfieldset->addField('static_block', 'select', [
            'name'   => 'static_block',
            'label'  => __('CMS Block'),
            'title'  => __('CMS Block'),
            'values' => $this->staticBlock->getOptionArray(),
        ]);

        $metafieldset = $form->addFieldset('brand_meta_fieldset', [
            'legend' => __('Meta Information'),
            'class'  => 'fieldset-wide'
        ]);
        $metafieldset->addField('meta_title', 'text', [
            'name'  => 'meta_title',
            'label' => __('Meta Title'),
            'title' => __('Meta Title'),
            'note'  => __('If empty, option label by store will be used.')
        ]);
        $metafieldset->addField('meta_keywords', 'textarea', [
            'name'  => 'meta_keywords',
            'label' => __('Meta Keywords'),
            'title' => __('Meta Keywords'),
        ]);
        $metafieldset->addField('meta_description', 'editor', [
            'name'  => 'meta_description',
            'label' => __('Meta Description'),
            'title' => __('Meta Description'),
        ]);

        $form->addValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
