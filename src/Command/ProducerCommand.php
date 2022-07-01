<?php
namespace Overblog\ActiveMqBundle\Command;

use Overblog\ActiveMqBundle\ActiveMq\Message;
use Overblog\ActiveMqBundle\ActiveMq\Publisher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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

    public function addPublisher($name, Publisher $consumer)
    {
        $this->publishers[$name] = $consumer;
    }


    protected function configure()
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
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if(!$input->getOption('message'))
        {
            $input->setOption(
                'message',
                $questionHelper->ask(
                    $input,
                    $output,
                    new Question('<comment>Enter the message to send: </comment>')
                ));
        }
    }

    /**
     * Send the message
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time_start = microtime(true);

        $message = $input->getOption('message');

        if(empty($message))
        {
            $output->writeln('<error>Message cannot be empty</error>');
            return;
        }

        $name = $input->getArgument('name');
        if (!isset($this->publishers[$name])) {
            throw new \InvalidArgumentException(sprintf('Publisher "%s" not found', $name));
        }
        $publisher = $this->publishers[$name];

        // Serializer
        switch($input->getOption('serializer'))
        {
            case 'serialize':
                $message = serialize($message);
                break;

            case 'json':
                $message = json_encode($message);
                break;
        }

        // Send message
        try
        {
            $msg = new Message($message);

            $publisher->publish($msg, $input->getOption('route'));
            $output->writeln(
                sprintf(
                    '<info>Message has been sent in %s ms</info>',
                    round(microtime(true) - $time_start, 3)
                )
            );
        }
        catch(\Exception $e)
        {
            $output->writeln(
                sprintf(
                    '<error>Error while sending message: %s</error>',
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Replace standard question helper
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        $questionHelper = $this->getHelperSet()->get('question');

        //@todo Update the Deploy project wich have a strong dependance with this bundle
        if (!$questionHelper /*|| get_class($questionHelper) !== 'Overblog\DeployBundle\Command\Helper\DialogHelper'*/) {
            $this->getHelperSet()->set($questionHelper = new QuestionHelper());
        }

        return $questionHelper;
    }
}
