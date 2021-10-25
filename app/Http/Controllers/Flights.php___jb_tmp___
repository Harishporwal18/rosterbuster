<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Http\Request;

class Flights extends Controller
{
    /**
     * Show the profile for a given user.
     *
     *
     * @return array[]
     */
    protected  $flightsdata;
    public function index()
    {
        try {
             Artisan::call('flight:models');
        } catch (ArtisanCommandException $e) {
            Log::error($e);
        }
    }
}
