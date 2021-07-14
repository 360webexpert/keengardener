<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

declare(strict_types=1);

namespace Amasty\Base\Model\Feed\FeedTypes;

use Amasty\Base\Model\Feed\FeedContentProvider;
use Amasty\Base\Model\ModuleInfoProvider;
use Amasty\Base\Model\Parser;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Config\CacheInterface;

class Ads
{
    const CSV_CACHE_ID = 'amasty_base_csv';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var FeedContentProvider
     */
    private $feedContentProvider;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Ad\Offline
     */
    private $adOffline;

    /**
     * @var ModuleInfoProvider
     */
    private $moduleInfoProvider;

    public function __construct(
        CacheInterface $cache,
        Serializer $serializer,
        FeedContentProvider $feedContentProvider,
        Parser $parser,
        Ad\Offline $adOffline,
        ModuleInfoProvider $moduleInfoProvider
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->feedContentProvider = $feedContentProvider;
        $this->parser = $parser;
        $this->adOffline = $adOffline;
        $this->moduleInfoProvider = $moduleInfoProvider;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        $cache = $this->cache->load(self::CSV_CACHE_ID);
        $unserializedCache = $cache ? $this->serializer->unserialize($cache) : null;

        return $unserializedCache ?: $this->getFeed();
    }

    /**
     * @return array
     */
    public function getFeed(): array
    {
        $result = [];

        if (!$this->moduleInfoProvider->isOriginMarketplace()) {
            $content = $this->feedContentProvider->getFeedContent(
                $this->feedContentProvider->getFeedUrl(FeedContentProvider::URN_ADS)
            );
            $result = $this->parser->parseCsv($content);
        }

        if (!$result) {
            $result = $this->adOffline->getOfflineData($this->moduleInfoProvider->isOriginMarketplace());
        }
        $result = $this->parser->trimCsvData($result, ['upsell_module_code', 'module_code']);
        $this->cache->save(
            $this->serializer->serialize($result),
            self::CSV_CACHE_ID,
            [self::CSV_CACHE_ID]
        );

        return $result;
    }
}
