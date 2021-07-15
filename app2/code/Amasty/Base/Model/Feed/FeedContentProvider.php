<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Model\Feed;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FeedContentProvider for reading file content by url
 */
class FeedContentProvider
{
    /**
     * Path to NEWS
     */
    const URN_NEWS = 'cdn.amasty.com/feed-news-segments.xml';//do not use https:// or http

    /**
     * Path to ADS
     */
    const URN_ADS = 'amasty.com/media/marketing/upsells.csv';

    /**
     * Path to EXTENSIONS
     */
    const URN_EXTENSIONS = 'cdn.amasty.com/feed-extensions-m2.xml';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Zend\Uri\Uri
     */
    private $baseUrlObject;

    public function __construct(
        CurlFactory $curlFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->curlFactory = $curlFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $url
     *
     * @return false|string
     */
    public function getFeedContent(string $url)
    {
        /** @var Curl $curlObject */
        $curlObject = $this->curlFactory->create();
        $curlObject->setConfig(
            [
                'timeout' => 2,
                'useragent' => 'Amasty Base Feed'
            ]
        );
        $curlObject->write(\Zend_Http_Client::GET, $url);
        $result = $curlObject->read();

        if ($result === false || $result === '') {
            return false;
        }
        $result = preg_split('/^\r?$/m', $result, 2);
        preg_match("/(?i)(\W|^)(Status: 404 File not found)(\W|$)/", $result[0], $notFoundFile);

        if ($notFoundFile) {
            return false;
        }
        $result = trim($result[1]);
        $curlObject->close();

        return $result;
    }

    public function getFeedUrl(string $urn): string
    {
        return 'https://' . $urn;
    }

    /**
     * @return string
     */
    public function getDomainZone()
    {
        $host = $this->getBaseUrlObject()->getHost();
        $host = explode('.', $host);

        return end($host);
    }

    /**
     * @return \Zend\Uri\Uri
     */
    private function getBaseUrlObject()
    {
        if ($this->baseUrlObject === null) {
            $url = $this->storeManager->getStore()->getBaseUrl();
            $this->baseUrlObject = \Zend\Uri\UriFactory::factory($url);
        }

        return $this->baseUrlObject;
    }
}
