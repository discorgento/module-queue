<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test\Unit\Helper;

use Discorgento\Queue\Api\Data\MessageInterface;
use Discorgento\Queue\Api\MessageRepositoryInterface;
use Discorgento\Queue\Helper\Data;
use Discorgento\Queue\Model\MessageFactory;
use Discorgento\Queue\Model\MessageRepository;
use Discorgento\Queue\Model\ResourceModel\Message\Collection;
use Discorgento\Queue\Model\ResourceModel\Message\CollectionFactory as MessageCollectionFactory;
use Discorgento\Queue\Test\Job;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $instance;

    /**
     * @var MessageCollectionFactory|MockObject
     */
    private $messageCollectionFactoryMock;

    /**
     * @var MessageFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var MessageRepositoryInterface|MockObject
     */
    private $messageRepositoryMock;

    /**
     * @var MessageInterface|MockObject
     */
    private $messageMock;

    /**
     * @var Collection|MockObject
     */
    private $messageCollectionMock;

    protected function setUp(): void
    {
        $this->messageCollectionFactoryMock = $this->createMock(MessageCollectionFactory::class);
        $this->messageCollectionMock = $this->createMock(Collection::class);
        $this->messageFactoryMock = $this->createMock(MessageFactory::class);
        $this->messageRepositoryMock = $this->createMock(MessageRepository::class);
        $this->messageMock = $this->getMockBuilder(MessageInterface::class)
            ->addMethods(['getJobClass', 'getTarget', 'addData'])
            ->getMockForAbstractClass();

        $this->instance = new Data(
            $this->messageCollectionFactoryMock,
            $this->messageFactoryMock,
            $this->messageRepositoryMock
        );
    }

    /**
     * Test append jobs
     *
     * @dataProvider jobClassesDataProvider
     * @param string $jobClass
     * @param mixed $target
     * @param array $additionalData
     * @param string $additionalDataEncoded
     * @return void
     */
    public function testAppendJobs(
        string $jobClass,
        $target,
        array $additionalData,
        string $additionalDataEncoded
    ): void {
        $messageQtyFound = 0;
        $this->messageFactoryMocks($jobClass, $target, $additionalData);
        $this->messageCollectionMocks($jobClass, $target, $additionalData, $additionalDataEncoded, $messageQtyFound);

        $this->messageRepositoryMock->expects(static::once())
            ->method('save')
            ->with($this->messageMock);

        $this->instance->append($jobClass, $target, $additionalData);
    }

    /**
     * Test append jobs already queued
     *
     * @dataProvider jobClassesDataProvider
     * @param string $jobClass
     * @param mixed $target
     * @param array $additionalData
     * @param string $additionalDataEncoded
     * @return void
     */
    public function testAppendJobsAlreadyQueued(
        string $jobClass,
        $target,
        array $additionalData,
        string $additionalDataEncoded
    ): void {
        $messageQtyFound = 1;
        $this->messageFactoryMocks($jobClass, $target, $additionalData);
        $this->messageCollectionMocks($jobClass, $target, $additionalData, $additionalDataEncoded, $messageQtyFound);

        $this->instance->append($jobClass, $target, $additionalData);
    }

    /**
     * Mocks related with message factory
     *
     * @param string $jobClass
     * @param $target
     * @param array $additionalData
     * @return void
     */
    private function messageFactoryMocks(string $jobClass, $target, array $additionalData): void
    {
        $this->messageFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->messageMock);

        $this->messageMock->expects(static::once())
            ->method('addData')
            ->with(['job_class' => $jobClass, 'target' => $target])
            ->willReturnSelf();

        $this->messageMock->expects(static::once())
            ->method('setAdditionalData')
            ->with($additionalData)
            ->willReturnSelf();
    }

    /**
     * Mocks related with message collection
     *
     * @param string $jobClass
     * @param $target
     * @param array $additionalData
     * @param string $additionalDataEncoded
     * @param int $messageQtyFound
     * @return void
     */
    private function messageCollectionMocks(
        string $jobClass,
        $target,
        array $additionalData,
        string $additionalDataEncoded,
        int $messageQtyFound
    ): void {
        $this->messageMock->expects(static::once())
            ->method('getAdditionalData')
            ->willReturn($additionalData);

        $this->messageCollectionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->messageCollectionMock);

        $this->messageMock->expects(static::once())
            ->method('getJobClass')
            ->willReturn($jobClass);

        $this->messageMock->expects(static::once())
            ->method('getTarget')
            ->willReturn($target);

        $this->messageCollectionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->messageCollectionMock);

        $this->messageCollectionMock->expects(static::exactly(3))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['job_class', $jobClass],
                ['target', $target],
                ['additional_data', $additionalDataEncoded]
            )
            ->willReturnSelf();

        $this->messageCollectionMock->expects(static::once())
            ->method('count')
            ->willReturn($messageQtyFound);
    }

    /**
     * Provide information to append on message queue
     *
     * @return string[][]
     */
    public function jobClassesDataProvider(): array
    {
        return [
            [Job::class, 32, ['foo1' => 'bar1'], '{"foo1":"bar1"}'],
            [Job::class, 43, ['foo2' => 'bar2'], '{"foo2":"bar2"}'],
            [Job::class, null, [], '[]'],
            [Job::class, 64, ['foo4' => 'bar4'], '{"foo4":"bar4"}'],
            [Job::class, null, [], '[]'],
            [Job::class, 643, ['foo6' => 'bar6'], '{"foo6":"bar6"}']
        ];
    }
}
