<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Ripoo\OdooClient;
use App\Models\Atendance;
use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;
use Carbon\Carbon;

class Attendance
{

    const  URL = "https://owia.odoo.com";
    const  DB = 'odoo-ps-psbe-sun-electronics-main-1649702';
    const USERNAME = 'staff.attendance';
    const  PASSWORD = 'h15E-ff47S!-6c561261a';

    // const  URL = "https://owia-14-0-staging-4241215.dev.odoo.com";
    // const  DB = 'owia-14-0-staging-4241215';
    // const USERNAME = 'testing@attendance';
    // const  PASSWORD = 'e61f96c561261a4da20443b81d2b620962683828c';



    static public function client()
    {
        return new OdooClient(self::URL, self::DB, self::USERNAME, self::PASSWORD);
    }


    static protected function odologin()
    {
        $connexion = new Client(self::URL . "/xmlrpc/2/common");
        $response = $connexion->send(new Request('login', array(new Value(self::DB), new Value(self::USERNAME), new Value(self::PASSWORD))));
        $connexion->setSSLVerifyPeer(0);
        return $response;
    }





    static public function updateAttendance($ids, $check_out)
    {

        $c_response = self::odologin();


        //   dd($ids[0]);
        if ($c_response->errno != 0) {
            echo  '<p>error : ' . $c_response->faultString() . '</p>';
        } else {
            $uid = $c_response->value()->scalarval();

            $val = array(
                "check_out"    => new Value($check_out),
            );
            $id_list = array();
            $id_list[] = new Value($ids[0], 'int');
            $client = new Client(self::URL . "/xmlrpc/2/object");
            $client->setSSLVerifyPeer(0);


            $response = $client->send(new Request('execute', array(
                new Value(self::DB),
                new Value($uid),
                new Value(self::PASSWORD),
                new Value("hr.attendance"),
                new Value("write"),
                new Value($id_list, "array"),
                new Value($val, "struct"),

            )));

            if ($response->errno != 0) {
                return true;
            }
        }
    }






    // create customer
    static public function createAtt($data)
    {


        $c_response = self::odologin();

        if ($c_response->errno != 0) {
            echo  '<p>error : ' . $c_response->faultString() . '</p>';
        } else {
            $uid = $c_response->value()->scalarval();

            $val = array(
                "check_in"    => new Value($data["check_in"]),
                "employee_id"    => new Value($data["employee_id"]),

            );

            $client = new Client(self::URL . "/xmlrpc/2/object");
            $client->setSSLVerifyPeer(0);


            $response = $client->send(new Request('execute', array(
                new Value(self::DB),
                new Value($uid),
                new Value(self::PASSWORD),
                new Value("hr.attendance"),
                new Value("create"),
                new Value($val, "struct"),

            )));

            if ($response->errno != 0) {
                return $response->errstr;
            } else {
                return $response;
            }
        }
    }

    static public function clockOutAttendance($data, $phone)
    {
        $ids = self::client()->search('hr.attendance', [['x_phone', '=', $phone]], 0, 1);

        self::client()->write('hr.attendance', $ids, $data);
    }


    static public function allAttendance()
    {

        $ids = self::client()->search('hr.attendance', [['check_in', '!=', '2022-03-10 07:36:55']], 0, 100);
        $fields = ['check_in', 'check_out', 'employee_id', 'create_date', 'x_job_id', 'x_checkid', 'x_company', 'display_name'];
        $customers = self::client()->read('hr.attendance', $ids, $fields);
        dd($customers);
    }


    static public function getAtendances($check_in)
    {

        $ids = self::client()->search('hr.attendance', [['check_in', '=', $check_in]], 0, 1);

        $fields = ['employee_id', 'id', 'check_in', 'check_out'];
        $att = self::client()->read('hr.attendance', $ids, $fields);
        return $att;
    }


