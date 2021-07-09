<?php
namespace WeSupply\Toolbox\Controller\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use WeSupply\Toolbox\Api\OrderRepositoryInterface;
use WeSupply\Toolbox\Helper\Data as WeSupplyHelper;

class Fetch extends Action
{

    const  ALL_STORES = 'all';

    const MULTIPLE_STORE_ID_DELIMITER = ',';
    /**
     * maximum response xml file size allowed - expressed in MB
     */
    const MAX_FILE_SIZE_ALLOWED = '30';
    /**
     * @var string
     */
    protected $guid;

    /**
     * @var string
     */
    protected $startDate;

    /**
     * @var string
     */
    protected $endDate;

    /**
     * @var string
     */
    protected $storeIds;

    /**
     * @var WeSupplyHelper
     */
    protected $helper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Fetch constructor.
     * @param Context $context
     * @param WeSupplyHelper $helper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        WeSupplyHelper $helper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $response = '';
        $params = $this->getRequest()->getParams();
        $validationError = $this->_validateParams($params);

        if ($validationError) {
            /** Add the error response */
            $errorMessage = '';
            foreach ($validationError as $error) {
                $errorMessage .= $error . ' ';
            }

            $response .= $this->addResponseStatus('true', 'ERROR', trim($errorMessage));
        } else {
            /** Get the orders from the required interval */
            try {
                $xmlResponse = $this->fetchOrders();
            } catch (LocalizedException $e) {
                $xmlResponse = [
                    'error' => Http::STATUS_CODE_504,
                    'message' => $e->getMessage()
                ];
            }

            if (is_array($xmlResponse) && array_key_exists('error', $xmlResponse)) {
                $response .= $this->addResponseStatus('true', 'ERROR', $xmlResponse['message'] ?? 'General error occurred.');
            } else {
                $response .= $xmlResponse;
            }

            $response .= $this->addResponseStatus('false', 'SUCCESS', '');
        }

        $response = '<Orders>' . $response . '</Orders>';
        $xml = simplexml_load_string($response);  // Might be ignored this and just send the $response as result

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        $this->getResponse()->setBody($xml->asXML());
    }

    /**
     * @return array|string
     * @throws LocalizedException
     */
    protected function fetchOrders()
    {
        $ordersXml = '';
        $startDate = date('Y-m-d H:i:s', strtotime($this->startDate));
        $endDate = date('Y-m-d H:i:s', strtotime($this->endDate));

        $this->searchCriteriaBuilder->addFilter('updated_at', $startDate, 'gteq');
        $this->searchCriteriaBuilder->addFilter('updated_at', $endDate, 'lteq');

        // ignore already excluded orders
        $this->searchCriteriaBuilder->addFilter('is_excluded', 0, 'neg');

        /**
         * if storeId param has the all stores value, we are not filtering based on store id
         */
        if ($this->storeIds <> self::ALL_STORES) {
            $storeIds = array_filter(explode(self::MULTIPLE_STORE_ID_DELIMITER, $this->storeIds));
            $this->searchCriteriaBuilder->addFilter('store_id', $storeIds, 'in');
        }

        $this->sortOrderBuilder->setDirection(\Magento\Framework\Api\SortOrder::SORT_ASC);
        $this->sortOrderBuilder->setField('updated_at');
        $sortOrder = $this->sortOrderBuilder->create();
        $this->searchCriteriaBuilder->addSortOrder($sortOrder);

        $orders = $this->orderRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();

        if (count($orders)) {
            foreach($orders as $item) {
                $orderXml = $item->getInfo();
                $ordersXml .= $orderXml;
                /**
                 * extra check for the rare cases where massive xml file sizes are created
                 */
                $xmlFileSizeBit = $this->helper->strbits($ordersXml);
                $xmlFileSize = $this->helper->formatSizeUnits($xmlFileSizeBit);
                if($xmlFileSize >= self::MAX_FILE_SIZE_ALLOWED ) {
                    return array('error' => Http::STATUS_CODE_504,
                                 'message' => 'XML File Size exceeds '.self::MAX_FILE_SIZE_ALLOWED
                        );
                }
            }
        }

        return $ordersXml;
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
            "<ResponseDescription>$errorDescription</ResponseDescription>"
            . "</Response>";
    }

    /**
     * @param $params
     * @return array|bool
     */
    private function _validateParams($params)
    {
        $errors = [];
        $guid = isset($params['guid']) ? $params['guid'] : false;
        $startDate = isset($params['DateStart']) ? $params['DateStart'] : false;
        $endDate = isset($params['DateEnd']) ? $params['DateEnd'] : false;
        $externalIds = isset($params['AffiliateExternalId']) ? explode(self::MULTIPLE_STORE_ID_DELIMITER, $params['AffiliateExternalId']) : [];
        $requiredGuid = $this->helper->getGuid();

        if (!$guid) {
            $errors[] = 'Access Key is required.';
        }
        if ($guid != $requiredGuid) {
            $errors[] = 'Access Key is invalid.';
        }
        if (!$startDate) {
            $errors[] = 'DateStart is a required field.';
        }
        if (!$endDate) {
            $errors[] = 'DateEnd is a required field.';
        }

        $invalidStoreIds = $this->_validateStoreIds($externalIds);
        if ($invalidStoreIds) {
            $errors = array_merge($errors, $invalidStoreIds);
        }

        if ($errors) {
            return $errors;
        }

        $this->storeIds = implode(self::MULTIPLE_STORE_ID_DELIMITER, $externalIds);
        $this->guid = $guid;
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        return false;
    }

    /**
     * @param array $externalIds
     * @return array
     */
    private function _validateStoreIds($externalIds)
    {
        $errors = [];
        if (!$externalIds) {
            $errors[] = 'Store Id is a required field.';
        }

        // remove store ID "all" if multiple store IDs provided
        if (count($externalIds) > 1 && in_array('all', $externalIds)) {
            unset($externalIds[array_search('all', $externalIds)]);
        }

        // check if given store IDs exists
        if (!in_array('all', $externalIds)) {
            $storeIds = [];
            $storeList = $this->storeManager->getStores();
            foreach ($storeList as $store) {
                $storeIds[] = $store->getId();
            }

            $notExists = array_diff($externalIds, $storeIds);
            if ($notExists) {
                $errors[] = 'Store ID(s): ' . implode(', ', $notExists) . ' does not exist';
            }
        }

        return $errors;
    }
}
