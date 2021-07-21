<?php


namespace Ebizmarts\SagePaySuite\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Ebizmarts\SagePaySuite\Model\Session as SagePaySession;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Framework\UrlInterface;

class RecoverCart implements ObserverInterface
{
    /** @var Session */
    private $session;

    /** @var Logger */
    private $suiteLogger;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var UrlInterface */
    private $urlInterface;

    /** @var Http  */
    private $request;

    /**
     * RecoverCart constructor.
     * @param Session $session
     * @param Logger $suiteLogger
     * @param ManagerInterface $messageManager
     * @param UrlInterface $urlInterface
     * @param Http $request
     */
    public function __construct(
        Session $session,
        Logger $suiteLogger,
        ManagerInterface $messageManager,
        UrlInterface $urlInterface,
        Http $request
    )
    {
        $this->session = $session;
        $this->suiteLogger = $suiteLogger;
        $this->messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->request = $request;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->filterActions()) {
            $presavedOrderId = $this->session->getData(SagePaySession::PRESAVED_PENDING_ORDER_KEY);
            $convertingQuoteToOrder = $this->session->getData(SagePaySession::CONVERTING_QUOTE_TO_ORDER);
            if ($this->checkIfRecoverCartIsPossible($presavedOrderId, $convertingQuoteToOrder)) {
                $url = $this->urlInterface->getBaseUrl() . "sagepaysuite/cart/recover";
                $message = "<a target='_self' href=$url>HERE</a>";
                $message = __("There is an order in process. Click " . $message . " to recover the cart.");
                $this->messageManager->addNotice($message);
                $this->session->setData(SagePaySession::CONVERTING_QUOTE_TO_ORDER, 0);
            }
        }
    }

    /**
     * @param $presavedOrderId
     * @param $quoteIsActive
     * @return bool
     */
    private function checkIfRecoverCartIsPossible($presavedOrderId, $quoteIsActive)
    {
        return $this->checkPreSavedOrder($presavedOrderId) && $this->checkQuoteIsNotActive($quoteIsActive);
    }

    /**
     * @param $presavedOrderId
     * @return bool
     */
    private function checkPreSavedOrder($presavedOrderId)
    {
        return !empty($presavedOrderId);
    }

    /**
     * @param $quoteIsActive
     * @return bool
     */
    private function checkQuoteIsNotActive($quoteIsActive)
    {
        return $quoteIsActive === 1;
    }

    /**
     * @return bool
     */
    private function filterActions()
    {
          return $this->request->getFrontName() !== 'rest' &&
              $this->request->getFrontName() !== 'sagepaysuite' &&
              $this->request->getFullActionName() !== 'customer_section_load';
    }
}
