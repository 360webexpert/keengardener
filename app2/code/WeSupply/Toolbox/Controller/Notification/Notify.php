<?php
namespace WeSupply\Toolbox\Controller\Notification;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Phrase;

class Notify extends  \Magento\Framework\App\Action\Action
{

    /**
     * @var
     */
    protected $orderNo;

    /**
     * @var
     */
    protected $phone;

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var
     */
    protected $country;

    /**
     * @var
     */
    protected $unsubscribe;

    /**
     * @var
     */
    protected $clientPhone;

    /**
     * @var \WeSupply\Toolbox\Api\WeSupplyApiInterface
     */
    protected $weSupplyApi;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \WeSupply\Toolbox\Helper\Data
     */
    protected $helper;

    /**
     * @var \WeSupply\Toolbox\Helper\PhoneCodes
     */
    protected $phoneCodes;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \WeSupply\Toolbox\Helper\Data $helper,
        \WeSupply\Toolbox\Helper\PhoneCodes $phoneCodes,
        \WeSupply\Toolbox\Api\WeSupplyApiInterface $weSupplyApi,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->phoneCodes = $phoneCodes;
        $this->weSupplyApi = $weSupplyApi;
        $this->resultJsonFactory = $jsonFactory;
    }



    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $validation = $this->_validateParams($params);
        $result = $this->resultJsonFactory->create();
        $this->weSupplyApi->setProtocol($this->helper->getProtocol());
        $this->weSupplyApi->setApiPath($this->helper->getWesupplyApiFullDomain());

        if ($validation) {
            /** Add validation error response */
            return $result->setData(['success' => false, 'error' => $validation]);
        } else {
            $response = $this->weSupplyApi->notifyWeSupply($this->orderNo, $this->phone, $this->prefix, $this->country,  $this->unsubscribe);
        }

        if(!$response){
            /** Add Api communication error response */
            return $result->setData(['success' => false, 'error' => 'Error signing up for SMS updates!']);
        }

        if(is_array($response)){

            if(isset($response['error'])) {
                return $result->setData(['success' => false, 'error' => 'Error encountered : '.$response['error']]);
            }

            $result->setData(['success' => false, 'error' => 'Error signing up for SMS updates!']);
        }

        return $result->setData(['success' => true]);
    }


    /**
     * @param $params
     * @return bool|Phrase
     */
    private function _validateParams($params)
    {
        $orderNo = isset($params['order']) ? trim($params['order']) : false;
        $phone = isset($params['phone']) ? trim($params['phone']) : false;
        $prefix = isset($params['prefix']) ? trim($params['prefix']) : false;
        $country = isset($params['country']) ? trim($params['country']) : false;
        $unsubscribe = isset($params['unsubscribe']) ? trim($params['unsubscribe']) : false;

        if (!$orderNo || empty($orderNo)) {
            return __('Order number missing');
        }
        if (!$phone || empty($phone)) {
            return __('Client Phone is missing');
        }
        if (!$prefix || empty($prefix)) {
            return __('Phone Prefix is missing');
        }
        if (!$country || empty($country)) {
            return __('Country Code is missing');
        }
        if(!$this->phoneCodes->validatePhoneNr($prefix, $phone)){
            return __('Invalid Phone Number');
        }

        $this->orderNo = $orderNo;
        $this->phone = $phone;
        $this->prefix = $prefix;
        $this->country = $country;
        $this->unsubscribe = $unsubscribe;

        $this->clientPhone = $this->prefix . $this->phone;

        return false;
    }



}
