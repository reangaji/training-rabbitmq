<?php
namespace Icube\TrainingRabbitMq\Observer;

class BackendAuthObserver implements \Magento\Framework\Event\ObserverInterface
{
	public function __construct
	(
		\Icube\TrainingRabbitMq\Model\MessageQueues\Training\Publisher $publisher
	)
	{
		$this->publisher = $publisher;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $user = $observer->getEvent()->getUser();

        $message = [
        	'user_data' => $user->getData(),
        	'message' => 'success login'
        ];

        $this->publisher->execute(json_encode($message));
        return $this;
    }
}