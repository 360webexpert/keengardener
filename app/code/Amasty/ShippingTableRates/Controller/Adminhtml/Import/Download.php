<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Controller\Adminhtml\Import;

use Amasty\Base\Controller\Adminhtml\Import\Download as BaseDownload;
use Amasty\ShippingTableRates\Controller\Adminhtml\AbstractImport as AbstractImport;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result\Raw;
use Magento\Backend\App\Action;

class Download extends AbstractImport
{
    /**
     * @var BaseDownload
     */
    private $download;

    public function __construct(
        BaseDownload $download,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->download = $download;
    }

    /**
     * @return Redirect|Raw
     */
    public function execute()
    {
        return $this->download->downloadSample('Amasty_ShippingTableRates');
    }
}
