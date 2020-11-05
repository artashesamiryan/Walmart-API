<?php

namespace App\Repositories\Listing;

interface ListingRepositoryInterface
{
    public function get();

    public function push();

    public function inventoryPricePush();

    public function storeProducts($items);

    public function receive($token, $cursor = null, $wt);
}
