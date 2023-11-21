<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test\Integration\Legacy;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Helper\Data as Helper;
use Discorgento\Queue\Model\MessageFactory;
use Discorgento\Queue\Model\QueueManagement;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    /** @var Helper */
    private $queueHelper;

    /** @var QueueManagement */
    private $queueManagement;

    /** @var MessageFactory */
    private $messageFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $di = Bootstrap::getObjectManager();

        $this->queueHelper = $di->get(Helper::class);
        $this->queueManagement = $di->get(QueueManagement::class);
        $this->messageFactory = $di->get(MessageFactory::class);
    }

    /**
     * Make sure the legacy helper is still working for retrocompatibility
     *
     * @return void
     */
    public function testLegacyHelper()
    {
        $message = $this->buildDummyMessage();

        $this->queueHelper->append(
            $message->getJob(),
            $message->getTarget(),
            $message->getAdditionalData()
        );

        $this->queueManagement->alreadyQueued($message);
    }

    /**
     * Build a dummy message
     *
     * @return MessageInterface
     */
    private function buildDummyMessage(): MessageInterface
    {
        return $this->messageFactory->create()
            ->setJob(DummyJob::class)
            ->setTarget('test')
            ->setAdditionalData(['test' => 'test']);
    }
}
