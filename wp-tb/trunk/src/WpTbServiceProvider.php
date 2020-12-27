<?php declare(strict_types=1);

namespace Pollen\WpTb;

use Pollen\WpTb\Contracts\WpTbContract;
use tiFy\Container\ServiceProvider;

class WpTbServiceProvider extends ServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * {@internal Permet le chargement différé des services qualifié.}
     * @var string[]
     */
    protected $provides = [
        WpTbContract::class,
    ];

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        events()->listen('wp.booted', function () {
            $this->getContainer()->get(WpTbContract::class)->boot();
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share(WpTbContract::class, function () {
            return new WpTb(config('wp-tb', []), $this->getContainer());
        });
    }
}