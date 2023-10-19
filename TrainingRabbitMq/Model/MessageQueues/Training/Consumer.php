<?php declare(strict_types=1);

namespace Icube\TrainingRabbitMq\Model\MessageQueues\Training;

class Consumer
{
     /**
     * Consumer constructor.
     */
     public function __construct()
     {

     }

     public function processMessage(string $_data)
     {

        // do something with message queue data

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Consumer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('consumer : '.$_data);

        echo $_data."\n";
     }

     public function processMessageCustom(string $_data)
     {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ConsumerCustom.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('consumer : '.$_data);

        echo $_data."\n";
     }
}
