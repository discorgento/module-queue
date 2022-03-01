# Discorgento Queue
A dev-friendly approach to handle background jobs in Magento 2

## Overview
Now and then we need to execute processes that can take some time to execute, and that doesn't necessarily need to be done in real time. Like (but not limited to) third-party integrations.

For example, let's say you need to reflect product changes made by the storekeeper through the admin panel to their PIM/ERP. You can observe the `catalog_product_save_after` event and push the changes, but this would make the "Save" admin action become a hostage of the third-party system response time, potentially making the store admin reeealy slow.

But fear not citizens, because [we](https://discord.gg/UddsfAbc9V) are here!  
![All Might laughting](docs/we_are_here.gif)

## Installation
```sh
composer require discorgento/module-queue
bin/magento setup:upgrade
```

## Usage
There's just two steps needed: 1) append a job to the queue, 2) create the job class itself (like the Laravel ones).

Let's go back to the product sync example. You can now write the `catalog_product_save_after` event observer like this:

```php
class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $queueHelper;

    public function __construct(
        \Discorgento\Queue\Helper\Data $queueHelper
    ) {
        $this->queueHelper = $queueHelper;
    }

    /** @inheritDoc */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /**
         * Just append a job to the queue so it will run later in background,
         * without affecting the product save performance at all 😎
         */
        $this->queueHelper->append(
            \YourCompany\YourModule\Jobs\SyncProduct::class,
            $observer->getProduct()->getId(),
            ['foo' => $observer->getFoo()] // additional data that can be used later (optional)
        );
    }
}
```

Now, create the job itself, like lets say _app/code/YourCompany/YourModule/Jobs/SyncProduct.php_:

```php
// the job should implent the JobInterface
class SyncProduct implements \Discorgento\Queue\Api\JobInterface
{
    protected $productRepository;
    protected $productSynchronizer;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \YourCompany\YourModule\Helper\Sync\Product $productSynchronizer
    ) {
        $this->productRepository = $productRepository;
        $this->productSynchronizer = $productSynchronizer;
    }

    /**
     * @param $target The product id
     * @param $additionalData Optional extra data inserted on append
     * @inheritDoc
     */
    public function execute($target, $additionalData)
    {
        // retrieve the product and sync it
        $product = $this->productRepository->getById($target);
        $this->productSynchronizer->sync($product);
    }
}
```

And.. that's it! In the next cron iteration (which should be in the next minute) your job will be executed without comprimsing the performance of the main process.

Any async/background/integration/lazy process can benefit from this approach, your creativity is the limit.

## Footer notes
 - magento can do this natively through Message Queues, but those are ridiculously verbose to use;
 - issues and PRs are welcome in this repo;
 - **YOU** are welcome on [our community](https://discord.gg/UddsfAbc9V) 😉

## Roadmap
 - [ ] add a safety lock to prevent jobs from overflowing each other;
 - [ ] add an option on admin allowing to choose between cron and rabbitmq backend;
