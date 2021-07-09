<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Controller\Webhook;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Serialize\Serializer\Json;
use \Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Api\CreditmemoManagementInterface;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Api\WeSupplyApiInterface;
use WeSupply\Toolbox\Api\Data\ReturnslistInterface;
use WeSupply\Toolbox\Api\GiftcardInterface;
use WeSupply\Toolbox\Helper\Data as Helper;
use WeSupply\Toolbox\Logger\Logger as Logger;
use WeSupply\Toolbox\Model\Webhook;

/**
 * Class Returns
 * @package WeSupply\Toolbox\Controller\Webhook
 */

class Returns extends Action
{
    /**#@+
     * Constants
     */
    const WESUPPLY_API_ENDPOINT = 'returns/grabById';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    protected $priceHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var CreditmemoLoader
     */
    protected $creditMemoLoader;

    /**
     * @var CreditmemoManagementInterface
     */
    protected $creditMemoManagement;

    /**
     * @var CreditmemoSender
     */
    protected $creditMemoSender;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var WeSupplyApiInterface
     */
    protected $weSupplyApiInterface;

    /**
     * @var ReturnslistInterface
     */
    protected $returnsList;

    /**
     * @var GiftcardInterface
     */
    protected $giftCardInterface;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Webhook
     */
    protected $webhook;

    /**
     * @var array
     */
    protected $params;

    /**
     * An unique id for success refund
     * @var
     */
    protected $requestLogId;

    /**
     * @var array
     */
    protected $finalSuccessMessage = '';

    /**
     * @var array
     */
    protected $returnDetails = [];

    /**
     * @var array
     */
    protected $creditMemoData = [];

    /**
     * Returns constructor.
     * @param Context $context
     * @param ProductMetadataInterface $productMetadata
     * @param ResourceConnection $resourceConnection
     * @param JsonFactory $jsonFactory
     * @param Json $json
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Invoice $invoice
     * @param CreditmemoLoader $creditMemoLoader
     * @param CreditmemoManagementInterface $creditMemoManagement
     * @param CreditmemoSender $creditMemoSender
     * @param StoreManagerInterface $storeManager
     * @param DateTimeFactory $dateTimeFactory
     * @param ReturnslistInterface $returnsList
     * @param GiftcardInterface $giftCardInterface
     * @param WeSupplyApiInterface $weSupplyApiInterface
     * @param PriceHelper $priceHelper
     * @param Helper $helper
     * @param Logger $logger
     * @param Webhook $webhook
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        ResourceConnection $resourceConnection,
        JsonFactory $jsonFactory,
        Json $json,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Invoice $invoice,
        CreditmemoLoader $creditMemoLoader,
        CreditmemoManagementInterface $creditMemoManagement,
        CreditmemoSender $creditMemoSender,
        StoreManagerInterface $storeManager,
        DateTimeFactory $dateTimeFactory,
        GiftcardInterface $giftCardInterface,
        ReturnslistInterface $returnsList,
        WeSupplyApiInterface $weSupplyApiInterface,
        PriceHelper $priceHelper,
        Helper $helper,
        Logger $logger,
        Webhook $webhook
    ) {
        $this->productMetadata = $productMetadata;
        $this->resultJsonFactory = $jsonFactory;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->invoice = $invoice;
        $this->creditMemoLoader = $creditMemoLoader;
        $this->creditMemoManagement = $creditMemoManagement;
        $this->creditMemoSender = $creditMemoSender;
        $this->storeManager = $storeManager;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->giftCardInterface = $giftCardInterface;
        $this->returnsList = $returnsList;
        $this->weSupplyApiInterface = $weSupplyApiInterface;
        $this->priceHelper = $priceHelper;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->webhook = $webhook;

        $this->resource = $resourceConnection;
        $this->connection = $resourceConnection->getConnection();

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultJson|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        if (!$this->webhook->canProceedsRequest()) {
            $error = $this->webhook->getError();
            $this->logger->addError($error['status-message']);

            return $resultJson->setData($error);
        }

        $this->params = $this->getRequest()->getParams();
        if (!$this->webhook->validateParams('return', $this->params)) {
            $error = $this->webhook->getError();
            $this->logger->addError($error['status-message']);

            return $resultJson->setData($error);
        }

        $this->returnDetails = $this->webhook->proceed(
            self::WESUPPLY_API_ENDPOINT,
            'GET',
            [
                'provider' => 'Magento',
                'reference' => $this->params['reference']
            ]
        );

        if ($this->webhook->getError() || !$this->returnDetails) {
            return $resultJson->setData($this->webhook->getError());
        }

        $this->matchRefundTypeAmountPairs();

        /**
         * First, we need to check what type of refund was requested in order to proceed
         * Gift Card and Store Credit is only available for Magento Commerce (Enterprise)
         */
        if (
            !$this->isEnterprise() &&
            ($this->checkRefundMethodExists('gift_card') || $this->checkRefundMethodExists('credit'))
        ) {
            $hasGiftCard = false;
            $message = 'refund is only available for Magento Commerce.';
            if ($this->checkRefundMethodExists('gift_card')) {
                $hasGiftCard = true;
                $message = __('Gift Card %1', $message);
            }
            if ($this->checkRefundMethodExists('credit')) {
                $message = !$hasGiftCard ? __('Store Credit %1', $message) : __('Store Credit and %1', $message);
            }
            return $resultJson->setData(['success' => false, 'status-title' => 'Refund Failed', 'status-message' => $message]);
        }

