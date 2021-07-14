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

namespace Mageplaza\SeoDashboard\Controller\Adminhtml\Checklist;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\GoogleAnalytics\Helper\Data;
use Mageplaza\SeoDashboard\Helper\Checklist\Content;
use Mageplaza\SeoDashboard\Helper\Checklist\Homepage;
use Mageplaza\SeoDashboard\Helper\Checklist\Robot;
use Mageplaza\SeoDashboard\Helper\Checklist\SiteMap;
use Mageplaza\SeoDashboard\Helper\Data as HelperConfig;

/**
 * Class Index
 * @package Mageplaza\SeoDashboard\Controller\Adminhtml\Checklist
 */
class Index extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_SeoDashboard::checklist';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var HelperConfig
     */
    protected $_config;

    /**
     * @var Robot
     */
    protected $_robot;

    /**
     * @var SiteMap
     */
    protected $_siteMap;

    /**
     * @var Homepage
     */
    protected $_homepage;

    /**
     * @var Data
     */
    protected $googleAnalyticsHelper;

    /**
     * @var Content
     */
    protected $content;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Data $googleAnalyticsHelper
     * @param Homepage $homepage
     * @param SiteMap $siteMap
     * @param Robot $robot
     * @param HelperConfig $config
     * @param PageFactory $resultPageFactory
     * @param Content $content
     */
    public function __construct(
        Context $context,
        Data $googleAnalyticsHelper,
        Homepage $homepage,
        SiteMap $siteMap,
        Robot $robot,
        HelperConfig $config,
        PageFactory $resultPageFactory,
        Content $content
    ) {
        $this->googleAnalyticsHelper = $googleAnalyticsHelper;
        $this->_homepage             = $homepage;
        $this->_siteMap              = $siteMap;
        $this->_robot                = $robot;
        $this->_config               = $config;
        $this->resultPageFactory     = $resultPageFactory;
        $this->content               = $content;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->getResultPageFactory()->create();
        if ($this->_config->isEnabled()) {
            $this->checklist();
            $resultPage->setActiveMenu('Mageplza_SeoDashboard::checklist');
            $resultPage->getConfig()->getTitle()->prepend(__('Checklist'));

            return $resultPage;
        }
        $this->message(
            HelperConfig::WARNING,
            __(
                'Mageplaza SEO extension is being disabled. Please enable it <a href="%1">here</a>',
                $this->getUrl('adminhtml/system_config/edit/section/seo')
            )
        );

        return $resultPage;
    }

    /**
     * @return PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * Add message
     *
     * @param $state
     * @param $msg
     */
    public function message($state, $msg)
    {
        switch ($state) {
            case HelperConfig::WARNING:
                $this->messageManager->addWarning($msg);
                break;
            case HelperConfig::ERROR:
                $this->messageManager->addError($msg);
                break;
            case HelperConfig::SUCCESS:
                $this->messageManager->addSuccess($msg);
                break;
        }
    }

    /**
     * Check list
     * @return $this
     */
    public function checklist()
    {
        $this->checkHomepage()
            ->checklistConfig()
            ->checkRobotFile()
            ->checkRobotDisallow()
            ->checkRobotSiteMap()
            ->hasSiteMapFile()
            ->hasGa();

        return $this;
    }

    /**
     *  Check set up Google Analytics
     */
    public function hasGa()
    {
        if ($this->googleAnalyticsHelper->isGoogleAnalyticsAvailable($this->getStoreId())) {
            $this->message(HelperConfig::SUCCESS, 'Congrats! Google Analytics is set!');
        } else {
            $this->message(
                HelperConfig::WARNING,
                __(
                    'We recommend that you should set up Google Analytics for your site. Click <a href="%1">here.</a>',
                    'https://www.mageplaza.com/kb/how-to-setup-google-universal-analytics-magento-2.html?utm_source=refferal&utm_medium=backend&utm_campaign=seo-checklist'
                )
            );
        }
    }

    /**
     * Check robot disallow
     * @return $this
     */
    public function checkRobotDisallow()
    {
        if (!$this->_robot->hasExistRobotFile()) {
            return $this;
        }

        if (!$this->_robot->checkRobotDisallow()) {
            $this->message(
                HelperConfig::ERROR,
                __('Your robots.txt file disallow Search Engine index your store. Please check yourdomain.com/robots.txt file for more information.')
            );
        }

        return $this;
    }

    /**
     * Has Site map file
     * @return $this
     */
    public function hasSiteMapFile()
    {
        if (!$this->_robot->hasRobotSiteMap()) {
            return $this;
        }

        if ($this->_siteMap->hasSiteMapFile()) {
            $this->message(HelperConfig::SUCCESS, __('Your store has sitemap.xml file'));
        } else {
            $this->message(
                HelperConfig::WARNING,
                __('We recommend that you should add sitemap.xml in Magento directory.')
            );
        }

        return $this;
    }

    /**
     * Check home page
     * @return $this
     */
    public function checkHomepage()
    {
        if ($this->_homepage->checkHomepage($this->getStoreId())) {
            $this->message(HelperConfig::SUCCESS, __('Congrats! Homepage page title, description is set!'));
        } else {
            $this->message(HelperConfig::WARNING, __(
                'You should setup Meta Title, Description for Homepage: <i>Content > Elements > Pages > <a href="%1">Homepage</a></i> <br />Homepage page maybe change in <i>Stores > Configuration >General> Web > <a href="%2">Default Pages</a></i>',
                $this->getUrl('cms/page/index'),
                $this->getUrl('adminhtml/system_config/edit/section/web') . '#web_default-head'
            ));
        }

        return $this;
    }

    /**
     * Check robot site map
     * @return $this|bool
     */
    public function checkRobotSiteMap()
    {
        if (!$this->_robot->hasExistRobotFile()) {
            return $this;
        }

        if ($this->_robot->hasRobotSiteMap()) {
            $this->message(HelperConfig::SUCCESS, __('Great! You declare Sitemap URL in robots.txt file.'));
        } else {
            $this->message(
                HelperConfig::WARNING,
                __('We recommend you add Sitemap URL in robots.txt file or add submit sitemap to Google Search Console / Webmaster Tools.')
            );
        }

        return $this;
    }

    /**
     * check list config
     * @return $this
     */
    public function checklistConfig()
    {
        foreach ($this->_config->getConfigChecklist($this->getStoreId()) as $path => $value) {
            $config = $this->_config->getConfigValue($path, $this->getStoreId());
            if ($config === null) {
                $config = 0;
            }
            $this->message($value[$config]['type'], $value[$config]['message']);
        }

        return $this;
    }

    /**
     * Check exist robot file
     */
    public function checkRobotFile()
    {
        if ($this->_robot->hasExistRobotFile()) {
            $this->message(HelperConfig::SUCCESS, __('Great! robots.txt file is exist.'));
        } else {
            $this->message(HelperConfig::WARNING, __('We recommend that you should create robots.txt file.'));
        }

        return $this;
    }

    /**
     * Get store id
     * @return int|mixed
     */
    public function getStoreId()
    {
        return $this->content->getStore()->getId();
    }
}
