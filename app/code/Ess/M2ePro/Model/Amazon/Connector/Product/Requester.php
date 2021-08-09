<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Product;

/**
 * Class \Ess\M2ePro\Model\Amazon\Connector\Product\Requester
 */
abstract class Requester extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Requester
{
    /**
     * @var \Ess\M2ePro\Model\Listing\Product
     */
    protected $listingProduct = null;

    /**
     * @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Logger
     */
    protected $logger = null;

    /**
     * @var \Ess\M2ePro\Model\Connector\Connection\Response\Message[]
     */
    protected $storedLogMessages = [];

    /**
     * @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator
     */
    protected $validatorObject = null;

    /**
     * @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Request
     */
    protected $requestObject = null;

    /**
     * @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData
     */
    protected $requestDataObject = null;

    protected $activeRecordFactory;
    protected $amazonFactory;

    //########################################

    /**
     * Requester constructor.
     * @param \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory
     * @param \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory
     * @param \Ess\M2ePro\Helper\Factory $helperFactory
     * @param \Ess\M2ePro\Model\Factory $modelFactory
     * @param \Ess\M2ePro\Model\Account|NULL $account
     * @param array $params
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Account $account = null,
        array $params = []
    ) {
        if (!isset($params['logs_action_id']) || !isset($params['status_changer'])) {
            throw new \Ess\M2ePro\Model\Exception('Product Connector has not received some params');
        }

        $this->amazonFactory = $amazonFactory;
        $this->activeRecordFactory = $activeRecordFactory;
        parent::__construct($helperFactory, $modelFactory, $account, $params);
    }

    //########################################

    public function setListingProduct(\Ess\M2ePro\Model\Listing\Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;

        if ($listingProduct->getActionConfigurator() === null) {
            $this->listingProduct->setActionConfigurator(
                $this->modelFactory->getObject('Amazon_Listing_Product_Action_Configurator')
            );
        }

        if ($this->listingProduct->getProcessingAction() === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Processing Action was not set.');
        }

        $this->account = $this->listingProduct->getAccount();
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Product_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            [
                'request_data'       => $this->getRequestData(),
                'configurator'       => $this->listingProduct->getActionConfigurator()->getSerializedData(),
                'listing_product_id' => $this->listingProduct->getId(),
                'lock_identifier'    => $this->getLockIdentifier(),
                'action_type'        => $this->getActionType(),
                'requester_params'   => $this->params
            ]
        );
    }

    //########################################

    abstract protected function getLogsAction();

    // ----------------------------------------

    protected function getLockIdentifier()
    {
        if ($this->getActionType() == \Ess\M2ePro\Model\Listing\Product::ACTION_LIST) {
            return 'list';
        }

        return strtolower($this->getOrmActionType());
    }

    //########################################

    public function process()
    {
        $this->getLogger()->setStatus(\Ess\M2ePro\Helper\Data::STATUS_SUCCESS);

        if ($this->validateAndProcessParentListingProduct()) {
            $this->writeStoredLogMessages();
            $this->getProcessingRunner()->stop();
            return;
        }

        if (!$this->validateListingProduct() || !$this->validateConfigurator()) {
            $this->writeStoredLogMessages();
            $this->getProcessingRunner()->stop();
            return;
        }

        $this->eventBeforeExecuting();

        $processingRunner = $this->getProcessingRunner();
        $processingRunner->setParams($this->getProcessingParams());
        $processingRunner->setResponserModelName($this->getResponserModelName());
        $processingRunner->setResponserParams($this->getResponserParams());

        $processingRunner->prepare();
    }

    //########################################

    public function getStatus()
    {
        return $this->getLogger()->getStatus();
    }

    //########################################

    protected function validateListingProduct()
    {
        $validator = $this->getValidatorObject();

        $validationResult = $validator->validate();

        foreach ($validator->getMessages() as $messageData) {
            /** @var \Ess\M2ePro\Model\Connector\Connection\Response\Message $message */
            $message = $this->modelFactory->getObject('Connector_Connection_Response_Message');
            $message->initFromPreparedData($messageData['text'], $messageData['type']);

