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

namespace Mageplaza\SeoRule\Console;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\SeoRule\Model\Rule\Source\Type;
use Mageplaza\SeoRule\Model\RuleFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApplyRule
 * @package Mageplaza\SeoRule\Console
 */
class ApplyRule extends Command
{
    /**
     * Name of input option
     */
    const INPUT_KEY_RULE_ID = 'id';

    /**
     * @var RuleFactory
     */
    protected $seoRuleFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * ApplyRule constructor.
     *
     * @param RuleFactory $seoRuleFactory
     * @param State $state
     * @param null $name
     */
    public function __construct(
        RuleFactory $seoRuleFactory,
        State $state,
        $name = null
    ) {
        parent::__construct($name);

        $this->seoRuleFactory = $seoRuleFactory;
        $this->state          = $state;
    }

    /**
     * Config command line
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_RULE_ID,
                null,
                InputOption::VALUE_REQUIRED,
                'Apply rule id'
            )
        ];
        $this->setName('mageplaza:applyrule')
            ->setDescription('Apply rule')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return $this|int|null
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('adminhtml');
        if ($id = $input->getOption(self::INPUT_KEY_RULE_ID)) {
            $ruleModel = $this->seoRuleFactory->create()->load($id);
            if (!$ruleModel->getRuleId()) {
                $output->writeln('Not found. Please check this rule again!');

                return $this;
            }
            if ($ruleModel->getEntityType() == Type::LAYERED_NAVIGATION) {
                $output->writeln('Cannot apply this rule with Layered Navigation.');

                return $this;
            }
            try {
                $this->getHelperData()->applyRuleId($ruleModel);
                $output->writeln('Rule id has been applied.');
            } catch (Exception $e) {
                $output->writeln('Cannot apply rule!');
            }
        } else {
            $result = $this->getHelperData()->applyRules();
            $output->writeln("Rule id " . implode(',', $result) . " has been applied.");
        }

        return $this;
    }

    /**
     * Get helper data
     * @return mixed
     */
    public function getHelperData()
    {
        $objectManager = ObjectManager::getInstance();

        return $objectManager->get('Mageplaza\SeoRule\Helper\Data');
    }
}
