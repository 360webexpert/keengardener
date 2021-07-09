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

/**
 * Class Robot
 * @package Mageplaza\SeoDashboard\Helper\Checklist
 */
class Robot
{
    const PATH_UN_SECURE = 'web/unsecure/base_url';
    const PATH_SECURE    = 'web/secure/base_url';
    const USER_AGENT     = 'User-agent';
    const DISALLOW_1     = 'Disallow: /';
    const DISALLOW_2     = 'Disallow: *';
    const SITE_MAP       = 'Sitemap:';
    const USER_AGENT_ALL = 'User-Agent: *';
    const DISALLOW       = 'Disallow';

    /**
     * @var Content
     */
    protected $_content;

    /**
     * @var array
     */
    protected $allLine = [];

    /**
     * Robot constructor.
     *
     * @param Content $content
     */
    public function __construct(Content $content)
    {
        $this->_content = $content;
    }

    /**
     * Has exist robot file
     * @return bool
     */
    public function hasExistRobotFile()
    {
        if (empty($allLine = $this->getAllLine())) {
            return false;
        }

        foreach ($allLine as $line) {
            if (!empty($line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check robot inline
     *
     * @param $content
     * @param $key
     *
     * return true if exists $key in $content
     *
     * @return bool
     */
    public function checkRobotInline($content, $key)
    {
        if (empty($content)) {
            return false;
        }

        $position = strpos($content, $key);
        if (is_numeric($position)) {
            return true;
        }

        return false;
    }

    /**
     * Get all line
     * @return array
     */
    public function getAllLine()
    {
        if (!$this->allLine) {
            $this->allLine = $this->_content->removeCommentInLineTXTFile('robots.txt');//TODO
        }

        return $this->allLine;
    }

    /**
     * Check robot disallow
     * @return bool
     */
    public function checkRobotDisallow()
    {
        foreach ($this->getAllLine() as $line) {
            if (in_array($line, $this->getRobotDisallowPattern())) {
                return false;
            }
        }

        return true;
    }

    /**
     * get robot disallow pattern
     * @return array
     */
    public function getRobotDisallowPattern()
    {
        return [strtolower(self::DISALLOW_1), strtolower(self::DISALLOW_2)];
    }

    /**
     * Has robot site map
     * @return bool
     */
    public function hasRobotSiteMap()
    {
        if (!$this->hasExistRobotFile()) {
            return false;
        }

        foreach ($this->getAllLine() as $line) {
            $line = str_replace(' ', '', $line);
            if ($this->checkRobotInline($line, strtolower(self::SITE_MAP))) {
                if (str_replace(strtolower(self::SITE_MAP), '', strtolower($line))) {
                    return true;
                }
            }
        }

        return false;
    }
}
