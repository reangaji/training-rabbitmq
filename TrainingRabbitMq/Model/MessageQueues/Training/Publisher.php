<?php declare(strict_types=1);

namespace Icube\TrainingRabbitMq\Model\MessageQueues\Training;
use Magento\Framework\MessageQueue\PublisherInterface;

class Publisher
{
     const TOPIC_NAME = 'icube.training.rabbitmq';

     /**
      * @var \Magento\Framework\MessageQueue\PublisherInterface
      */
     private $publisher;

     /**
     * Publisher constructor.
     * @param Publisher $publisher
     */
     public function __construct
     (
         PublisherInterface $publisher
     )
     {
         $this->publisher = $publisher;
     }

     /**
     * @param data
     */
     public function execute(string $_data)
     {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Consumer.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('publisher : '.$_data);
        
        $this->publisher->publish(self::TOPIC_NAME, $_data);
     }
}