    static public function getAtendance($PersonName)
    {
        $todays = date("Y-m-d");
        $employee = self::getemployee($PersonName);
        if (count($employee) > 0) {
            $ids = self::client()->search('hr.attendance', [['employee_id', '=', $employee[0]['id']], ['x_check_in_date', '=', $todays]], 0, 1);

            if (count($ids) > 0) {
                $fields = ['employee_id', 'id', 'check_in', 'check_out', 'x_check_in_date'];
                $att = self::client()->read('hr.attendance', $ids, $fields);

                if ($att[0]['check_out'] == false) {
                    // $te ="";

                    $newDateTime = Carbon::create($att[0]['check_in'])->addHours(1)->toDateTimeString();



                    // self::updateAttendance($ids, $newDateTime);
                    return $att;
                } else {
                    return $att;
                }
            } else {
                $ids = self::client()->search('hr.attendance', [['employee_id', '=', $employee[0]['id']]], 0, 1);
                $fields = ['employee_id', 'id', 'check_in', 'check_out', 'x_check_in_date'];
                $att = self::client()->read('hr.attendance', $ids, $fields);
                if ($att[0]['check_out'] == false) {

                    $newDateTime = Carbon::create($att[0]['check_in'])->addHours(8)->toDateTimeString();

                    self::updateAttendance($ids, $newDateTime);
                } else {
                    return $att;
                }
            }
        } else {
            return 0;
        }
    }


    // checkin attendances
    static public function ClockInAttendance($PersonName, $check_out, $data)
    {
// return $data;
        $todays = date("Y-m-d");
        $employee = self::getemployee($PersonName);
        if (count($employee) > 0) {
            $ids = self::client()->search('hr.attendance', [['employee_id', '=', $employee[0]['id']], ['x_check_in_date', '=', $todays]], 0, 1);
            if (count($ids) > 0) {
                $fields = ['employee_id', 'id', 'check_in', 'check_out', 'x_check_in_date'];
                $att = self::client()->read('hr.attendance', $ids, $fields);
                if ($check_out !== '') {
                    if ($att[0]['check_out'] == false) {
                        self::updateAttendance($ids, $check_out);
                        return 1;
                    } else {
                        self::updateAttendance($ids, $check_out);
                        return 1;
                    }
                }else{
                    // info($att[0]['check_in'] );
                    if($att[0]['check_in'] > $data['check_in']){
                        $dataem1 = DB::table('attendances')->where('CheckIn', '=', $data['check_in'])->update(['CheckIn' => $att[0]['check_in'], 'status' => 'odooCheckIn']);
                        return 1;
                    }else{
                    self::updateAttendance($ids, $data['check_in']);
                    return 1;
                    }
                }
            } else {
                $ids = self::client()->search('hr.attendance', [['employee_id', '=', $employee[0]['id']]], 0, 1);
                $fields = ['employee_id', 'id', 'check_in', 'check_out', 'x_check_in_date'];
                $att = self::client()->read('hr.attendance', $ids, $fields);
                if ($att[0]['check_out'] == false) {

                    $newDateTime = Carbon::create($att[0]['check_in'])->addHours(8)->toDateTimeString();

                    self::updateAttendance($ids, $newDateTime);
                    return 0;
                } else {
                    // dd($data);
                    $datas = [
                        'check_in' => $data['check_in'],
                        'employee_id' => $employee[0]['id']
                    ];
                    self::client()->create('hr.attendance', $datas);
                    return 1;
                }
            }
            return 1;
        } else {
            return 0;
        }
    }



    static public function getemployee($name)
    {

        $ids = self::client()->search('hr.employee.public', [['name', '=', $name]], 0, 1);
        $fields = ['name', 'id'];
        return  self::client()->read('hr.employee.public', $ids, $fields);
    }

    static public function getemployeeall()
    {

        $ids = self::client()->search('hr.employee.public', [['name', '!=', "name"]], 0, 1);
        $fields = ['name', 'id'];
        $employee = self::client()->read('hr.employee.public', $ids, $fields);
        return $employee;
    }

