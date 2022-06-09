<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test;

use Discorgento\Queue\Api\JobInterface;

class Job implements JobInterface
{
    /** @inheritDoc */
    public function execute($target, $additionalData)
    {
        sleep(1);

        // 50% chance of random failure
        if (random_int(1, 2) == 2) {
            throw new \Exception('Failure simulation XD');
        }

        return 'Test successful :D';
    }
}
