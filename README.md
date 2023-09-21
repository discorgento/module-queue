![Discorgento Queue](docs/header.png)

<p align="center">A dev-friendly approach to handle background jobs in Magento 2</p>
<p align="center">
    <a href="https://github.com/discorgento/module-queue/stargazers" target="_blank"><img alt="GitHub Stars" src="https://img.shields.io/github/stars/discorgento/module-queue?style=social"/></a>
    <a href="https://packagist.org/packages/discorgento/module-queue/stats" target="_blank"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/discorgento/module-queue"/></a>
    <a target="_blank" href="https://packagist.org/packages/discorgento/module-queue"><img src="https://img.shields.io/packagist/v/discorgento/module-queue" alt="Latest Version on Packagist"></a>
    <a target="_blank" href="https://discord.io/Discorgento"><img alt="Join our Discord" src="https://img.shields.io/discord/768653248902332428?color=%237289d9&label=Discord"/></a>
</p>

<p align="center">Our Sponsors</p>
<p align="center">
    <a href="https://www.caravelx.com/"><img src="docs/sponsors/caravelx.svg" alt="Caravel X"></a>
</p>

## Overview 💭
Now and then we need to create processes that can take some time to execute, and that doesn't necessarily need to be done in real time. Like (but not limited to) third-party integrations.

For example, let's say you need to reflect product changes made by the storekeeper through the admin panel to their PIM/ERP. You can observe the `catalog_product_save_after` event and push the changes, but this would make the "Save" admin action become a hostage of the third-party system response time, potentially making the store admin reeealy slow.

![Linear Workflow](docs/linear-workflow.png)

But fear not citizens, because [we](https://discorgento.com/discord) are here!  
![All Might laughting](docs/we-are-here.gif)

## Install 🔧
This module is compatible with both Magento 2.3 and 2.4, from PHP 7.3 to 8.1.
```
composer require discorgento/module-queue:^3 && bin/magento setup:upgrade
```

## Usage ⚙️
> 💡 **Tip:** for 2.x version please refer to the old docs [here](https://github.com/discorgento/module-queue/blob/2.0.4/README.md#usage-%EF%B8%8F). Just remember: the current version is 100% retrocompatible, so you can [upgrade](https://github.com/discorgento/module-queue/wiki/Upgrading-from-2.x) and use all the new features without breaking your existant code!

<br>

It's really simple, there's just two steps needed:
 - append a [job](https://github.com/discorgento/module-queue/wiki/Glossary) to the queue;
 - create the job class itself ([similar to Laravel](https://laravel.com/docs/9.x/queues#class-structure)).

![Async Workflow](docs/async-workflow.png)

Let's go back to the product sync example. You can now write the `catalog_product_save_after` observer like this:

```php
<?php declare(strict_types=1);

namespace YourCompany\YourModule\Observer;

use Discorgento\Queue\Api\QueueManagementInterface;
use Magento\Framework\Event;

class ProductSaveAfter implements Event\ObserverInterface
{
    private QueueManagementInterface $queueManagement;

    public function __construct(
        QueueManagementInterface $queueManagement
    ) {
        $this->queueManagement = $queueManagement;
    }

    /** @inheritDoc */
    public function execute(Event\Observer $observer) {
        // append a job to the queue so it will run in background
        $this->queueManagement->append(
            // your job class, we'll create it later
            \YourCompany\YourModule\Job\SyncProduct::class,
            // a identifier of the entity we'll be working with
            $observer->getProduct()->getId(),
            // (optional) additional data for later usage
            ['foo' => $observer->getFoo()]
        );
    }
}
```

<br>

Now create the job itself, let's say <i>app/code/YourCompany/YourModule/Job/SyncProduct.php</i>:

```php
<?php declare(strict_types=1);

namespace YourCompany\YourModule\Job;

use Discorgento\Queue\Api\JobInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use YourCompany\YourModule\Gateway\ProductSyncer;

// the job MUST implement the JobInterface
class SyncProduct implements JobInterface
{
    private ProductRepositoryInterface $productRepository;
    private ProductSyncer $productSynchronizer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSyncer $productSynchronizer
    ) {
        $this->productRepository = $productRepository;
        $this->productSynchronizer = $productSynchronizer;
    }

    /** @inheritDoc */
    public function execute($target, $additionalData)
    {
        // retrieve the target product
        $product = $this->productRepository->getById($target);

        // optional additional data usage example
        $product->setFoo($additionalData['foo'] ?? null);

        // sync it to a third-party PIM/ERP
        $response = $this->productSynchronizer->sync($product);

        // NEW!! Now you can optionally return a string as the job "result".
        // This will be shown at admin in "System->(Tools) Queue Management"
        return "Synced. ID on PIM: {$response->pim_id}";
    }
}
```

And.. that's it! In the next cron iteration (which should be within five minutes) your job will be executed without compromising the performance of the store, assuring a smooth workflow for both your clients and their customers.

> 💡 **Tip:** any async process can benefit from this approach, your creativity is the limit.

## Managing the queue 🆕
You can track the queued jobs status and their respective output with our brand new Queue Management grid, accessible through the "System->(Tools) Queue Management" menu (near to the native cache/index management entries):

![Queue Management Grid Preview](docs/admin-grid.png)
> 💡 **Tip:** for more info about all the actions available in this grid please refer to [its documentation](https://github.com/discorgento/module-queue/wiki/Managing-the-queue).

## Advanced features 🤖
Although this module was <abbr title="originally it was designed for 1.9 due to 1.x lack of a native queue feature">ported</abbr> to Magento 2 due to its simplicity, over the time it also got some really neat tricks! So if want to do more with it, don't forget to check the [official wiki](https://github.com/discorgento/module-queue/wiki).

## Notes 🗒
 - Magento can do this natively through [Message Queues](https://developer.adobe.com/commerce/php/development/components/message-queues/), but those are ridiculously verbose to use;
 - issues and PRs are welcome in this repo;
 - we want **YOU** for [our community](https://discorgento.com/discord)!
