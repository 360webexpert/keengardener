<?php


namespace Ebizmarts\SagePaySuite\Observer;

use Ebizmarts\SagePaySuite\Helper\Data;
use Ebizmarts\SagePaySuite\Model\Api\Reporting;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CreditMemo implements ObserverInterface
{
    /** @var Data */
    private $suiteHelper;

    /** @var Reporting */
    private $reportingApi;

    /** @var ManagerInterface */
    private $messageManager;

    public function __construct(
        ManagerInterface $messageManager,
        Data $suiteHelper,
        Reporting $reportingApi
    ) {
        $this->suiteHelper    = $suiteHelper;
        $this->reportingApi   = $reportingApi;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order             = $observer->getData('creditmemo')->getOrder();
        $payment           = $order->getPayment();
        $paymentMethodCode = $payment->getMethod();

        if (!$this->suiteHelper->methodCodeIsSagePay($paymentMethodCode)) {
            return;
        }

        $vpsTxIdRaw = $order->getPayment()->getLastTransId();
        $vpsTxId    = $this->suiteHelper->clearTransactionId($vpsTxIdRaw);

        try {
            $this->reportingApi->getTransactionDetailsByVpstxid($vpsTxId, $order->getStoreId());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($this->reportingApiErrorMessage($e));
        }
    }

    /**
     * @param $e
     * @return \Magento\Framework\Phrase
     */
    private function reportingApiErrorMessage($e)
    {
        $message = "This Opayo transaction cannot be refunded online because the Reporting API communication";
        $message .= " could not be established. The response is: %1";
        return __($message, $e->getMessage());
    }
}
