<?php

namespace Brianpando\Plantumlgen;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

class PlantController extends Controller
{

    public function index($timezone)
    {
        echo Carbon::now($timezone)->toDateTimeString();
    }

}