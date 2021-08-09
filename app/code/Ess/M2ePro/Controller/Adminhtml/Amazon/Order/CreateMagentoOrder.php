<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Order;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Order;

/**
 * Class \Ess\M2ePro\Controller\Adminhtml\Amazon\Order\CreateMagentoOrder
 */
class CreateMagentoOrder extends Order
{
    public function execute()
    {
        $ids      = $this->getRequestIds();
        $isForce  = (bool)$this->getRequest()->getParam('force');
        $warnings = 0;
        $errors   = 0;

        foreach ($ids as $id) {
            /** @var $order \Ess\M2ePro\Model\Order */
            $order = $this->amazonFactory->getObjectLoaded('Order', (int)$id);
            $order->getLog()->setInitiator(\Ess\M2ePro\Helper\Data::INITIATOR_USER);

            if ($order->getMagentoOrderId() !== null && !$isForce) {
                $warnings++;
                continue;
            }

            // Create magento order
            // ---------------------------------------
            try {
                $order->createMagentoOrder($isForce);
            } catch (\Exception $e) {
                $errors++;
            }

            // ---------------------------------------

            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice();
            }

            if ($order->getChildObject()->canCreateShipments()) {
                $order->createShipments();
            }

            // ---------------------------------------
            $order->updateMagentoOrderStatus();
            // ---------------------------------------
        }

        if (!$errors && !$warnings) {
            $this->messageManager->addSuccess($this->__('Magento Order(s) were created.'));
        }

        if ($errors) {
            $this->messageManager->addError(
                $this->__(
                    '%count% Magento order(s) were not created. Please <a target="_blank" href="%url%">view Log</a>
                for the details.',
                    $errors, $this->getUrl('*/amazon_log_order')
                )
            );
        }

        if ($warnings) {
            $this->messageManager->addWarning(
                $this->__(
                    '%count% Magento order(s) are already created for the selected amazon order(s).', $warnings
                )
            );
        }

        if (count($ids) == 1) {
            return $this->_redirect('*/*/view', ['id' => $ids[0]]);
        } else {
            return $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}
