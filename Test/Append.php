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
        $this->helper->append(
            Job::class,
            1,
            ['lorem' => 'ipsum']
        );
    }
}
