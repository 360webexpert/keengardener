<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Block\Adminhtml\System\Config\Fieldset;

use Ebizmarts\SagePaySuite\Model\Config\ModuleVersion;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Template;

class Version extends Template implements RendererInterface
{
    /**
     * @var ModuleVersion
     */
    private $moduleVersion;

    /**
     * Version constructor.
     *
     * @param ModuleVersion $moduleVersion
     * @param Context $context
     * @param array $data
     */
    public function __construct(ModuleVersion $moduleVersion, Context $context, array $data = [])
    {
        $this->moduleVersion = $moduleVersion;
        parent::__construct($context, $data);
    }
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        if ($element->getData('group')['id'] == 'version') {
            $html = $this->toHtml();
        }
        return $html;
    }

    public function getVersion()
    {
        return $this->moduleVersion->getModuleVersion('Ebizmarts_SagePaySuite');
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'Ebizmarts_SagePaySuite::system/config/fieldset/version.phtml';
    }
}
