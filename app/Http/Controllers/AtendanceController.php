<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Atendance;
use Illuminate\Support\Facades\DB;
class AtendanceController extends Controller
{
 
    public function index(){
        $data = DB::table('Test')->get();
        dd($data);
    }


}