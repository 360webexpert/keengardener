<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay;

use Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractContainer;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Ebay\Order
 */
class Order extends AbstractContainer
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setId('ebayOrder');
        $this->_controller = 'adminhtml_ebay_order';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton(
            'upload_by_user',
            [
                'label'     => $this->__('Order Reimport'),
                'onclick'   => 'UploadByUserObj.openPopup()',
                'class'     => 'action-primary'
            ]
        );
    }

    //########################################

    protected function _prepareLayout()
    {
        $this->appendHelpBlock([
            'content' => $this->__(
                <<<HTML
                <p>In this section, you can find the list of the Orders imported from eBay. </p>
                <p>An eBay Order, for which Magento Order is created, contains a value in
                <strong>Magento Order #</strong> column of the grid. You can find the corresponding
                Magento Order in Sales > Orders section of your Magento</p><br>

                <p>To manage the imported eBay Orders, you can use Mass Action options available in the
                Actions bulk: Reserve QTY, Cancel QTY Reserve, Mark Order(s) as Shipped or Paid and Resend
                Shipping Information.</p><br>

                <p>Also, you can view the detailed Order information by clicking on the appropriate
                row of the grid.</p><br>

                <p><strong>Note:</strong> Automatic creation of Magento Orders, Invoices, and Shipments is
                performed in accordance with the Order settings specified in <br>
                <strong>Account Settings (eBay Integration > Configuration > Accounts)</strong>. </p>
HTML
            ),
        ]);

        $this->setPageActionsBlock('Ebay_Order_PageActions');

        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->createBlock('Order_Item_Edit')->toHtml() .
               parent::getGridHtml();
    }

    protected function _beforeToHtml()
    {
        $this->jsPhp->addConstants(
            $this->getHelper('Data')->getClassConstants(\Ess\M2ePro\Controller\Adminhtml\Order\EditItem::class)
        );

        $this->js->addRequireJs(['upload' => 'M2ePro/Order/UploadByUser'], <<<JS
UploadByUserObj = new UploadByUser('ebay', 'orderUploadByUserPopupGrid');
JS
        );

        $this->jsUrl->addUrls(
            $this->getHelper('Data')->getControllerActions('Order_UploadByUser')
        );

        $this->jsTranslator->addTranslations(
            [
                'Order Reimport'               => $this->__('Order Reimport'),
                'Order importing in progress.' => $this->__('Order importing in progress.'),
                'Order importing is canceled.' => $this->__('Order importing is canceled.')
            ]
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
