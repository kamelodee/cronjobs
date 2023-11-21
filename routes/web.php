<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AtendanceController;
use App\Http\Controllers\OdooController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/checkInAttendance', [OdooController::class, 'checkInAttendance']);
Route::get('/odoo/fields', [OdooController::class, 'checkOdoofields']);
Route::get('/atendance', [OdooController::class, 'index']);
Route::get('/createAtendance', [OdooController::class, 'createAtendance']);
Route::get('/atendance/all', [OdooController::class, 'searchOdoo']);
Route::get('/search', [OdooController::class, 'odooSearch']);
Route::get('/employee', [OdooController::class, 'getEmployee']);
Route::get('/getAttendance', [OdooController::class, 'getAttendance']);
Route::get('/getfieldss', [OdooController::class, 'getfieldss']);
Route::get('/newlog', [OdooController::class, 'newlog']);
Route::get('/acumatica', [OdooController::class, 'acumatica']);
Route::get('/getFields', [OdooController::class, 'getFields']);
Route::get('/orders', [OdooController::class, 'getAllSaleOrders']);
Route::get('/indexcheckout', [OdooController::class, 'ClockIn']);
Route::get('/clockout', [OdooController::class, 'ClockOut']);
Route::get('/datas', [AtendanceController::class, 'index']);
Route::get('/', function () {
    return view('welcome');
});
