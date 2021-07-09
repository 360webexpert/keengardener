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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\ProductsPage\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class DefaultAttribute
 * @package Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\ProductsPage\Edit\Tab
 */
class DefaultAttribute extends Generic implements TabInterface
{
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        //todo create default attributes table
        /** @var \Mageplaza\LayeredNavigationUltimate\Model\ProductsPage $post */
        $model = $this->_coreRegistry->registry('current_page');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Default Attributes'),
            'class'  => 'fieldset-wide'
        ]);
        $fieldset->addField('default_attributes', 'text', [
            'class' => 'no-display'
        ]);

        $field = $fieldset->addField('render_default_attributes', 'text', [
            'label' => 'Default Attributes',
            'name'  => 'render_default_attributes'
        ]);

        /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $renderer */
        $renderer = $this->getLayout()->createBlock('Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\Form\Renderer\RenderDefaultAttributes');
        $field->setRenderer($renderer);

        $form->addValues($model->getData());
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
        return __('Default Attributes');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Default Attributes');
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
