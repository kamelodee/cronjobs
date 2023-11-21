<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Odoo;
use App\Services\Acumatica;

use Teckwei1993\Otp\Rules\OtpValidate;
use Illuminate\Support\Facades\DB;

class OdooController extends Controller
{
    public $alldata = [];

   


    public function getFields()
    {
        return Odoo::employeeFields();
    }
    public function getAllSaleOrders()
    {
        return Odoo::getAllSaleOrders();
    }

    public function acumatica(Request $request)
    {

       

        $jayParsedAry = [
          "OrderType" => [
                "value" => "SO" 
             ], 
          "OrderNbr" => [
                   "value" => "<NEW>" 
                ], 
          "Hold" => [
                      "value" => true 
                   ], 
          "CustomerID" => [
                         "value" => "0248907440" 
                      ], 
          "Description" => [
                            "value" => "TEST-API- 003" 
                         ], 
          "CustomerOrder" => [
                               "value" => "VUSTOM11002w" 
                            ], 
          "Details" => [
                                  [
                                     "InventoryID" => [
                                        "value" => "32 INCH AMCON BRACKET" 
                                     ], 
                                     "OrderQty" => [
                                           "value" => "2" 
                                        ], 
                                     "SalespersonID" => [
                                              "value" => "GHACC0001" 
                                           ], 
                                     "WarehouseID" => [
                                                 "value" => "ACHIWARE" 
                                              ], 
                                     "LocationID" => [
                                                    "value" => "MAIN" 
                                                 ], 
                                     "LotSerialNbr" => [
                                                       "value" => "" 
                                                    ], 
                                     "Qty" => [
                                                          "value" => "2" 
                                                       ] 
                                  ] 
                               ] 
       ]; 
        
        
       $data=[
        'customer_name'=>"kamilo",
        'customerPhone'=>"o248907440",
        'customerLocation'=>"accra",
        'postData'=>$jayParsedAry
    
    ];
        return Acumatica::acumatica($data);
    }





    static public function ClockIn()
    {
        $todays = date("Y-m-d");

        $data = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('status', 'checkin')->get();

        foreach ($data as $d) {
            $employee = Odoo::getemployee($d->PersonName);


            if (count($employee) > 0) {


                if ($d->CheckOut == null) {

                    $data = [
                        'check_in' => $d->CheckIn,
                        'employee_id' => $employee[0]['id']
                    ];
                    Odoo::checkInAttendance($data);


                    DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $d->EmployeeID)->update(['status' => "odoocheckin"]);
                } elseif ($d->status == 'checkin') {
                    $data = [
                        'check_in' => $d->CheckIn,
                        'employee_id' => $employee[0]['id']
                    ];
                    Odoo::checkInAttendance($data);
                    DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $d->EmployeeID)->update(['status' => "odoocheckin"]);
                }
            }
        }
    }





    static public function ClockOut()
    {
        $todays = date("Y-m-d");

        $data = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('status', 'odoocheckout')->get();
// dd($data);
        foreach ($data as $d) {

            if ($d->CheckOut !== null) {

              $data =[
                'check_out' => $d->CheckOut
              ];
            //   dd($d);
              Odoo::updateAttendance($d->CheckIn,$d->PersonName,$d->CheckOut);
                // Odoo::checkOutAttendance($d->CheckIn, $d->PersonName, $data);
                DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $d->EmployeeID)->update(['status' => "odoocheckout"]);
            }
        }
    }






    public function index()
    {
        $todays = date("Y-m-d");
        $data = DB::table('table1')->whereDate('AccessDate', '=', $todays)->get();

        foreach ($data as $d) {
            info($d);
            $datas = DB::table('table1')->whereDate('AccessDate', '=', $todays)->where('PersonName', $d->PersonName)->where('attendance', 'checkin')->orderBy('AccessDateandTime', 'DESC')->get();
            array_push($this->alldata, $datas);
        }
        // dd($this->alldata);
        foreach ($this->alldata as $d) {
            $employee = Odoo::getemployee($d[0]->PersonName);
            if (count($employee) > 0) {
                $data = [
                    'check_in' => $d[0]->AccessDateandTime,
                    'employee_id' => $employee[0]['id']
                ];
                $id =  Odoo::checkInAttendance($data);
                if ($id) {
                    $datas = DB::table('table1')->where('EmployeeID', $d[0]->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                }
            }
        }
    }

    public function indexcheckout()
    {
        $todays = date("Y-m-d");

        $data = DB::table('table1')->whereDate('AccessDate', '=', $todays)->get();

        foreach ($data as $d) {
            $datas = DB::table('table1')->whereDate('AccessDate', '=', $todays)->where('attendance', 'odooCheckIn')->orderBy('AccessDateandTime', 'DESC')->get();
            array_push($this->alldata, $datas);
        }
        foreach ($this->alldata as $d) {
            //    dd($d);
            if ($d[0]->attendance == "odooCheckIn") {
                $ids = Odoo::getAtendances($d[0]->AccessDateandTime);
                $dat = DB::table('table1')->whereDate('AccessDate', '=', $todays)->where('PersonName', $d[0]->PersonName)->orderBy('AccessDateandTime', 'DESC')->first();
                // dd($dat);
                $data = [
                    'check_out' => $dat->AccessDateandTime
                ];

                Odoo::checkOutAttendance([$ids[0]['id']], $data);
            }
        }
    }




    public function getEmployee()
    {

        $employee = Odoo::getemployee('Christian Klonzia');
        // return $employee[0]['id'];
        $data = [
            'check_in' => '2022-02-25 07:48:51',
            'employee_id' => $employee[0]['id']
        ];
        Odoo::checkInAttendance($data);
    }


    public function getAttendance()
    {

        $ids = Odoo::getAtendances("4513");
        $data = [
            'check_out' => '2022-02-24 14:48:51'
        ];
        Odoo::checkOurAttendance([$ids[0]['id']], $data);
    }




    public static function pingAfriq($phone, $message)
    {
        $phone = preg_replace('/\D+/', '', $phone);
        $name = "CediPay";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://mysms.pingafrik.com/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => array('key' => 'IYJdg', 'secret' => '8e4Sb8rBV2ih', 'contacts' => $phone, 'sender_id' => $name, 'message' => $message),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
    }
    public function getfieldss()
    {
        $customer = Odoo::attFields();

        return $customer;
    }
}
