<?php

namespace App\Http\Controllers;

use App\WeekValidation;
use Illuminate\Http\Request;
use App\Repositories\WeekValidationRepository;
use App\Http\Controllers\Master\MasterController;

class WeekValidationController extends MasterController
{
    public function __construct(WeekValidation $weekValidation)
    {
         parent::__construct(new WeekValidationRepository($weekValidation));
    }
}
