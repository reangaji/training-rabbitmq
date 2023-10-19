<?php

namespace Icube\TrainingRabbitMq\Model;

use Closure;
use Exception;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\CallbackInvokerInterface;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\QueueInterface;
use PhpAmqpLib\Wire\AMQPTable;

class Consumer implements ConsumerInterface
{

    public function __construct(
        CallbackInvokerInterface $invoker,
        MessageEncoder $messageEncoder,
        ConsumerConfigurationInterface $configuration,
        CommunicationConfig $communicationConfig
    ) {
        $this->invoker = $invoker;
        $this->messageEncoder = $messageEncoder;
        $this->configuration = $configuration;
        $this->communicationConfig = $communicationConfig;
    }

    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();
        $maxIdleTime = $this->configuration->getMaxIdleTime();
        $sleep = $this->configuration->getSleep();

        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke(
                $queue,
                $maxNumberOfMessages,
                $this->getTransactionCallback($queue),
                $maxIdleTime,
                $sleep
            );
        }
    }

    public function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {
            try {
                $topicName = $message->getProperties()['topic_name'];
                $allowedTopics = $this->configuration->getTopicNames();
                if (in_array($topicName, $allowedTopics)) {
                    $this->dispatchMessage($message);
                } else {
                    $queue->reject($message);
                    echo "Message has been rejected: Topic not allowed\n";
                    return;
                }
                $queue->acknowledge($message);
            } catch (Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                echo sprintf("Message has been rejected: %s\n", $e->getMessage());
            }
        };
    }

    public function dispatchMessage(EnvelopeInterface $message): void
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $message->getBody();

        if (isset($decodedMessage)) {
            foreach ($handlers as $callback) {
                call_user_func_array($callback, [$decodedMessage]);
            }
        }
    }
}