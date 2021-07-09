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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Mageplaza\SeoRule\Model\Rule\Source\Type;
use Mageplaza\SeoRule\Model\RuleFactory;

/**
 * Class Product
 * @package Mageplaza\SeoRule\Block\Adminhtml\Rule\Edit\Tab\Conditions
 */
class Product extends Generic implements TabInterface
{
    /**
     * @var RuleFactory
     */
    protected $autoRelatedRuleFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var Conditions
     */
    protected $conditions;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param RuleFactory $autoRelatedRuleFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Conditions $conditions,
        Fieldset $rendererFieldset,
        RuleFactory $autoRelatedRuleFactory,
        array $data = []
    ) {
        $this->rendererFieldset       = $rendererFieldset;
        $this->conditions             = $conditions;
        $this->autoRelatedRuleFactory = $autoRelatedRuleFactory;
        $this->registry               = $registry;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->registry->registry('mageplaza_seorule_rule');
        /** @var Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return Form
     * @throws LocalizedException
     */
    protected function addTabToForm(
        $model,
        $fieldsetId = 'conditions_fieldset',
        $formName = 'mageplaza_seorule_rule_form'
    ) {
        $id = $this->getRequest()->getParam('id');
        if (!$model) {
            $model = $this->autoRelatedRuleFactory->create();
            $model->load($id);
        }

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $newChildUrl = $this->getUrl(
            'seo/condition/newConditionHtml/form/' . $model->getConditionsFieldSetId($formName),
            ['form_namespace' => $formName]
        );

        $renderer = $this->rendererFieldset->setTemplate('Mageplaza_SeoRule::rule/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($model->getConditionsFieldSetId($formName));
        if ($model->getBlockType() == 'product') {
            $renderer->setAjaxUrl($this->getUrl(
                'seo/grid/productlist',
                ['id' => $id, 'type' => 'cond', 'form_key' => $this->formKey->getFormKey()]
            ));
        }
        $seoRuleType = $this->registry->registry('seorule_type');
        $legend      = 'Conditions (don\'t add conditions if rule is applied to all products and attribute set)';
        if ($seoRuleType == Type::LAYERED_NAVIGATION) {
            $legend = 'Conditions (don\'t add conditions if rule is applied to all attribute)';
        }
        if ($seoRuleType) {
            $fieldset = $form->addFieldset(
                $fieldsetId,
                ['legend' => __($legend)]
            )->setRenderer($renderer);
        }

        $fieldset->addField('conditions', 'text', [
            'name'           => 'conditions',
            'label'          => __('Conditions'),
            'title'          => __('Conditions'),
            'required'       => true,
            'data-form-part' => $formName
        ])
            ->setRule($model)
            ->setRenderer($this->conditions);
        $form->setValues($model->getData());
        $model->getConditions()->setJsFormObject($model->getConditionsFieldSetId($formName));
        $this->setConditionFormName($model->getConditions(), $formName);

        return $form;
    }
}
