<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test;

use Discorgento\Queue\Api\JobInterface;

class Job implements JobInterface
{
    /** @inheritDoc */
    public function execute($target, $additionalData)
    {
        echo var_dump($target, $additionalData);
    }
}
