<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoDashboard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Reports\Controller\Adminhtml\Report\Statistics;
use Psr\Log\LoggerInterface;

/**
 * Class RefreshStatistics
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Dashboard
 */
class RefreshStatistics extends Statistics
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Date $dateFilter
     * @param array $reportTypes
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Date $dateFilter,
        array $reportTypes,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $dateFilter, $reportTypes);

        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        try {
            $collectionsNames = array_values($this->reportTypes);
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate();
            }
            $this->messageManager->addSuccess(__('We updated lifetime statistic.'));
        } catch (Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh lifetime statistics.'));
            $this->logger->critical($e);
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*');
    }
}
