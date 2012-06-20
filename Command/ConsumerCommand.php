<?php
namespace Overblog\ActiveMqBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of ProducerCommand
 *
 * @author Xavier HAUSHERR
 */
class ConsumerCommand extends ContainerAwareCommand
{
    protected function configure()
	{
        $this->setName('activemq:consumer')
             ->setDescription('Consumer a message from a given queue.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
	{
        $stomp = new \Stomp('tcp://localhost:61613');
        $stomp->subscribe('/queue/hub/xavier');

        while(true)
        {
            if($stomp->hasFrame())
            {
                $frame = $stomp->readFrame();

                var_dump($frame);

                $stomp->ack($frame);
            }
        }
    }
}