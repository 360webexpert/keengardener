<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;

/**
 * Class SpecificEstimationRanges
 * @package WeSupply\Toolbox\Block\System\Config
 */

class SpecificEstimationRanges extends Field
{
    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var string
     */
    protected $_template = 'WeSupply_Toolbox::system/config/specific_estimation_ranges.phtml';

    /**
     * SpecificEstimationRanges constructor.
     *
     * @param Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Escaper $escaper,

        array $data = []
    )
    {
        $this->_escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * @param $content
     * @return string
     */
    public function htmlEscape($content)
    {
        return $this->_escaper->escapeHtml($content);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}