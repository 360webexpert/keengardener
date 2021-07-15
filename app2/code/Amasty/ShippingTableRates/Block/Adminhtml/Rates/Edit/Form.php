<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Block\Adminhtml\Rates\Edit;

use Amasty\ShippingTableRates\Api\Data\ShippingTableRateInterface;

/**
 * Shipping Rate of Method Form initialization
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\ShippingTableRates\Helper\Data $helper,
        array $data
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('amstrates_rate_form');
        $this->setTitle(__('Rate Information'));
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('amtable_rate');

        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('amstrates/rates/save'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $fieldsetDestination = $form->addFieldset('destination', ['legend' => __('Destination')]);

        if ($model->getId()) {
            $fieldsetDestination->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldsetDestination->addField(
            ShippingTableRateInterface::METHOD_ID,
            'hidden',
            [
                'name' => ShippingTableRateInterface::METHOD_ID
            ]
        );

        $fieldsetDestination->addField(
            ShippingTableRateInterface::COUNTRY,
            'select',
            [
                'label' => __('Country'),
                'name' => ShippingTableRateInterface::COUNTRY,
                'options' => $this->_helper->getCountriesHash()
            ]
        );

        $fieldsetDestination->addField(
            ShippingTableRateInterface::STATE,
            'select',
            [
                'label' => __('State'),
                'name' => ShippingTableRateInterface::STATE,
                'options' => $this->_helper->getStatesHash()
            ]
        );

        $fieldsetDestination->addField(
            ShippingTableRateInterface::CITY,
            'text',
            [
                'label' => __('City'),
                'name' => ShippingTableRateInterface::CITY
            ]
        );

        $fieldsetDestination->addField(
            ShippingTableRateInterface::ZIP_FROM,
            'text',
            [
                'label' => __('Zip From'),
                'name' => ShippingTableRateInterface::ZIP_FROM
            ]
        );

        $fieldsetDestination->addField(
            ShippingTableRateInterface::ZIP_TO,
            'text',
            [
                'label' => __('Zip To'),
                'name' => ShippingTableRateInterface::ZIP_TO
            ]
        );

        $fieldsetConditions = $form->addFieldset('conditions', ['legend' => __('Conditions')]);

        $fieldsetConditions->addField(
            ShippingTableRateInterface::WEIGHT_FROM,
            'text',
            [
                'label' => __('Weight From'),
                'name' => ShippingTableRateInterface::WEIGHT_FROM,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::WEIGHT_TO,
            'text',
            [
                'label' => __('Weight To'),
                'name' => ShippingTableRateInterface::WEIGHT_TO,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::QTY_FROM,
            'text',
            [
                'label' => __('Qty From'),
                'name' => ShippingTableRateInterface::QTY_FROM,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::QTY_TO,
            'text',
            [
                'label' => __('Qty To'),
                'name' => ShippingTableRateInterface::QTY_TO,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::SHIPPING_TYPE,
            'select',
            [
                'label' => __('Shipping Type'),
                'name' => ShippingTableRateInterface::SHIPPING_TYPE,
                'options' => $this->_helper->getTypesHash(),
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::PRICE_FROM,
            'text',
            [
                'label' => __('Price From'),
                'name' => ShippingTableRateInterface::PRICE_FROM,
                'note' => __('Original product cart price, without discounts.'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::PRICE_TO,
            'text',
            [
                'label' => __('Price To'),
                'name' => ShippingTableRateInterface::PRICE_TO,
                'note' => __('Original product cart price, without discounts.'),
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::TIME_DELIVERY,
            'text',
            [
                'label' => __('Estimated Delivery (days)'),
                'name' => ShippingTableRateInterface::TIME_DELIVERY,
                'note' => __('This value will be used for the {day} variable in the Method name')
            ]
        );

        $fieldsetConditions->addField(
            ShippingTableRateInterface::NAME_DELIVERY,
            'text',
            [
                'label' => __('Name delivery'),
                'name' => ShippingTableRateInterface::NAME_DELIVERY,
                'note' => __('This value will be used for the {name} variable in the Method name')
            ]
        );

        $fieldsetRate = $form->addFieldset('rate', ['legend' => __('Rate')]);

        $fieldsetRate->addField(
            ShippingTableRateInterface::COST_BASE,
            'text',
            [
                'label' => __('Base Rate for the Order'),
                'name' => ShippingTableRateInterface::COST_BASE,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRate->addField(
            ShippingTableRateInterface::COST_PERCENT,
            'text',
            [
                'label' => __('Percentage per Product'),
                'name' => ShippingTableRateInterface::COST_PERCENT,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRate->addField(
            ShippingTableRateInterface::COST_PRODUCT,
            'text',
            [
                'label' => __('Fixed Rate per Product'),
                'name' => ShippingTableRateInterface::COST_PRODUCT,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRate->addField(
            ShippingTableRateInterface::COST_WEIGHT,
            'text',
            [
                'label' => __('Fixed Rate per 1 unit of weight'),
                'name' => ShippingTableRateInterface::COST_WEIGHT,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $fieldsetRate->addField(
            ShippingTableRateInterface::START_WEIGHT,
            'text',
            [
                'label' => __('Count weight from'),
                'name' => ShippingTableRateInterface::START_WEIGHT,
                'class' => 'validate-number validate-zero-or-greater'
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
