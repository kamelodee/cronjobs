<?php 

namespace App\Services;
use App\Models\Product;
use App\Services\Odoo;
use App\Models\AcumaticaOrder;
use App\Models\AcumaticaOrderCustomer;
use Illuminate\Support\Facades\DB;
use App\Models\AcumaticaOrderItem;
class Acumatica
{

    //const  BASEURL = "https://erp.sel.com.gh/AcumaticaHisense/entity/";
    const  BASEURL  = "https://erptest.sel.com.gh/AcumaticaHisense2023R1/entity/";
    const  NAME     = "ricky";
	const  PASSWORD = "Rick2023R1";
    const  COMPANY  = "Hisense2016";





    public static function acumatica(){
        // $data = Odoo::getAllSaleOrders();

        $order=  DB::table('acumatica_orders')
            ->select('acumatica_orders.partner_id','acumatica_orders.id','acumatica_orders.website_order_line','acumatica_orders.x_studio_acumatica_update','acumatica_orders.warehouse','acumatica_orders.website_id','acumatica_orders.order_line','acumatica_orders.amount_total','acumatica_orders.warehouse_id','acumatica_orders.delivery_status','acumatica_orders.cart_quantity','acumatica_orders.date_order','acumatica_orders.delivery_count','acumatica_orders.state','acumatica_orders.type_name','acumatica_orders.name','acumatica_orders.display_name','acumatica_orders.order_id')
            ->where('x_studio_acumatica_update', false)
            
            ->first();

            // dd($order);
        $customers=  DB::table('acumatica_order_customers')
            ->select('acumatica_order_customers.partner_id','acumatica_order_customers.name','acumatica_order_customers.phone_sanitized','acumatica_order_customers.contact_address_complete')
         
            ->where('partner_id', $order->partner_id)
            ->first();

            // dd($customer);

        $order_line=  DB::table('acumatica_order_items')
            ->select('acumatica_order_items.product_qty','acumatica_order_items.name','acumatica_order_items.qty_to_deliver','acumatica_order_items.product_packaging_qty','acumatica_order_items.price_total','acumatica_order_items.product_template_name','acumatica_order_items.order_id')
         
            ->where('order_id', $order->order_id)
            ->first();

            // dd($customers,$order,$order_line);
        $cookie= self::loginForCookies();
        // dd($cookie);
        if(count($cookie)>3){

          
        //   $data = Odoo::getAllSaleOrders();
          $customer= self::CreateNewCustomer($customers->name,$customers->phone_sanitized,$customers->contact_address_complete,$cookie);
           $saleOrder=  self::SalesOrder( $order,$order_line, $customers,$cookie);
           AcumaticaOrder::find($order->id)->update(['x_studio_acumatica_update'=>true]);
           $log= self::logout($cookie);
        //    return $saleOrder;
        }
       
      
        $log= self::logout($cookie);
        return $cookie;
       
    }