    static public function checkInAttendance($data)
    {


        $id = self::client()->create('hr.attendance', $data);

        return $id;
    }




    static public function checkOutAttendance($check_in, $PersonName, $data)
    {
        $ids = self::client()->search('hr.attendance', [['check_in', '=', $check_in], ['display_name', '=', $PersonName]], 0, 1);

        self::client()->write('hr.attendance', $ids, $data);
    }



// get employee fields
    static public function employeeFields()
    {

        $data =  self::client()->fields_get('hr.employee.public', array(), array('attributes' => array('string', 'help', 'type')));
        return $data;
    }
    // get attendance fields
    static public function attFields()
    {

        $data =  self::client()->fields_get('hr.attendance', array(), array('attributes' => array('string', 'help', 'type')));
        return $data;
    }





    // go
    static public function CheckIntest()
    {
        $todays = date("Y-m-d");
        $data = DB::table('table1')->whereDate('AccessDate', '=', $todays)->where('attendance', 'checkin')->orderBy('AccessDateandTime', 'asc')->get();
        foreach ($data as $d) {

            if ($d->PersonName) {

                $dataem = DB::table('attendances')->WhereDate('CheckInDate', '=', $todays)->where('PersonName', $d->PersonName)->first();

                if ($dataem !== null) {

                    if ($dataem->CheckOut !== null) {
                        if ($dataem->status == 'odoocheckout') {
                            $dataem1 = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $dataem->EmployeeID)->update(['CheckOutDate' => $d->AccessDate, 'CheckOut' => $d->AccessDateandTime, 'CheckOutTime' => $d->AccessTime, 'status' => 'checkin']);
                            DB::table('table1')->where('EmployeeID', $d->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                        }else{
                            $dataem1 = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $dataem->EmployeeID)->update(['CheckOutDate' => $d->AccessDate, 'CheckOut' => $d->AccessDateandTime, 'CheckOutTime' => $d->AccessTime, 'status' => 'checkin']);
                            DB::table('table1')->where('EmployeeID', $d->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                        }
                    } else {

                        if ($d->AccessTime !== $dataem->CheckInTime) {
                            $dataem1 = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $dataem->EmployeeID)->update(['CheckOutDate' => $d->AccessDate, 'CheckOut' => $d->AccessDateandTime, 'CheckOutTime' => $d->AccessTime, 'status' => 'checkin']);
                            DB::table('table1')->where('EmployeeID', $d->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                        }else{
                            DB::table('table1')->where('EmployeeID', $d->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                        }
                    }
                } else {

                    $dataem12 = DB::table('attendances')->insert(['EmployeeID' => $d->EmployeeID, 'PersonName' => $d->PersonName, 'CheckInDate' => $d->AccessDate, 'CheckIn' => $d->AccessDateandTime, 'CheckInTime' => $d->AccessTime, 'status' => 'checkin']);
                    DB::table('table1')->where('EmployeeID', $d->EmployeeID)->update(['attendance' => "odooCheckIn"]);
                }
            }
        }
    }



    // checkout

    static public function ClockIn()
    {
        $todays = date("Y-m-d");

        $data = DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('status', 'checkin')->get();

        foreach ($data as $d) {

            if ($d->CheckOut == null) {
// dd($d);
                $data = [
                    'check_in' => $d->CheckIn,

                ];

                $employee =    self::ClockInAttendance($d->PersonName, '', $data);

                if ($employee > 0) {
                    DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $d->EmployeeID)->update(['status' => "odoocheckin"]);
                }
            } else {
                $data = [
                    'check_in' => $d->CheckIn,

                ];
                $employee =     self::ClockInAttendance($d->PersonName, $d->CheckOut, $data);
                if ($employee > 0) {
                    DB::table('attendances')->whereDate('CheckInDate', '=', $todays)->where('EmployeeID', $d->EmployeeID)->update(['status' => "odoocheckout"]);
                }
            }
        }
    }





}
