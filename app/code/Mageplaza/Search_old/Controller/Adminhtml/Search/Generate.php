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
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Controller\Adminhtml\Search;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Mageplaza\Search\Helper\Data;

/**
 * Class Generate
 * @package Mageplaza\Search\Controller\Adminhtml\Search
 */
class Generate extends Action
{
    /**
     * @var Data
     */
    protected $moduleHelper;

    /**
     * Generate constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        Data $dataHelper
    ) {
        $this->moduleHelper = $dataHelper;

        parent::__construct($context);
    }

    /**
     * execute js file data for all store & customer group
     * then redirect back to the system page
     */
    public function execute()
    {
        $errors = $this->moduleHelper->createJsonFile();
        if (empty($errors)) {
            $this->messageManager->addSuccessMessage(__('Generate search data successfully.'));
        } else {
            foreach ($errors as $error) {
                $this->messageManager->addErrorMessage($error);
            }
        }

        $this->_redirect('adminhtml/system_config/edit/section/mpsearch');
    }
}
