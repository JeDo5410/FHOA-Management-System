<?php

namespace App\Http\Controllers;

use App\Models\ConstructionPermit;
use App\Models\PermitType;
use App\Models\StatusType;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConstructionPermitController extends Controller
{
    public function index()
    {
        $permitTypes = PermitType::orderBy('typecode')->get();

        return view('construction-permit.construction-permit', compact('permitTypes'));
    }
}