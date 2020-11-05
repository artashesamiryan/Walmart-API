<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Fitment\FitmentRepository;
use App\Repositories\Fitment\FitmentRepositoryInterface;
use App\Repositories\Listing\ListingRepository;
use App\Repositories\Listing\ListingRepositoryInterface;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $repositories = [
        CategoryRepositoryInterface::class => CategoryRepository::class,
        FitmentRepositoryInterface::class  => FitmentRepository::class,
        ListingRepositoryInterface::class  => ListingRepository::class,
        OrderRepositoryInterface::class    => OrderRepository::class
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->repositories as $interface => $repository) {
            $this->app->bind($interface, $repository);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
