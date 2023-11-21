<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test\Integration\Model;

use Discorgento\Queue\Command\ClearCommand;
use Discorgento\Queue\Model\Message;
use Discorgento\Queue\Model\MessageFactory;
use Discorgento\Queue\Model\QueueManagement;
use Discorgento\Queue\Model\ResourceModel\Message\Collection as MessageCollection;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class QueueManagementTest extends TestCase
{
    /** @var ClearCommand */
    private $clearCommand;

    /** @var QueueManagement */
    private $queueManagement;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var MessageCollectionFactory */
    private $messageCollectionFactory;

    /** @var SerializerInterface */
    private $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $di = Bootstrap::getObjectManager();

        $this->clearCommand = $di->get(ClearCommand::class);
        $this->queueManagement = $di->get(QueueManagement::class);
        $this->messageFactory = $di->get(MessageFactory::class);
        $this->messageCollectionFactory = $di->get(MessageCollectionFactory::class);
        $this->serializer = $di->get(SerializerInterface::class);
    }

    /**
     * Make sure there's no residual test message
     *
     * @return void
     */
    public function testClearQueue()
    {
        $this->clearCommand->execute();
        $this->assertEquals(0, $this->getMessages()->getSize());
    }

    /**
     * Test message queueing
     *
     * @depends testClearQueue
     *
     * @return void
     */
    public function testAppendJob()
    {
        $message = $this->buildDummyMessage();
        $this->queueManagement->append(
            $message->getJob(),
            $message->getTarget(),
            $message->getAdditionalData(),
        );

        $this->assertTrue($this->queueManagement->alreadyQueued($message));
    }

    /**
     * Test message queueing to a specific group
     *
     * @depends testClearQueue
     *
     * @return void
     */
    public function testAppendJobToGroup()
    {
        $testGroup = 'test';

        $message = $this->buildDummyMessage()->setGroup($testGroup);
        $this->assertEquals($testGroup, $message->getGroup());

        $this->queueManagement->appendToGroup(
            $message->getGroup(),
            $message->getJob(),
            $message->getTarget(),
            $message->getAdditionalData(),
        );

        $this->assertTrue($this->queueManagement->alreadyQueued($message));
    }

    /**
     * Make sure the same message is not queued twice
     *
     * @depends testClearQueue
     *
     * @return void
     */
    public function testDuplicatedMessagesPrevention()
    {
        $message = $this->buildDummyMessage();

        for ($i = 0; $i < 2; $i++) {
            $this->queueManagement->append(
                $message->getJob(),
                $message->getTarget(),
                $message->getAdditionalData(),
            );
        }

        $encodedAdditionalData = $this->serializer->serialize($message->getAdditionalData());

        $messagesCount = $this->getMessages()
            ->addFieldToFilter(Message::FIELD_GROUP, $message->getGroup())
            ->addFieldToFilter(Message::FIELD_JOB, $message->getJob())
            ->addFieldToFilter(Message::FIELD_TARGET, $message->getTarget())
            ->addFieldToFilter(Message::FIELD_ADDITIONAL_DATA, $encodedAdditionalData)
            ->getSize();

        $this->assertEquals(1, $messagesCount);
    }

    /**
     * Dummy message builder
     *
     * @return Message
     */
    private function buildDummyMessage() : Message
    {
        return $this->messageFactory->create()
            ->setJob(DummyJob::class)
            ->setTarget('123')
            ->setAdditionalData(['foo' => 'bar']);
    }

    /**
     * Get current messages collection
     *
     * @return MessageCollection
     */
    private function getMessages() : MessageCollection
    {
        return $this->messageCollectionFactory->create();
    }
}
