<?php

declare(strict_types=1);

namespace Overblog\ActiveMqBundle\Command;

use Exception;
use InvalidArgumentException;
use Overblog\ActiveMqBundle\ActiveMq\Message;
use Overblog\ActiveMqBundle\ActiveMq\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Description of ProducerCommand
 *
 * @author Xavier HAUSHERR
 */
class ProducerCommand extends Command
{
    /**
     * @var Publisher[]
     */
    private $publishers;

    public function addPublisher(string $name, Publisher $consumer): void
    {
        $this->publishers[$name] = $consumer;
    }

    protected function configure(): void
    {
        $this->setName('activemq:producer')
            ->setDescription('Send a message to a given queue.');

        $this->addArgument('name', InputArgument::REQUIRED, 'Producer name')
            ->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Message to send')
            ->addOption('serializer', 'z', InputOption::VALUE_REQUIRED, 'Serialize message (serialize, json)')
            ->addOption('route', 'r', InputOption::VALUE_OPTIONAL, 'Routing Key', '');
    }

    /**
     * Interaction with console
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($helpers = $this->getHelperSet()) {
            /** @var QuestionHelper $questionHelper */
            $questionHelper = $helpers->get('question');

            if (!$input->getOption('message')) {
                $input->setOption(
                    'message',
                    $questionHelper->ask(
                        $input,
                        $output,
                        new Question('<comment>Enter the message to send: </comment>')
                    )
                );
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $time_start = microtime(true);

        $message = $input->getOption('message');

        if (empty($message)) {
            $output->writeln('<error>Message cannot be empty</error>');

            return 1;
        }

        $name = $input->getArgument('name');
        if (!isset($this->publishers[$name])) {
            throw new InvalidArgumentException(sprintf('Publisher "%s" not found', $name));
        }
        $publisher = $this->publishers[$name];

        // Serializer
        switch ($input->getOption('serializer')) {
            case 'serialize':
                $message = serialize($message);
                break;

            case 'json':
                $message = json_encode($message);
                break;
        }

        // Send message
        try {
            $msg = new Message($message);

            $publisher->publish($msg, $input->getOption('route'));
            $output->writeln(
                sprintf(
                    '<info>Message has been sent in %s ms</info>',
                    round(microtime(true) - $time_start, 3)
                )
            );
        } catch (Exception $e) {
            $output->writeln(
                sprintf(
                    '<error>Error while sending message: %s</error>',
                    $e->getMessage()
                )
            );

            return 1;
        }

        return 0;
    }
}