            $this->storeLogMessage($message);
        }

        return $validationResult;
    }

    /**
     * Some data parts can be disallowed from configurator on validateListingProduct() action
     * @return bool
     */
    protected function validateConfigurator()
    {
        /** @var \Ess\M2ePro\Model\Listing\Product\Action\Configurator $configurator */
        $configurator = $this->listingProduct->getActionConfigurator();
        if (empty($configurator->getAllowedDataTypes())) {
            /** @var \Ess\M2ePro\Model\Connector\Connection\Response\Message $message */
            $message = $this->modelFactory->getObject('Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                'There was no need for this action. It was skipped.
                Please check the log message above for more detailed information.',
                \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_ERROR
            );

            $this->storeLogMessage($message);
            return false;
        }

        return true;
    }

    //########################################

    protected function validateAndProcessParentListingProduct()
    {
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
        $amazonListingProduct = $this->listingProduct->getChildObject();

        if (!$amazonListingProduct->getVariationManager()->isRelationParentType()) {
            return false;
        }

        if (!$amazonListingProduct->getGeneralId()) {
            return false;
        }

        $childProducts = $amazonListingProduct->getVariationManager()->getTypeModel()->getChildListingsProducts();
        $childProducts = $this->filterChildListingProductsByStatus($childProducts);
        $childProducts = $this->filterLockedChildListingProducts($childProducts);

        if (empty($childProducts)) {
            $this->listingProduct->setData('no_child_for_processing', true);
            return false;
        }

        return false;
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Product[] $listingProducts
     * @return \Ess\M2ePro\Model\Listing\Product[]
     */
    abstract protected function filterChildListingProductsByStatus(array $listingProducts);

    /**
     * @param \Ess\M2ePro\Model\Listing\Product[] $listingProducts
     * @return \Ess\M2ePro\Model\Listing\Product[]
     */
    protected function filterLockedChildListingProducts(array $listingProducts)
    {
        $resultListingProducts = [];
        foreach ($listingProducts as $listingProduct) {
            $lockItemManager = $this->modelFactory->getObject('Lock_Item_Manager', [
                'nick' => \Ess\M2ePro\Helper\Component\Amazon::NICK.'_listing_product_'.$listingProduct->getId()
            ]);

            if ($listingProduct->isSetProcessingLock('in_action') || $lockItemManager->isExist()) {
                continue;
            }

            $resultListingProducts[] = $listingProduct;
        }

        return $resultListingProducts;
    }

    //########################################

    public function getRequestData()
    {
        if ($this->requestDataObject !== null) {
            return $this->requestDataObject->getData();
        }

        $requestObject = $this->getRequestObject();
        $requestDataRaw = $requestObject->getRequestData();

        foreach ($requestObject->getWarningMessages() as $messageText) {
            /** @var \Ess\M2ePro\Model\Connector\Connection\Response\Message $message */
            $message = $this->modelFactory->getObject('Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                $messageText,
                \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_WARNING
            );

            $this->storeLogMessage($message);
        }

        $requestDataRaw = array_merge($requestDataRaw, ['id' => $this->listingProduct->getId()]);

        $this->buildRequestDataObject($requestDataRaw);

        return $requestDataRaw;
    }

    protected function getResponserParams()
    {
        $logMessages = [];
        foreach ($this->getStoredLogMessages() as $message) {
            $logMessages[] = $message->asArray();
        }

        $metaData = $this->getRequestObject()->getMetaData();
        $metaData['log_messages'] = $logMessages;

        $product = [
            'request'          => $this->getRequestData(),
            'request_metadata' => $metaData,
            'configurator'     => $this->listingProduct->getActionConfigurator()->getSerializedData(),
            'id'               => $this->listingProduct->getId(),
        ];

        return [
            'account_id'      => $this->account->getId(),
            'action_type'     => $this->getActionType(),
            'lock_identifier' => $this->getLockIdentifier(),
            'logs_action'     => $this->getLogsAction(),
            'logs_action_id'  => $this->getLogger()->getActionId(),
            'status_changer'  => $this->params['status_changer'],
            'params'          => $this->params,
            'product'         => $product,
        ];
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Logger
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getLogger()
    {
        if ($this->logger === null) {

            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Logger $logger */
            $logger = $this->modelFactory->getObject('Amazon_Listing_Product_Action_Logger');

            $logger->setActionId((int)$this->params['logs_action_id']);
            $logger->setAction($this->getLogsAction());

            switch ($this->params['status_changer']) {
                case \Ess\M2ePro\Model\Listing\Product::STATUS_CHANGER_UNKNOWN:
                    $initiator = \Ess\M2ePro\Helper\Data::INITIATOR_UNKNOWN;
                    break;
                case \Ess\M2ePro\Model\Listing\Product::STATUS_CHANGER_USER:
                    $initiator = \Ess\M2ePro\Helper\Data::INITIATOR_USER;
                    break;
                default:
                    $initiator = \Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION;
                    break;
            }

            $logger->setInitiator($initiator);

            $this->logger = $logger;
        }

        return $this->logger;
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator
     * @throws \Ess\M2ePro\Model\Exception
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getValidatorObject()
    {
        if ($this->validatorObject === null) {

            /** @var $validator \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator */
            $validator = $this->modelFactory->getObject(
                'Amazon\Listing\Product\Action\Type\\' . $this->getOrmActionType() . '\Validator'
            );

            $validator->setParams($this->params);
            $validator->setListingProduct($this->listingProduct);
            $validator->setConfigurator($this->listingProduct->getActionConfigurator());

            $this->validatorObject = $validator;
        }

        return $this->validatorObject;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Request
     * @throws \Ess\M2ePro\Model\Exception
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getRequestObject()
    {
        if ($this->requestObject === null) {
            /** @var $request \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Request */
            $request = $this->modelFactory->getObject(
                'Amazon\Listing\Product\Action\Type\\' . $this->getOrmActionType() . '\Request'
            );

            $request->setParams($this->params);
            $request->setListingProduct($this->listingProduct);
            $request->setConfigurator($this->listingProduct->getActionConfigurator());
            $request->setCachedData($this->getValidatorObject()->getData());

            $this->requestObject = $request;
        }

        return $this->requestObject;
    }

    // ----------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    /**
     * @param array $data
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function buildRequestDataObject(array $data)
    {
        if ($this->requestDataObject === null) {

            /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData $requestData */
            $requestData = $this->modelFactory->getObject('Amazon_Listing_Product_Action_RequestData');

            $requestData->setData($data);
            $requestData->setListingProduct($this->listingProduct);

            $this->requestDataObject = $requestData;
        }

        return $this->requestDataObject;
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Amazon\Connector\Product\ProcessingRunner
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    protected function getProcessingRunner()
    {
        if ($this->processingRunner !== null) {
            return $this->processingRunner;
        }

        $this->processingRunner = $this->modelFactory->getObject($this->getProcessingRunnerModelName());

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Processing $processingAction */
        $processingAction = $this->listingProduct->getProcessingAction();

        $this->processingRunner->setProcessingObject($processingAction->getProcessing());
        $this->processingRunner->setProcessingAction($processingAction);

        return $this->processingRunner;
    }

    //########################################

    protected function getOrmActionType()
    {
        switch ($this->getActionType()) {
            case \Ess\M2ePro\Model\Listing\Product::ACTION_LIST:
                return 'ListAction';
            case \Ess\M2ePro\Model\Listing\Product::ACTION_RELIST:
                return 'Relist';
            case \Ess\M2ePro\Model\Listing\Product::ACTION_REVISE:
                return 'Revise';
            case \Ess\M2ePro\Model\Listing\Product::ACTION_STOP:
                return 'Stop';
            case \Ess\M2ePro\Model\Listing\Product::ACTION_DELETE:
                return 'Delete';
        }

        throw new \Ess\M2ePro\Model\Exception('Wrong Action type');
    }

    abstract protected function getActionType();

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Connector\Connection\Response\Message[]
     */
    protected function getStoredLogMessages()
    {
        return $this->storedLogMessages;
    }

    protected function storeLogMessage(\Ess\M2ePro\Model\Connector\Connection\Response\Message $message)
    {
        $this->storedLogMessages[] = $message;
    }

    protected function writeStoredLogMessages()
    {
        foreach ($this->getStoredLogMessages() as $message) {
            $this->getLogger()->logListingProductMessage(
                $this->listingProduct,
                $message
            );
        }
    }

    //########################################
}
