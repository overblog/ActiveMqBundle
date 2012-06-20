<?php
namespace Overblog\ActiveMqBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Overblog\ActiveMqBundle\ActiveMq\Message;

/**
 * Description of ProducerCommand
 *
 * @author Xavier HAUSHERR
 */
class ProducerCommand extends ContainerAwareCommand
{
    protected function configure()
	{
        $this->setName('activemq:producer')
             ->setDescription('Send a message to a given queue.');

        $this->addArgument('name', InputArgument::REQUIRED, 'Queue name');
        $this->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Message to send');
        $this->addOption('serializer', 'z', InputOption::VALUE_REQUIRED, 'Serialize message (serialize, json)');
    }

    /**
     * Interaction with console
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        $input->setOption(
                'message',
                $dialog->ask($output, "<comment>Enter the message to send: </comment>"
            ));
    }

    /**
     * Send the message
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
	{
        $message = $input->getOption('message');

        if(empty($message))
        {
            $output->writeln('<error>Message cannot be empty</error>');
            return;
        }

        $publisher = $this->getContainer()->get('overblog_active_mq.publisher');

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
            $publisher->publish(
                    $input->getArgument('name'),
                    new Message($message)
                );
            $output->writeln('<info>Message has been sent</info>');
        }
        catch(\Exception $e)
        {
            $output->writeln('<error>Error while sending message</error>');
        }
    }

    /**
     * Replace standard dialog helper
     * @return DialogHelper
     */
    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Overblog\DeployBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}

