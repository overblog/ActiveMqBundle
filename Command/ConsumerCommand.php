<?php
namespace Overblog\ActiveMqBundle\Command;

use Overblog\ActiveMqBundle\ActiveMq\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ProducerCommand
 *
 * @author Xavier HAUSHERR
 */
class ConsumerCommand extends Command
{
    /**
     * @var Consumer[]
     */
    private $consumers;

    public function __construct(array $consumers = [])
    {
        $this->consumers = $consumers;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('activemq:consumer')
            ->setDescription('Consume a message from a given queue.');

        $this->addArgument('name', InputArgument::REQUIRED, 'Consumer name');
        $this->addOption('messages', 'm', InputOption::VALUE_REQUIRED, 'Messages to consume', 0);
        $this->addOption('route', 'r', InputOption::VALUE_OPTIONAL, 'Routing Key', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consumer = $this->getContainer()
            ->get(
                sprintf(
                    'overblog_active_mq.consumer.%s',
                    $input->getArgument('name')
                )
            );

        $consumer->setRoutingKey($input->getOption('route'));
        $consumer->consume($input->getOption('messages'));
    }
}
