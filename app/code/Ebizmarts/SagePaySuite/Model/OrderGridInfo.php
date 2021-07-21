<?php
/**
 * Copyright © 2019 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Model;

use Ebizmarts\SagePaySuite\Api\AdminGridColumnInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use \Magento\Sales\Api\OrderRepositoryInterface;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use \Ebizmarts\SagePaySuite\Helper\AdditionalInformation;

class OrderGridInfo implements AdminGridColumnInterface
{
    const IMAGE_PATH = 'Ebizmarts_SagePaySuite::images/icon-shield-';

    /**
     * @var RequestInterface
     */
    protected $requestInterface;

    /**
     * @var AdditionalInformation
     */
    private $serialize;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Logging instance
     * @var Logger
     */
    private $suiteLogger;

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * OrderGridInfo constructor.
     * @param RequestInterface $requestInterface
     * @param AdditionalInformation $serialize
     * @param OrderRepositoryInterface $orderRepository
     * @param Logger $suiteLogger
     * @param Repository $assetRepository
     */

    public function __construct(
        RequestInterface $requestInterface,
        AdditionalInformation $serialize,
        OrderRepositoryInterface $orderRepository,
        Logger $suiteLogger,
        Repository $assetRepository
    ) {
        $this->requestInterface = $requestInterface;
        $this->serialize = $serialize;
        $this->orderRepository = $orderRepository;
        $this->suiteLogger = $suiteLogger;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param array $dataSource
     * @param string $index
     * @param string $fieldName
     * @return array
     */
    public function prepareColumn(array $dataSource, string $index, string $fieldName) :array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (strpos($item['payment_method'], "sagepaysuite") !== false) {
                    $orderId = $item['entity_id'];
                    $params = ['_secure' => $this->requestInterface->isSecure()];
                    try {
                        $order = $this->orderRepository->get($orderId);
                    } catch (InputException $e) {
                        $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
                        continue;
                    } catch (NoSuchEntityException $e) {
                        $this->suiteLogger->logException($e, [__METHOD__, __LINE__]);
                        continue;
                    }
                    $payment = $order->getPayment();

                    if ($payment !== null) {
                        $additional = $payment->getAdditionalInformation();
                        if (is_string($additional)) {
                            $additional = $this->serialize->getUnserializedData($additional);
                        }
                        if (is_array($additional) && !empty($additional)) {
                            $image = $this->getImage($additional, $index);
                            $url = $this->assetRepository->getUrlWithParams($image, $params);
                            $item[$fieldName . '_src'] = $url;
                        }
                    }
                }
            }
        }
        return $dataSource;
    }

}
