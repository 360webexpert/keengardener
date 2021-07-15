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

namespace Mageplaza\SeoDashboard\Helper\Checklist;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SeoDashboard\Helper\Data;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;

/**
 * Class Content
 * @package Mageplaza\SeoDashboard\Helper\Checklist
 */
class Content
{
    const INDEX           = 'index.php';
    const HTML_CODE_ERROR = '404';

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Content constructor.
     *
     * @param CurlFactory $curlFactory
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlFactory $curlFactory,
        Data $helper,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->curlFactory   = $curlFactory;
        $this->_helper       = $helper;
        $this->_storeManager = $storeManager;
        $this->request       = $request;
        $this->logger        = $logger;
    }

    /**
     * Get content
     *
     * @param $fileName
     *
     * @return bool|null|resource
     */
    public function getContent($fileName)
    {
        if ($this->check404Page($fileName)) {
            return null;
        }

        return fopen($this->getBaseURl() . $fileName, "r");
    }

    /**
     * Get inline content
     *
     * @param $fileName
     *
     * @return array
     */
    public function getInlineContent($fileName)
    {
        $allLine = [];

        try {
            if ($file = $this->getContent($fileName)) {
                while (!feof($file)) {
                    if ($line = trim(fgets($file))) {
                        $allLine[] = $line;
                    }
                }
                fclose($file);
            }
        } catch (Exception $e) {
            $this->logger->warning('Exception getBaseUrl: ' . $e);
        }

        return $allLine;
    }

    /**
     * remove Comment inline txt file
     *
     * @param $fileName
     *
     * @return array
     */
    public function removeCommentInLineTXTFile($fileName)
    {
        $allLine = [];
        foreach ($this->getInlineContent($fileName) as $line) {
            $count = strpos($line, '#');
            if (!is_numeric($count)) {
                $allLine[] = strtolower($line);
                continue;
            }
            $allLine[] = strtolower(substr($line, 0, $count));
        }

        return $allLine;
    }

    /**
     * Get html content
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getHtmlContent($storeId = null)
    {
        $curl = $this->curlFactory->create();
        $curl->write(Zend_Http_Client::GET, $this->getBaseURl($storeId), '1.0');
        $data = $curl->read();
        $curl->close();

        return $data;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getBaseURl($storeId = null)
    {
        $store   = $this->getStore();
        $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_LINK, $store->isUrlSecure());

        if ($this->_helper->getConfigValue('web/seo/use_rewrites', $storeId) == 0) {
            $baseUrl = str_replace(self::INDEX, '', $baseUrl);
        }

        return $baseUrl;
    }

    /**
     * Check 404 page
     *
     * @param $fileName
     *
     * @return bool
     */
    public function check404Page($fileName)
    {
        $curl = $this->curlFactory->create();
        $curl->write(Zend_Http_Client::GET, $this->getBaseURl() . $fileName, '1.0');
        $code = $curl->getInfo(CURLINFO_HTTP_CODE);
        $curl->close();

        return $code == self::HTML_CODE_ERROR;
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        $websiteCode = $this->request->getParam('website');
        $storeCode   = $this->request->getParam('store');
        if (!$storeCode && $websiteCode) {
            try {
                $store = $this->_storeManager->getWebsite($websiteCode)->getDefaultStore();
            } catch (Exception $e) {
                $store = $this->_storeManager->getDefaultStoreView();
            }
        } elseif ($storeCode) {
            $store = $this->_storeManager->getStore($storeCode);
        } else {
            $store = $this->_storeManager->getDefaultStoreView();
        }

        return $store;
    }
}
