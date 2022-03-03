<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Queue\Test;

use Discorgento\Queue\Helper\Data as Helper;

class Append
{
    protected Helper $helper;

    public function __construct(
        Helper $helper
    ) {
        $this->helper = $helper;
    }

    public function append()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->helper->append(Job::class, $i);
        }
    }
}