   public static function loginForCookies(){

        $postData = [
            "name"     => self::NAME,
            "password" => self::PASSWORD,
            "company"  => self::COMPANY,
        ];

        // Initialize cURL object 
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, self::BASEURL."auth/login");
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curlObj, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($curlObj, CURLOPT_HTTPHEADER,  array(
            "Content-Type: application/json"
        ));
        curl_setopt($curlObj, CURLOPT_HEADER, 1);
        curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($curlObj);
        // Matching the response to extract cookie value 
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi',$result,$match_found);
        $cookies = array();
        foreach ($match_found[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        // Closing curl object instance 
        curl_close($curlObj); 

        //echo  $result;

        return $cookies;
    }

    public static function logout($cookie){


        $postData = [
              "name"     => self::NAME,
              "password" => self::PASSWORD,
              "company"  => self::COMPANY,
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => self::BASEURL.'auth/logout',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($postData),
       CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: .ASPXAUTH='.$cookie["_ASPXAUTH"].';ASP.NET_SessionId='.$cookie["ASP_NET_SessionId"].';CompanyID=Hisense2016;Cookie_1=value;Locale='.$cookie["Locale"].';TimeZone='.$cookie["TimeZone"].'; UserBranch='.$cookie["UserBranch"].';requestid='.$cookie["requestid"].';requeststat='.$cookie["requeststat"].''
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;


    }
    

    public static function InventorySummary($itemID, $warehouseID, $cookie){

            $postData = [
                'InventoryID' => ["value" => $itemID],
                'WarehouseID' => ["value" => $warehouseID],
                'LocationID'  => ["value" => "MAIN",],
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => self::BASEURL.'queue/20.200.001/InventorySummaryInquiry',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER =>  array(
             'Content-Type: application/json',
             'Cookie: UserBranch='.$cookie["UserBranch"].'; CompanyID='.self::COMPANY.'; ASP.NET_SessionId='.@$cookie["ASP_NET_SessionId"].'; .ASPXAUTH='.@$cookie["ASPXAUTH"].'; Locale=TimeZone=GMTE0000U&Culture=en-US'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
                                    
            $result =  json_decode($response,true);

            return !empty($result['Qty']['value'])?$result['Qty']['value']:null;

    }


    public static function SearchCustomer($customerPhone,$cookie){ 

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => self::BASEURL.'queue/20.200.001/customer/'.$customerPhone,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',   
            'Cookie: UserBranch='.$cookie["UserBranch"].'; CompanyID='.self::COMPANY.'; ASP.NET_SessionId='.$cookie["ASP_NET_SessionId"].'; .ASPXAUTH='.$cookie["ASPXAUTH"].'; Locale=TimeZone=GMTE0000U&Culture=en-US'
        ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);

        //echo $response;

        $result =  json_decode($response,true);

        return !empty($result['CustomerName']['value'])?$result['CustomerName']['value']:null;
    }



// create customer  object
    public static function CreateNewCustomer($name,$phone_sanitized,$contact_address_complete,$cookie){

      
// dd($cookie["_ASPXAUTH"],$cookie["ASP_NET_SessionId"]);
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://erptest.sel.com.gh/AcumaticaHisense2023R1/entity/queueV2/20.200.001/customer',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS =>'{
          "CustomerID" : {"value" : "'.$phone_sanitized.'" } ,
          "CustomerName" : {"value" : "'.$name.'" },
          "Terms" :{
              "value" : "COD"
          },
            
          "MainContact" : 
            {
              "Address" : 
                {
                  "AddressLine1" : {"value" : "'.$contact_address_complete.'" },
                  "City" : {"value" : "Accra" }
                }
            }  
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: .ASPXAUTH='.$cookie["_ASPXAUTH"].';ASP.NET_SessionId='.$cookie["ASP_NET_SessionId"].';CompanyID=Hisense2016;Cookie_1=value;Locale='.$cookie["Locale"].';TimeZone='.$cookie["TimeZone"].'; UserBranch='.$cookie["UserBranch"].';requestid='.$cookie["requestid"].';requeststat='.$cookie["requeststat"].''
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        

        $result =  json_decode($response,true);

        return $result;
    }


    // create sale order
public static function SalesOrder($order,$order_line, $customer,$cookie){
                 
    //    dd($order,$order_line, $customer);
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://erptest.sel.com.gh/AcumaticaHisense2023R1/entity/queueV2/20.200.001/SalesOrder',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS =>'{
    "OrderType": {
        "value": "SO"
    },
    "OrderNbr": {
        "value": "'.$order->name.'"
    },
    "Hold": {
        "value": true
    },
    "CustomerID": {
        "value": "'.$customer->phone_sanitized.'"
    },
    "Description": {
        "value": "TEST-API- 005"
    },
    "CustomerOrder": {
        "value": "'.$order->name.'"
    },
    "PaymentRef": {
        "value": "CASH"
    },
    "ExternalRef": {
        "value": "CASH"
    },
    "CashAccount": {
        "value": "'.$order_line->price_total.'"
    },
    "Details": [
        {
            "InventoryID": {
                "value": "'.$order_line->product_template_name.'"
            },
            "AlternateID": {
                "value": ""
            },
            "OrderQty": {
                "value": '.$order_line->product_qty.'
            },
            "SalespersonID": {
                "value": "GHACC0304"
            },
            "WarehouseID": {
                "value": "EASTLSWARE"
            },
            "LocationID": {
                "value": "MAIN"
            },
            "LotSerialNbr": {
                "value": "AA0000000A8"
            },
            "UOM": {
                "value": "EACH"
            }
            
        },
        
            
        }
    ]
}',
CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Cookie: .ASPXAUTH='.$cookie["_ASPXAUTH"].';ASP.NET_SessionId='.$cookie["ASP_NET_SessionId"].';CompanyID=Hisense2016;Cookie_1=value;Locale='.$cookie["Locale"].';TimeZone='.$cookie["TimeZone"].'; UserBranch='.$cookie["UserBranch"].';requestid='.$cookie["requestid"].';requeststat='.$cookie["requeststat"].''
  ),
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;
$result =  json_decode($response,true);


return $result;
    }


