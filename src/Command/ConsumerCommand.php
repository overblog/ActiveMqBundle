<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\Command;

use InvalidArgumentException;
use Overblog\ActiveMqBundle\ActiveMq\Consumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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

    public function addConsumer(string $name, Consumer $consumer): void
    {
        $this->consumers[$name] = $consumer;
    }

    protected function configure(): void
    {
        $this->setName('activemq:consumer')
            ->setDescription('Consume a message from a given queue.');

        $this->addArgument('name', InputArgument::REQUIRED, 'Consumer name');
        $this->addOption('messages', 'm', InputOption::VALUE_REQUIRED, 'Messages to consume', 0);
        $this->addOption('route', 'r', InputOption::VALUE_OPTIONAL, 'Routing Key', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        if (!isset($this->consumers[$name])) {
            throw new InvalidArgumentException(sprintf('Consumer "%s" not found', $name));
        }
        $consumer = $this->consumers[$name];

        $consumer->setRoutingKey($input->getOption('route'));
        $consumer->consume($input->getOption('messages'));

        return self::SUCCESS;
    }
}
