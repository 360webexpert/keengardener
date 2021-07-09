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

namespace Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard;

use Magento\Backend\Block\Dashboard\Grid;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Mageplaza\SeoDashboard\Helper\Data as SeoDashboardData;

/**
 * Class Tab
 * @package Mageplaza\SeoDashboard\Block\Adminhtml\Dashboard
 */
abstract class Tab extends Grid
{
    /**
     * View more url
     */
    const VIEW_MORE_URL = 'seo/duplicate';

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_SeoDashboard::dashboard/grid.phtml';

    /**
     * @var SeoDashboardData
     */
    protected $_seoDashboardData;

    /**
     * Constructor
     *
     * @param SeoDashboardData $seoDashboardData
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        SeoDashboardData $seoDashboardData,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->_seoDashboardData = $seoDashboardData;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Get View more url
     *
     * @return string
     */
    abstract public function getViewMoreUrl();
}