    public static function SerialLookUp($serialNumber,$cookie){

		$postData = ['LotSerialNbr' => ['value' => $serialNumber] ];
		
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => self::BASEURL.'queue/20.200.001/GetSerialLot?$expand=Details',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS =>json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: UserBranch='.$cookie["UserBranch"].'; CompanyID='.self::COMPANY.'; ASP.NET_SessionId='.$cookie["ASP_NET_SessionId"].'; .ASPXAUTH='.$cookie["ASPXAUTH"].'; Locale=TimeZone=GMTE0000U&Culture=en-US'
        ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;

        $result =  json_decode($response,true);
		        
        if(!empty($result['Details'][0]['CustomerID']['value'])){

            $data['customer_phone'] = $result['Details'][0]['CustomerID']['value'];
            $data['customer_name']  = $result['Details'][0]['CustomerName']['value'];
            $data['product_name']   = $result['Details'][0]['Description']['value'];
            $data['product_serial'] = $result['Details'][0]['LotSerialNbr']['value'];
            $data['order_no']       = $result['Details'][0]['OrderNbr']['value'];

            return $data;

        }else{
            return null;
        }
    }
	
    public static function GetItem($itemID, $cookie){

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => self::BASEURL.'queue/20.200.001/StockItem/'.rawurlencode($itemID),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: UserBranch='.$cookie["UserBranch"].'; CompanyID='.self::COMPANY.'; ASP.NET_SessionId='.@$cookie["ASP_NET_SessionId"].'; .ASPXAUTH='.@$cookie["ASPXAUTH"].'; Locale=TimeZone=GMTE0000U&Culture=en-US'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $result =  json_decode($response,true);

            return !empty($result['LotSerialClass']['value'])?$result['LotSerialClass']['value']:null;
        }


