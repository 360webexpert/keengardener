<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay;

use Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractContainer;
use Ess\M2ePro\Model\Ebay\Template\Manager;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Ebay\Template
 */
class Template extends AbstractContainer
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('ebayTemplate');
        $this->_controller = 'adminhtml_ebay_template';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    //########################################

    protected function _prepareLayout()
    {
        $content = $this->__(
            '<p>This Page displays the list of the Policies you are currently using in your M2E Pro Listings.
            Policy is a combination of settings that can be used in different M2E Pro Listings.</p><br>
            <p>You can <strong>Delete</strong> a Policy only if it\'s not being used for an M2E Pro Listing.</p>'
        );

        $this->appendHelpBlock(
            [
                'content' => $content
            ]
        );

        $addButtonProps = [
            'id'           => 'add_policy',
            'label'        => __('Add Policy'),
            'class'        => 'add',
            'button_class' => '',
            'class_name'   => 'Ess\M2ePro\Block\Adminhtml\Magento\Button\DropDown',
            'options'      => $this->_getAddTemplateButtonOptions(),
        ];
        $this->addButton('add', $addButtonProps);

        return parent::_prepareLayout();
    }

    //########################################

    protected function _getAddTemplateButtonOptions()
    {
        $data = [
            Manager::TEMPLATE_PAYMENT         => [
                'label'   => $this->__('Payment'),
                'id'      => 'payment',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_PAYMENT) . "')",
                'default' => true
            ],
            Manager::TEMPLATE_SHIPPING        => [
                'label'   => $this->__('Shipping'),
                'id'      => 'shipping',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SHIPPING) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_RETURN_POLICY   => [
                'label'   => $this->__('Return'),
                'id'      => 'return',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_RETURN_POLICY) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_SELLING_FORMAT  => [
                'label'   => $this->__('Selling'),
                'id'      => 'selling',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SELLING_FORMAT) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_DESCRIPTION     => [
                'label'   => $this->__('Description'),
                'id'      => 'description',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_DESCRIPTION) . "')",
                'default' => false,
            ],
            Manager::TEMPLATE_SYNCHRONIZATION => [
                'label'   => $this->__('Synchronization'),
                'id'      => 'synchronization',
                'onclick' => "setLocation('" . $this->getTemplateUrl(Manager::TEMPLATE_SYNCHRONIZATION) . "')",
                'default' => false,
            ]
        ];

        return $data;
    }

    protected function getTemplateUrl($nick)
    {
        return $this->getUrl('*/ebay_template/new', ['nick' => $nick]);
    }

    //########################################
}
