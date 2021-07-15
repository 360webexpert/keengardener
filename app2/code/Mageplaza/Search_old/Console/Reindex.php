<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Search
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Search\Console;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Reindex
 * @package Mageplaza\Search\Console
 */
class Reindex extends Command
{
    /**
     * @var AppState
     */
    protected $appState;

    /**
     * Reindex constructor.
     *
     * @param AppState $appState
     */
    public function __construct(AppState $appState)
    {
        $this->appState = $appState;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('mpsearch:reindex')
            ->setDescription('Mageplaza Search Data Reindex');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        $objectManager = ObjectManager::getInstance();
        $helperData = $objectManager->create('Mageplaza\Search\Helper\Data');

        $errs = $helperData->createJsonFile();
        if (!empty($errs)) {
            foreach ($errs as $err) {
                $output->writeln('<error>' . $err . '</error>');
            }
        } else {
            $output->writeln('Search data reindex successfully!');
        }
    }
}
