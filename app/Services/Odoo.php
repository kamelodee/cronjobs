<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Ripoo\OdooClient;
use App\Models\Atendance;
use App\Models\AcumaticaOrder;
use App\Models\AcumaticaOrderCustomer;
use App\Models\AcumaticaOrderItem;
use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;
use Carbon\Carbon;
use App\Exceptions\InvalidOrderException;
use Ripoo\Exception\{CodingException, ResponseException, ResponseEntryException, ResponseFaultException, ResponseStatusException};

class Odoo
{

    const  URL = "https://owia-16pure2-10192457.dev.odoo.com";
    const  DB = 'owia-16pure2-10192457';
    const USERNAME = 'acumaticaluser@hisense.com.gh';
    const  PASSWORD = '0f23316f9bf8418adb8f77406db63f9f246e945f';
    // const  URL = "https://owia.odoo.com";
    // const  DB = 'odoo-ps-psbe-sun-electronics-main-1649702';
    // const USERNAME = 'staff.attendance';
    // const  PASSWORD = 'h15E-ff47S!-6c561261a';

    // const  URL = "https://owia-14-0-staging-4241215.dev.odoo.com";
    // const  DB = 'owia-14-0-staging-4241215';
    // const USERNAME = 'testing@attendance';
    // const  PASSWORD = 'e61f96c561261a4da20443b81d2b620962683828c';



    static public function client()
    {
        return new OdooClient(self::URL, self::DB, self::USERNAME, self::PASSWORD);
    }


   
    static public function getCustomer($id)
    {

        $ids = self::client()->search('res.partner', [['id', '=', $id]], 0, 1);
        $fields = ['name', 'id','phone_sanitized','contact_address_complete'];
        return  self::client()->read('res.partner', $ids, $fields);
    }

    static public function getProduct($id)
    {

        $ids = self::client()->search('sale.order.line', [['order_id', '=', $id]], 0, 1);
        $fields = ['name', 'id','product_qty','qty_to_deliver','product_packaging_qty','display_name','price_total','product_template_id','order_id'];
        return  self::client()->read('sale.order.line', $ids, $fields);
    }

    static public function getAllSaleOrders()
    {
        // AcumaticaOrder::truncate();
        // AcumaticaOrderCustomer::truncate();;
        // AcumaticaOrderItem::truncate();
        // dd('ooops');
        $ids = self::client()->search('sale.order', [['website_id', '!=', false,],['state', '=', 'sale',],['x_studio_acumatica_update', '=', false,]], 0, 1);
        $fields = ['website_order_line','x_studio_acumatica_update','id','sale_order_option_ids', 'id','delivery_status','cart_quantity','picking_policy','partner_id','date_order','delivery_count','effective_date','payment_term_id','cart_quantity','type_name','name','display_name','amount_total','state','client_order_ref','origin','reference','require_payment','order_line','warehouse_id','website_id'];
        $order = self::client()->read('sale.order', $ids, $fields);
    //    dd($order[0]);
        $customer= self::getCustomer($order[0]['partner_id'][0]);
       $product= self::getProduct($order[0]['id']);
    //    dd($product[0]['product_qty']);
        // dd( [$order,$customer,$product]);
        $data=[
            "website_order_line" => $order[0]['website_order_line'][0],
            "delivery_status" => $order[0]['delivery_status'],
            "cart_quantity" => $order[0]['cart_quantity'],
            "picking_policy" => $order[0]['picking_policy'],
            "partner_id" => $order[0]['partner_id'][0],
            "date_order" => $order[0]['date_order'],
            "delivery_count" => $order[0]['delivery_count'],
            "type_name" => $order[0]['type_name'],
            "name" => $order[0]['name'],
            "display_name" => $order[0]['display_name'],
            "amount_total" => $order[0]['amount_total'],
            "state" => $order[0]['state'],
            "order_line" => $order[0]['order_line'][0],
            "warehouse_id" => $order[0]['warehouse_id'][0],
            "warehouse" => $order[0]['warehouse_id'][1],
            "website_id" => $order[0]['website_id'][0],
            "order_id" => $order[0]['id'],
            "x_studio_acumatica_update" => false,
        ];
        $dataCustomer=[
           
            "partner_id" => $customer[0]['id'],
            "phone_sanitized" => $customer[0]['phone_sanitized'],
            "contact_address_complete" => $customer[0]['contact_address_complete'],
            "name" => $customer[0]['name'],
            
        ];
        $dataItem=[
           
      
            "product_qty" => $product[0]['product_qty'],
            "qty_to_deliver" => $product[0]['qty_to_deliver'],
            "product_packaging_qty" => $product[0]['product_packaging_qty'],
            "price_total" => $product[0]['price_total'],
            "product_template_id" => $product[0]['product_template_id'][0],
            "product_template_name" => $product[0]['product_template_id'][1],
            "order_id" => $product[0]['order_id'][0],
            "name" => $product[0]['name'],
            
        ];
        $datas = [
            'x_studio_acumatica_update' => true,
            
        ];
       
       $orders= AcumaticaOrder::updateOrCreate($data);
       $customer= AcumaticaOrderCustomer::updateOrCreate($dataCustomer);
       $product= AcumaticaOrderItem::updateOrCreate($dataItem);
    //    dd($orders,$customer,$product);
       self::client()->write('sale.order', $ids, $datas);
        return "success";

    }
   
  
    


    






// get employee fields
    static public function employeeFields()
    {

        $data =  self::client()->fields_get('sale.order', array(), array('attributes' => array('string', 'help', 'type')));
        return $data;
    }
    // get attendance fields
    static public function attFields()
    {

        $data =  self::client()->fields_get('hr.attendance', array(), array('attributes' => array('string', 'help', 'type')));
        return $data;
    }





    

   

   


}
