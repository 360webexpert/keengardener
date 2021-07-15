<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace WeSupply\Toolbox\Model;

use Magento\Framework\Phrase;
use Magento\Config\Model\Config\CommentInterface;
use WeSupply\Toolbox\Helper\Data as Helper;

/**
 * Class ApiInfoComment
 * @package WeSupply\Toolbox\Model
 */

class ApiInfoComment implements CommentInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * ApiInfoComment constructor.
     * @param Helper $helper
     */
    public function __construct(
        Helper $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param string $elementValue
     * @return Phrase|string
     */
    public function getCommentText($elementValue)
    {
        if ($apiEndpoint = $this->helper->getApiEndpointByScope()) {
            $commentEl = '<span id="wesupply_api_integration_api_endpoint">' . $apiEndpoint . '</span>';
            $commentEl .= '<button title="' . __('Copy') . '" type="button" class="action-default copy-text scalable" data-copy-element="wesupply_api_integration_api_endpoint">';
            $commentEl .= '<span>' . __('Copy') . '</span>';
            $commentEl .= '</button>';
            $commentEl .= '<br/>' . __('Copy this API Endpoint into your WeSupply account.');

            return $commentEl;
        }

        return __('Cannot get API Endpoint');
    }

}