	public static function SalesData($CustomerPhone,$PurchaseNumber,$SalespersonID, $WarehouseID, $PaymentRef, $CashAccount ,$customerProducts,$cookie){  
        
        
        foreach($customerProducts as $item){
			
		  $lot = self::GetItem($item->ItemID, $cookie);
			
          //$product = Product::where(["BarCode" => $item->BarCode])->first();
          //$lot = $product->Lot;

          if($lot == "DEFAULT" || $lot != "SERIAL"){

            $details[] = [
                'InventoryID'   => ['value'  => "$item->ItemID"],
                'OrderQty'      => ['value'  => "$item->Qty"],
                'SalespersonID' => ['value'  => "$SalespersonID"],
                'WarehouseID'   => ['value'  => "$WarehouseID"],

                'LocationID'   => ['value'  => "MAIN"],
                'LotSerialNbr' => ['value'  => ""],
                'Qty'          => ['value'  => "1"],
            ];

          }else{

            $serialNumbers = explode("***",$item->SerialNumber);

            foreach($serialNumbers as $key=> $value){

                $Allocations[] = [
                    'InventoryID'  => ['value' => "$item->ItemID"],
                    'LocationID'   => ['value' =>'MAIN'],
                    'LotSerialNbr' => ['value' => $value],
                    'Qty'          => ['value' => '1'],
                    'UOM'          => ['value' => 'EACH'],
                ];
            }
            
            $details[] = [
                'InventoryID'   => ['value' => "$item->ItemID"],
                'OrderQty'      => ['value'  => "$item->Qty"],
                'SalespersonID' => ['value'  => "$SalespersonID"],
                'WarehouseID'   => ['value'  => "$WarehouseID"],
                'Allocations'   => $Allocations
            ];

            unset($Allocations);
         }

         }
         

        $array1 =  [
            'OrderType' => [
              'value' => 'CS',
            ],
            'OrderNbr' => [
              'value' => '<NEW>',
            ],
            'Hold' => [
              'value' => true,
            ],
            'CustomerID' => [
              'value' => "$CustomerPhone",
            ],
            'Description' => [
              'value' => "$PurchaseNumber",
            ],
            'CustomerOrder' => [
              'value' => "$PurchaseNumber",
            ],
            'PaymentRef' => [
                'value' => "$PaymentRef",
            ],
			'ExternalRef' => [
                'value' => "$PaymentRef",
            ],
            'CashAccount' => [
                'value' => "$CashAccount",
            ],
            'Details' => $details,
        ];


        return $array1;
    }
	
	
	
/*
    public static function SalesData($CustomerPhone,$PurchaseNumber,$SalespersonID, $WarehouseID, $PaymentRef, $CashAccount ,$customerProducts){  
        
        
        foreach($customerProducts as $item){

          $serialNumbers = explode("***",$item->SerialNumber);

          foreach($serialNumbers as $key=> $value){

            $Allocations[] = [
                'InventoryID'  => ['value' => "$item->ItemID"],
                'LocationID'   => ['value' =>'MAIN'],
                'LotSerialNbr' => ['value' => $value],
                'Qty'          => ['value' => '1'],
                'UOM'          => ['value' => 'EACH'],
            ];
          }
          
          $details[] = [
              'InventoryID'   => ['value' => "$item->ItemID"],
              'OrderQty'      => ['value'  => "$item->Qty"],
              'SalespersonID' => ['value'  => "$SalespersonID"],
              'WarehouseID'   => ['value'  => "$WarehouseID"],
              'Allocations' => $Allocations
           ];

           unset($Allocations);

         }


        $array1 =  [
            'OrderType' => [
              'value' => 'CS',
            ],
            'OrderNbr' => [
              'value' => '<NEW>',
            ],
            'Hold' => [
              'value' => true,
            ],
            'CustomerID' => [
              'value' => "$CustomerPhone",
            ],
            'Description' => [
              'value' => 'TEST-API- 003',
            ],
            'CustomerOrder' => [
              'value' => "$PurchaseNumber",
            ],
            'PaymentRef' => [
                'value' => "$PaymentRef",
            ],
			'ExternalRef' => [
                'value' => "$PaymentRef",
            ],
            'CashAccount' => [
                'value' => "$CashAccount",
            ],
            'Details' => $details,
        ];


        return $array1;
    }
*/



static public function createCustomer($CustomerPhone,$CustomerName,$address,$city,$cookie){


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => self::BASEURL.'queueV2/20.200.001/customer',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'PUT',
  CURLOPT_POSTFIELDS =>'{
  "CustomerID" : {"value" : '.$CustomerPhone.' } ,
  "CustomerName" : {"value" : '.$CustomerName.' },
  "Terms" :{
      "value" : "COD"
  },
    
  "MainContact" : 
    {
      "Address" : 
        {
          "AddressLine1" : {"value" : '.$address.' },
          "City" : {"value" : '.$city.' }
        }
    }  
}',
CURLOPT_HTTPHEADER => array(
    'Cookie: UserBranch='.$cookie["UserBranch"].'; CompanyID='.self::COMPANY.'; ASP.NET_SessionId='.@$cookie["ASP_NET_SessionId"].'; .ASPXAUTH='.@$cookie["ASPXAUTH"].'; Locale=TimeZone=GMTE0000U&Culture=en-US'
),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

}
}