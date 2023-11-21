<?php
namespace App\Services;


class Sms{

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
}