        // create credit memo
        $this->requestLogId = uniqid();
        $this->prepareCreditMemoParams();
        $creditMemo = $this->createCreditMemo();

        // save processed refund
        if ($creditMemo['success'] === TRUE) {
            $this->saveProcessedReturn($this->params['reference']);
        }

        return $resultJson->setData(
            ['success' => $creditMemo['success'], 'status-title' => $creditMemo['status-title'], 'status-message' => $creditMemo['status-message']]
        );
    }

    /**
     * Set credit memo data
     */
    private function prepareCreditMemoParams()
    {
        $this->creditMemoData['increment_id'] = $this->helper->recursivelyGetArrayData(['ExternOrderNo'], $this->returnDetails, null);
        $this->creditMemoData['do_offline'] = $this->getOfflineFlag();
        $this->creditMemoData['shipping_amount'] = $this->refundShipping() ? $this->calculateShipping() : 0;
        $this->creditMemoData['adjustment_positive'] = 0; // not set !!!
        $this->creditMemoData['adjustment_negative'] = $this->helper->recursivelyGetArrayData(['logistics', 'cost'], $this->returnDetails, 0);
        $this->creditMemoData['items'] = $this->getReturnItems();
        $this->creditMemoData['comment_text'] .= $this->helper->recursivelyGetArrayData(['return_comment'], $this->returnDetails, ''); // not set !!!
        $this->creditMemoData['comment_text'] .= $this->collectReturnAdminComments();
        $this->creditMemoData['comment_text'] .= $this->addAdditionalAdminComments();
        $this->creditMemoData['send_email'] = false; // not set !!!
        $this->creditMemoData['store_credit_amount'] = $this->checkRefundMethodExists('credit') ? $this->getStoreCreditAmount() : 0;
        $this->creditMemoData['gift_card_amount'] = $this->checkRefundMethodExists('gift_card') ? $this->getGiftCardAmount() : 0;

        $this->creditMemoData['adjustment_negative'] += $this->creditMemoData['gift_card_amount'];
    }

    /**
     * @return array
     */
    private function createCreditMemo()
    {
        /**
            Expected params:

            $creditMemoData['do_offline'] = 1;
            $creditMemoData['shipping_amount'] = 0;
            $creditMemoData['adjustment_positive'] = 0;
            $creditMemoData['adjustment_negative'] = 0;
            $creditMemoData['comment_text'] = 'comment_text_for_creditmemo';
            $creditMemoData['send_email'] = 1;
            $creditMemoData['refund_customerbalance_return_enable'] = 0; // only for Magento commerce
            $orderItemId = 10; // pass order item id
            $itemToCredit[$orderItemId] = ['qty'=>1];
            $creditMemoData['items'] = $itemToCredit;
         */

        $order = $this->getOrder();
        if (!$order) {
            return [
                'success' => false,
                'status-title' => 'Refund Failed',
                'status-message' => __('Order with ID %1 was not found.', $this->creditMemoData['increment_id'])
            ];
        }

        if ($order->getPayment()->getMethodInstance()->isOffline() && !$this->creditMemoData['do_offline']) {
            $paymentInfo = $order->getPayment()->getAdditionalInformation();
            return [
                'success' => false,
                'status-title' => 'Refund Failed',
                'status-message' => __('Only offline type refunds are allowed for this order. Payment type used was %1.', $paymentInfo['method_title'])
            ];
        }

        // clear unnecessary data before load credit memo
        $orderIncrementId = $this->creditMemoData['increment_id'];
        unset($this->creditMemoData['increment_id']);
        $giftCardAmount = $this->creditMemoData['gift_card_amount'];
        unset($this->creditMemoData['gift_card_amount']);
        $storeCreditAmount = $this->creditMemoData['store_credit_amount'];
        unset($this->creditMemoData['store_credit_amount']);

        try {
            $storeCreditMessage = '';
            if ($storeCreditAmount > 0) { // only available for Magento Enterprise
                $this->creditMemoData['refund_customerbalance_return'] = $storeCreditAmount;
                $this->creditMemoData['refund_customerbalance_return_enable'] = 1;

                $storeCreditMessage = (__('of which %1 were refunded to Store Credit', $this->priceHelper->currency($storeCreditAmount, true, false)));
            }

            $this->creditMemoLoader->setOrderId($order->getId());
            $this->creditMemoLoader->setCreditmemo($this->creditMemoData);

            $creditMemo = $this->creditMemoLoader->load();
            if (!$creditMemo) {
                return [
                    'success' => false,
                    'reason' => 'order-locked',
                    'status-title' => 'Refund Failed',
                    'status-message' => __('Unable to create Credit Memo and process the refund. Possible reasons: order not completed, order not invoiced, other reason. Check Magento order #%1', $orderIncrementId)
                ];
            }

            if (!$creditMemo->isValidGrandTotal()) {
                return [
                    'success' => false,
                    'status-title' => 'Refund Failed',
                    'status-message' => __('The credit memo\'s total must be positive.')
                ];
            }

            if (!empty($this->creditMemoData['comment_text'])) {
                $creditMemo->addComment(
                    $this->creditMemoData['comment_text'],
                    isset($this->creditMemoData['comment_customer_notify']), // is not set anywhere
                    isset($this->creditMemoData['is_visible_on_front']) // is not set anywhere
                );

                $creditMemo->setCustomerNote($this->creditMemoData['comment_text']);
                $creditMemo->setCustomerNoteNotify(isset($this->creditMemoData['comment_customer_notify']));
            }

            $creditMemo->getOrder()->setCustomerNoteNotify(!empty($this->creditMemoData['send_email']));

            if (!$this->creditMemoData['do_offline']) {
                $invoiceIds = [];
                $invoices = $order->getInvoiceCollection();
                foreach ($invoices as $invoice) {
                    if ($invoice->canRefund()) {
                        $invoiceIds[] = $invoice->getIncrementId();
                    }
                }

                if (!$invoiceIds) {
                    return [
                        'success' => false,
                        'status-title' => 'Refund Failed',
                        'status-message' => __('Invoice not found or cannot be refunded. Check Magento order #%1', $orderIncrementId)
                    ];
                }
                $invoiceObj = $this->invoice->loadByIncrementId(reset($invoiceIds));
                $creditMemo->setInvoice($invoiceObj);
            }

            // create refund and generate gift card
            if ($this->creditMemoManagement->refund($creditMemo, (bool)$this->creditMemoData['do_offline'])) {

                if ($giftCardAmount > 0) { // only available for Magento Enterprise
                    $giftCardMessage = $this->generateGiftCard($order, $giftCardAmount);

                    $this->pushSuccessMessage($giftCardMessage);
                }

                $this->pushSuccessMessage(__('Created Credit Memo %1 in amount of %2 %3; Request log ID: %4',
                    $creditMemo->getIncrementId(),
                    $this->priceHelper->currency($creditMemo->getBaseGrandTotal(), true, false),
                    $storeCreditMessage,
                    $this->requestLogId
                ));
            }

            if (!empty($creditMemoData['send_email'])) {
                $this->creditMemoSender->send($creditMemo);
            }

            return [
                'success' => true,
                'status-title' => 'Successfully Refunded',
                'status-message' => $this->finalSuccessMessage
            ];

        } catch (\Exception $e) {
            $responseMessage = $e->getMessage();
            $responseMessage .= (strpos($e->getMessage(), 'shipping amount allowed')) !== false ? '. Possible reasons: already issued in another refund' : '';
            return [
                'success' => false,
                'status-title' => 'Refund Failed',
                'status-message' => __('Credit Memo not created! %1. Check Magento order #%2', $responseMessage, $orderIncrementId)
            ];
        }
    }

    /**
     * @param $order
     * @param $giftCardAmount
     * @return array|Phrase
     */
    private function generateGiftCard($order, $giftCardAmount)
    {
        try {
            $customerEmail = $order->getCustomerEmail();
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
            $websiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();

            // @TODO Giftcard.php needs refactoring
            $this->giftCardInterface->createAndDeliverGiftCard($giftCardAmount, $customerEmail, $customerName, $websiteId);

            $giftCardCode = $this->giftCardInterface->getGeneratedCode();
            $orderHistoryComment = __('Created Gift Card %1 in amount of %2', $giftCardCode, $this->priceHelper->currency($giftCardAmount, true, false));
            $order->addStatusHistoryComment($orderHistoryComment)->save();

            return $orderHistoryComment;
        } catch (\Exception $e) {
            $message = __('Error occurred while creating Gift Card. Message %1', $e->getMessage());
            return ['success' => false, 'status-title' => 'Refund Failed', 'status-message' => $message];
        }
    }

    /**
     * @return bool
     */
    private function getOfflineFlag()
    {
        if ($this->checkRefundMethodExists('refund')) {
            return false; // online
        }

        return true; // for any others will be offline
    }

    /**
     * @return array
     */
    private function getReturnItems()
    {
        $returnItems = [];
        $items = isset($this->returnDetails['items']) ? $this->returnDetails['items'] : [];

        $prevItemId = 0;
        foreach ($items as $item) {
            // skip items with zero qty
            if (empty($item['quantity']) || $item['quantity'] == 0) {
                continue;
            }

            $itemQty = $item['quantity'];
            $currentItemId = $item['itemid'];
            if ($currentItemId == $prevItemId) {
                // increment item qty if same item but different reasons
                $itemQty += $returnItems[$prevItemId]['qty'];
            }
            $prevItemId = $currentItemId;

            $returnItems[$currentItemId] = [
                'qty' => $itemQty,
                'back_to_stock' => (bool) $this->returnDetails['restock']
            ];

            $this->appendItemReturnReason($item);
        }

        return $returnItems;
    }

    /**
     * @param $item
     */
    private function appendItemReturnReason($item)
    {
        $this->creditMemoData['comment_text'] = 'Return reason: ' . $item['reason_desc']. '<br/>';
    }

    /**
     * @return string
     */
    private function getRefundType()
    {
        return $this->helper->recursivelyGetArrayData(['logistics','type'], $this->returnDetails, 'offline');
    }

    /**
     * @return int|string
     */
    private function getStoreCreditAmount()
    {
        if ($this->checkRefundMethodExists('credit')) {
            return $this->returnDetails['logistics']['refund_types_amount']['credit'];
        }

        return 0;
    }

    /**
     * @return int|string
     */
    private function getGiftCardAmount()
    {
        if ($this->checkRefundMethodExists('gift_card')) {
            return $this->returnDetails['logistics']['refund_types_amount']['gift_card'];
        }

        return 0;
    }

    /**
     * @return bool
     */
    private function isEnterprise()
    {
        if (strtolower($this->productMetadata->getEdition()) === 'enterprise') {
            return true;
        }

        return false;
    }

    /**
     * @return bool|mixed
     */
    private function getOrder()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $this->creditMemoData['increment_id'])->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();

        $order = array_values(array_filter($orderList, function ($order) {
            return $order->getIncrementId() == $this->creditMemoData['increment_id'];
        }));

        return reset($order) ?? false;
    }

    /**
     * @return bool
     */
    private function refundShipping()
    {
        return (bool) $this->helper->recursivelyGetArrayData(['logistics','refund_shipping'], $this->returnDetails, 0);
    }

    /**
     * @return float
     */
    private function calculateShipping()
    {
        $total = (float) $this->helper->recursivelyGetArrayData(['logistics','refund_total'], $this->returnDetails, 0);
        $subtotal = (float) $this->helper->recursivelyGetArrayData(['logistics','refund_subtotal'], $this->returnDetails, 0);
        $cost = (float) $this->helper->recursivelyGetArrayData(['logistics','cost'], $this->returnDetails, 0);

        return $total - $subtotal + $cost;
    }

    /**
     * @param string $processedReturns
     */
    private function saveProcessedReturn(string $processedReturns)
    {
        $table = $this->returnsList->getResource()->getMainTable();
        try {
            $tableName = $this->resource->getTableName($table);
            $this->connection->insert($tableName, ['return_id' => $processedReturns]);
        } catch (\Exception $e) {
            $this->logger->error('WeSupply saving processed return to database error : '.$e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function collectReturnAdminComments()
    {
        $comment = '';
        $commentKeys = [
            'authorization_comment' => 'Authorization',
            'reception_comment' => 'Reception',
            'received_comment' => 'Received',
            'controlled_comment' => 'Controlled',
            'refunded_comment' => 'Refunded' // not set yet at this level !!!
        ];

        foreach ($commentKeys as $key => $label) {
            if (
                isset($this->returnDetails['activity'][$key]) &&
                !empty($this->returnDetails['activity'][$key])
            ) {
                $comment .= $label . ': ' . $this->returnDetails['activity'][$key]. '<br/>';
            }
        }

        return $comment;
    }

    /**
     *
     * Match refund method type with its corresponding refund amount
     *
     * @return void
     */
    private function matchRefundTypeAmountPairs()
    {
        $this->returnDetails['logistics']['refund_types_amount'] = [];
        if (
            isset($this->returnDetails['logistics']['type_multiple']) &&
            isset($this->returnDetails['logistics']['type_amount'])
        ) {
            foreach ($this->returnDetails['logistics']['type_multiple'] as $key => $refundType) {
                $this->returnDetails['logistics']['refund_types_amount'][$refundType] =
                    isset($this->returnDetails['logistics']['type_amount'][$key]) ?
                        $this->returnDetails['logistics']['type_amount'][$key] : 0.00;
            }
        }
    }

    private function checkRefundMethodExists($key)
    {
        if (
            array_key_exists($key, $this->returnDetails['logistics']['refund_types_amount']) &&
            $this->returnDetails['logistics']['refund_types_amount'][$key]
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $message
     * @return void
     */
    private function pushSuccessMessage($message)
    {
        $this->finalSuccessMessage .= $message . '. ';
    }

    /**
     * @return string
     */
    private function addAdditionalAdminComments()
    {
        $dateTime = $this->dateTimeFactory->create();
        $gmtDate = $dateTime->gmtDate();

        $additionalComment  = '***' . '<br/>';
        $additionalComment  .= 'Credit Memo Source: WeSupply' . '<br/>';
        $additionalComment .= 'Return Request: #' . $this->params['reference'] . '<br/>';
        $additionalComment .= 'Credit Memo Creation GMT Date: ' . $gmtDate . '<br/>';
        $additionalComment .= 'Request log ID: ' . $this->requestLogId;

        return $additionalComment;
    }
}
