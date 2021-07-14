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
 * @package     Mageplaza_SeoAnalysis
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoAnalysis\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 * @package Mageplaza\SeoAnalysis\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributes = [
            [
                'label'      => 'Meta Data Preview',
                'value'      => 'mp_meta_data_preview',
                'sort_order' => 5,
            ],
            [
                'label'      => 'Focus Keyword',
                'value'      => 'mp_main_keyword',
                'sort_order' => 60
            ],
            [
                'label'      => 'SEO insights',
                'value'      => 'mp_seo_insights',
                'sort_order' => 70
            ]
        ];
        $this->removeAttributes($eavSetup, $attributes);
        $this->addAttributes($eavSetup, $attributes);

        $setup->endSetup();
    }

    /**
     * Remove attributes
     *
     * @param $eavSetup
     * @param $attributes
     *
     * @return $this
     */
    public function removeAttributes($eavSetup, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($eavSetup->getAttributeId(Product::ENTITY, $attribute['value'])) {
                $eavSetup->removeAttribute(Product::ENTITY, $attribute['value']);
            }
        }

        return $this;
    }

    /**
     * Add attributes
     *
     * @param $eavSetup
     * @param $attributes
     *
     * @return $this
     */
    public function addAttributes($eavSetup, $attributes)
    {
        foreach ($attributes as $attribute) {
            $eavSetup->addAttribute(Product::ENTITY, $attribute['value'], [
                'type'             => 'varchar',
                'label'            => $attribute['label'],
                'input'            => 'text',
                'backend'          => '',
                'frontend'         => '',
                'class'            => '',
                'source'           => '',
                'global'           => Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'group'            => 'Search Engine Optimization',
                'default'          => '',
                'apply_to'         => '',
                'sort_order'       => $attribute['sort_order'],
                'visible_on_front' => false,
                'note'             => ''
            ]);
        }

        return $this;
    }
}
