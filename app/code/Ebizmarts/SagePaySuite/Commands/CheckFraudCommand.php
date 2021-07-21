<?php

namespace Ebizmarts\SagePaySuite\Commands;

use Ebizmarts\SagePaySuite\Model\Cron;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class CheckFraudCommand extends Command
{
    /** @var Cron $posOrder*/
    private $cron;

    /** @var State  */
    protected $appState;

    /**
     * CheckFraudCommand constructor.
     * @param Cron $cron
     * @param State $appState
     */
    public function __construct(
        Cron $cron,
        State $appState
    ) {
        $this->appState = $appState;
        $this->cron     = $cron;
        parent::__construct();
    }
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('sage-pay:fraud-check');
        $this->setDescription('Opayo check fraud on transactions.');
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(AppArea::AREA_ADMINHTML);

        $output->writeln("<comment>Checking fraud...</comment>");

        $this->cron->checkFraud();

        $output->writeln("<info>Done.</info>");
    }
}
