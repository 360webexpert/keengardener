<?php

namespace WeSupply\Toolbox\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Helper\Data as WeSupply;
use WeSupply\Toolbox\Model\OrderInfoBuilder;

class FetchSingleOrder extends Action
{
    const REQUIRED_PARAMS = [
        'guid',
        'ClientName',
        'OrderId'
    ];

    const AUTH_KEYS = [
        'guid',
        'ClientName'
    ];

    /**
     * @var array
     */
    protected $invalidParams = [];

    /**
     * @var string
     */
    protected $guid;

    /**
     * @var string
     */
    protected $clientName;

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var bool
     */
    protected $orderIdIsInternal = FALSE;

    /**
     * @var WeSupply
     */
    protected $helper;


    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * FetchSingleOrder constructor.
     * @param Context $context
     * @param WeSupply $helper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        WeSupply $helper,
        OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        if (!$this->_validateParams()) {
            $this->getResponse()
                ->setStatusCode(Http::STATUS_CODE_500)
                ->setContent(__('API params should have at least: %1', implode(', ', $this->invalidParams)));

            return;
        }

        if (!$this->_clientAuthorization()) {
            $this->getResponse()
                ->setStatusCode(Http::STATUS_CODE_401)
                ->setContent(__('Unauthorized! Invalid value provided for %1 ', implode(', ', $this->invalidParams)));

            return;
        }

        /** Get the requested order data */
        $response = $this->addResponseStatus('false', 'SUCCESS', '');

        $orderData = $this->fetchOrder();
        if (!$orderData) {
            $response = $this->addResponseStatus('true', 'ERROR', __('Order with ID %1 was not found!', $this->orderId));
        }

        $response .= $orderData ?? '<Order>Order not found!</Order>';
        $response = '<Orders>' . $response . '</Orders>';

        $xml = simplexml_load_string($response);  // Might be ignored this and just send the $response as result

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        $this->getResponse()->setBody($xml->asXML());
    }

    /**
     * @return string|null
     */
    protected function fetchOrder()
    {
        $orderId = $this->cleanOrderId();
        switch ($this->orderIdIsInternal) {
            case TRUE;
                $order = $this->orderRepository->getByOrderId($orderId, TRUE);
                break;
            default:
                $order = $this->orderRepository->getByOrderNumber($orderId, TRUE);
                break;
        }

        return $order->getInfo();
    }

    /**
     * @param string $hasError
     * @param string $errorCode
     * @param string $errorDescription
     * @return string
     */
    protected function addResponseStatus($hasError, $errorCode, $errorDescription)
    {
        return "<Response>" .
               "<ResponseHasErrors>$hasError</ResponseHasErrors>" .
               "<ResponseCode>$errorCode</ResponseCode>" .
               "<ResponseDescription>$errorDescription</ResponseDescription>" .
               "</Response>";
    }

    /**
     * @return bool
     */
    private function _validateParams()
    {
        $params = $this->getRequest()->getParams();
        foreach (self::REQUIRED_PARAMS as $key) {
            $this->{lcfirst($key)} = !empty($params[$key]) ? $params[$key] : FALSE;
            if (FALSE === $this->{lcfirst($key)}) {
                $this->invalidParams[] = $key;
            }
        }

        if (!empty($this->invalidParams)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return bool
     */
    private function _clientAuthorization()
    {
        foreach (self::AUTH_KEYS as $key) {
            $method = 'get' . ucfirst($key);
            if ($this->helper->$method() != $this->{lcfirst($key)}) {
                $this->invalidParams[] = $key;
            }
        }

        if (!empty($this->invalidParams)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * @return string
     */
    private function cleanOrderId()
    {
        if (preg_match('/^' . OrderInfoBuilder::PREFIX . '/i', $this->orderId)) {
            $this->orderIdIsInternal = TRUE;
            return preg_replace('/^' . OrderInfoBuilder::PREFIX . '/i', '', $this->orderId);
        }

        return $this->orderId;
    }
}
