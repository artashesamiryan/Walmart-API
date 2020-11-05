<?php

namespace App\Repositories\Fitment;

class FitmentRepository
{
    public function get()
    {
        return response()->json(['message' => 'Fitments here']);
    }
}
