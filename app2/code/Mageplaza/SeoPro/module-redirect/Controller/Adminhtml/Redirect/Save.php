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
 * @category   Mageplaza
 * @package    Mageplaza_Redirects
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Redirects\Controller\Adminhtml\Redirect;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Mageplaza\Redirects\Helper\Data as HelperConfig;

/**
 * Class Save
 * @package Mageplaza\Redirects\Controller\Adminhtml\Redirect
 */
class Save extends Action
{
    /**
     * @type JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var HelperConfig
     */
    protected $helperConfig;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param UrlRewrite $urlRewrite
     * @param HelperConfig $helperConfig
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        UrlRewrite $urlRewrite,
        HelperConfig $helperConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlRewrite        = $urlRewrite;
        $this->helperConfig      = $helperConfig;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        if (!$this->helperConfig->isRedirectEnabled()) {
            return $this;
        }
        $status = false;

        $message = __('Can\'t redirect. Please try again!');
        try {
            $requestPath = $this->getRequest()->getParam('request_path');
            if ($this->getRequest()->getParam('cancel', false)) {
                $this->unsetDataRedirect($this->getRequest()->getParam('type_process'), $requestPath);
                $message = __('Cancel success!');
                $status  = true;
            } else {
                if (strpos($requestPath, '//') !== false) {
                    $message = __('Do not use two or more consecutive slashes in the request path.');
                } elseif (strpos($requestPath, '#') !== false) {
                    $message = __('Anchor symbol (#) is not supported in request path.');
                } else {
                    $targetPath = $this->getRequest()->getParam('target_path');
                    $model      = $this->getUrlRewriteModel();
                    $model->setEntityType('custom')
                        ->setRequestPath($requestPath)
                        ->setTargetPath($targetPath)
                        ->setRedirectType($this->getRequest()->getParam('redirect_type'))
                        ->setStoreId($this->getRequest()->getParam('store_id', 0))
                        ->setDescription($this->getRequest()->getParam('description'));
                    $model->save();

                    if ($model->getId()) {
                        $this->unsetDataRedirect($this->getRequest()->getParam('type_process'), $requestPath);
                        $status  = true;
                        $message = __('Redirect success!');
                    }
                }
            }
        } catch (Exception $e) {
            $status  = false;
            $message = __("Can't redirect. %1", $e->getMessage());
        }

        /** @var Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData(['success' => $status, 'message' => $message]);
    }

    /**
     * @return UrlRewrite
     */
    public function getUrlRewriteModel()
    {
        $urlRewriteId = (int) $this->getRequest()->getParam('id', 0);

        return $this->urlRewrite->load($urlRewriteId);
    }

    /**
     * Remove data redirect from session
     *
     * @param $type
     * @param $value
     *
     * @return $this
     */
    public function unsetDataRedirect($type, $value)
    {
        switch (trim($type)) {
            case 'product':
                $productDeleted = $this->_getSession()->getProductDeleted();
                unset($productDeleted[array_search($value, $productDeleted)]);
                $this->_getSession()->setProductDeleted($productDeleted);
                break;
            case 'category':
                $categoryDeleted = $this->_getSession()->getCategoryDeleted();
                unset($categoryDeleted[array_search($value, $categoryDeleted)]);
                $this->_getSession()->setCategoryDeleted($categoryDeleted);
                break;
            case 'page':
                $pageDeleted = $this->_getSession()->getPageDeleted();
                unset($pageDeleted[array_search($value, $pageDeleted)]);
                $this->_getSession()->setPageDeleted($pageDeleted);
                break;
            default:
        }

        return $this;
    }
}
