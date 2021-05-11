<?php

declare(strict_types=1);

namespace Pollen\WpTb;

use Pollen\Container\BootableServiceProvider;

class WpTbServiceProvider extends BootableServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        WpTbInterface::class,
    ];

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(WpTbInterface::class, function () {
            return new WpTb([], $this->getContainer());
        });
    }
}