<?php

namespace WeSupply\Toolbox\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use WeSupply\Toolbox\Helper\Data as Helper;

class AccessKey extends Field
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * AccessKey constructor.
     * @param Context $context
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->request = $context->getRequest();

        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getButtonHtml()
    {
        try {
            $button = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'id' => 'generate_button',
                    'label' => __('Regenerate')
                ]
            );

            return $button->toHtml();

        } catch (LocalizedException $e) {

        }
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('wesupply/system_config/generate');
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        if (!$this->helper->getGuid() || !$this->helper->getGuidByScope()) {
            // it was never saved
            return true;
        }
        if (!$this->request->getParam('store') && !$this->request->getParam('website')) {
            return true;
        }
        if ($this->helper->getGuid() != $this->helper->getGuidByScope()) {
            return true;
        }

        return false;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setData('readonly', true);
        return $element->getElementHtml() . $this->getTemplateHtml('copy') . $this->getTemplateHtml('generate');
    }

    /**
     * @param $type
     * @return mixed
     * @throws LocalizedException
     */
    protected function getTemplateHtml($type)
    {
        $content = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Template'
        )->setTemplate(
            'WeSupply_Toolbox::system/config/' . $type . '_access_key.phtml'
        );

        return $content->toHtml();
    }
}