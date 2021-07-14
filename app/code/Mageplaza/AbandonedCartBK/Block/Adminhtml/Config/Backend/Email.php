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
 * @package     Mageplaza_AbandonedCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AbandonedCart\Block\Adminhtml\Config\Backend;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Config\Model\Config\Source\Email\Identity;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Email\Model\ResourceModel\Template\Collection;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Data\Form\Element\Factory;

/**
 * Class Email
 * @package Mageplaza\AbandonedCart\Block\Adminhtml\Config\Backend
 */
class Email extends AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_AbandonedCart::system/config/form/field/email.phtml';

    /**
     * @var Factory
     */
    protected $elementFactory;

    /**
     * @var Identity
     */
    protected $emailIdentity;

    /**
     * @var CollectionFactory
     */
    protected $templatesFactory;

    /**
     * @var Config
     */
    protected $emailConfig;

    /**
     * @var Yesno
     */
    protected $yesnoSource;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param CollectionFactory $templatesFactory
     * @param Identity $emailIdentity
     * @param Config $emailConfig
     * @param Yesno $yesno
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        CollectionFactory $templatesFactory,
        Identity $emailIdentity,
        Config $emailConfig,
        Yesno $yesno,
        array $data = []
    ) {
        $this->elementFactory   = $elementFactory;
        $this->emailIdentity    = $emailIdentity;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig      = $emailConfig;
        $this->yesnoSource      = $yesno;

        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    public function _construct()
    {
        $this->addColumn('send', ['label' => __('Send after'), 'style' => 'width:100px']);
        $this->addColumn('sender', ['label' => __('Sender')]);
        $this->addColumn('template', ['label' => __('Email template')]);
        $this->addColumn('coupon', ['label' => __('Has coupon')]);

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('More');

        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return mixed|string
     * @throws Exception
     */
    public function renderCellTemplate($columnName)
    {
        if (!empty($this->_columns[$columnName])) {
            switch ($columnName) {
                case 'sender':
                    $options = $this->emailIdentity->toOptionArray();
                    break;
                case 'template':
                    $options = $this->getEmailTemplateOption();
                    break;
                case 'coupon':
                    $options = $this->yesnoSource->toOptionArray();
                    break;
                default:
                    $options = '';
                    break;
            }
            if ($options) {
                $element = $this->elementFactory->create('select');
                $element->setForm($this->getForm())
                    ->setName($this->_getCellInputElementName($columnName))
                    ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
                    ->setValues($options);

                return str_replace("\n", '', $element->getElementHtml());
            }
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * Generate list of email templates
     *
     * @return array
     */
    private function getEmailTemplateOption()
    {
        /** @var Collection $collection */
        $collection   = $this->templatesFactory->create()->load();
        $emailOptions = $collection->toOptionArray();

        $templateIds = [
            'mageplaza_abandoned_cart_template_1',
            'mageplaza_abandoned_cart_template_2',
            'mageplaza_abandoned_cart_template_3',
            'mageplaza_abandoned_cart_template_4',
            'mageplaza_abandoned_cart_template_5'
        ];
        foreach ($templateIds as $templateId) {
            $templateLabel = $this->emailConfig->getTemplateLabel($templateId);
            $templateLabel = __('%1 (Default)', $templateLabel);
            array_unshift($emailOptions, ['value' => $templateId, 'label' => $templateLabel]);
        }

        return $emailOptions;
    }

    /**
     * @return string
     */
    public function getAddButtonLabel()
    {
        return __('Add');
    }
}
