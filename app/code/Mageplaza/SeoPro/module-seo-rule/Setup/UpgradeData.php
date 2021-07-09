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
 * @package     Mageplaza_SeoRule
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SeoRule\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mageplaza\SeoRule\Model\Rule\Source\ApplyTemplate;
use Mageplaza\SeoRule\Model\Rule\Source\Attribute;
use Mageplaza\SeoRule\Model\Rule\Source\Status;
use Mageplaza\SeoRule\Model\Rule\Source\Type;
use Mageplaza\SeoRule\Model\RuleFactory;

/**
 * Class UpgradeData
 * @package Mageplaza\SeoRule\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var
     */
    protected $ruleFactory;

    /**
     * @var Attribute
     */
    protected $attrbute;

    /**
     * @var State
     */
    protected $state;

    /**
     * UpgradeData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param RuleFactory $ruleFactory
     * @param Attribute $attribute
     * @param State $state
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        RuleFactory $ruleFactory,
        Attribute $attribute,
        State $state
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory      = $eavSetupFactory;
        $this->ruleFactory          = $ruleFactory;
        $this->attrbute             = $attribute;
        $this->state                = $state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->state->setAreaCode('adminhtml');
            $attributeString = $this->attrbute->getListAttribute(true, ' ');
            $data            = [
                'name'             => "Default layered navigation",
                'entity_type'      => Type::LAYERED_NAVIGATION,
                'apply_template'   => ApplyTemplate::FORCE_UPDATE,
                'meta_title'       => $attributeString . ' in {{category_name}}',
                'meta_description' => $attributeString,
                'meta_keywords'    => $attributeString,
                'meta_robots'      => 'INDEX,FOLLOW',
                'status'           => Status::ENABLE,
                'stores'           => '0,1',
                'sort_order'       => 999,

            ];
            $rule            = $this->ruleFactory->create();
            $rule->addData($data)->save();

            /**
             * Product attribute
             */
            $eavSetup->removeAttribute(Product::ENTITY, 'mp_product_seo_name');
            $eavSetup->addAttribute(Product::ENTITY, 'mp_product_seo_name', [
                'type'             => 'varchar',
                'label'            => 'H1 Heading',
                'input'            => 'text',
                'backend'          => '',
                'frontend'         => '',
                'class'            => '',
                'source'           => '',
                'global'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'group'            => 'Search Engine Optimization',
                'default'          => '',
                'apply_to'         => '',
                'sort_order'       => 5,
                'visible_on_front' => false,
                'note'             => 'Added by Mageplaza SeoRule'
            ]);

            /**
             * Category attribute
             */
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $categorySetup->removeAttribute(Category::ENTITY, 'mp_category_seo_name');
            $categorySetup->addAttribute(Category::ENTITY, 'mp_category_seo_name', [
                'label'            => 'H1 Heading',
                'type'             => 'varchar',
                'input'            => 'text',
                'backend'          => '',
                'frontend'         => '',
                'class'            => '',
                'source'           => '',
                'group'            => 'Search Engine Optimization',
                'global'           => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible'          => true,
                'required'         => false,
                'user_defined'     => false,
                'visible_on_front' => false,
                'default'          => '',
                'apply_to'         => '',
                'sort_order'       => 5,
                'note'             => 'Added by Mageplaza SeoRule'
            ]);
        }
        $setup->endSetup();
    }
}
