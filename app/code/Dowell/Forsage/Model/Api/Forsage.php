<?php

namespace Dowell\Forsage\Model\Api;


class Forsage
{


    protected $key;
    protected $url_api = 'https://forsage-studio.com/api/';


    /*function __construct($key)
    {

        return $this
            ->setKeyForsage($key);
    }*/


    public function getKeyForsage()
    {
        return $this->key;
    }

    public function setKeyForsage($key)
    {
        $this->key = $key;
        return $this;
    }


    protected function request($action, $post = [],$_get=[])
    {

        $key = $this->getKeyForsage();

        $url = $this->url_api . $action . '?token=' . $key;
        if (count($_get)>0){

//            echo '<pre>';
//            print_r($_get);
//echo '</pre>';
//die();
            foreach ($_get as $_key=>$_value) {
                $url .= '&' . $_key . '=' . $_value;//implode('&', $get);
            }
        }
        //echo ' url '.$url."<br/>\r\n\r\n";

        if ($ch = curl_init($url)) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            //$header=['Authorization'=> 'Bearer '.$key];
            //curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookie.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookie.txt');
            //curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $password);


            //{ "Authorization": "Bearer {your_api_token}" }
            if (count($post) > 0) {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                $postdata = json_encode($post);

                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            } else curl_setopt($ch, CURLOPT_POST, 0);

            $res = curl_exec($ch);
            curl_close($ch);
            $res1 = json_decode($res);
            return $res1;
        }
        return false;

    }

    public function getChanges($type='full',$period=false,$period_finish=false)
    {
        $action = 'get_changes';
        $post=[];
        if ($period){
            $post = [
                'start_date' => strtotime($period),//"-5 week"),
            ];
        }else {
            $post = [
                'start_date' => strtotime("-5 week"),
            ];
        }
        if ($type!='')
            $post['products'] = $type;

        if ($period_finish){
            $post['end_date'] =  strtotime($period_finish);//

        }
        $result = $this->request($action,[],$post);
        return $result;
    }

    public function getProducts($period=false,$period_finish=false, $stock=false)
    {
        $action = 'get_products';
        $post=[];
        if ($period){
            $post['start_date'] =  strtotime($period);//,//"-5 week"),

        }else {
            $post['start_date'] =  strtotime("-5 week");//
            /*$post = [
                'start_date' => strtotime("-5 week"),
            ];*/
        }
        if ($stock){
            $post['stock'] = $stock;
        }
        if ($period_finish){
            $post['end_date'] =  strtotime($period_finish);//

        }
        $result = $this->request($action,[],$post);
        return $result;
    }



    public function getRefbookCharacteristics()
    {
        $action = 'get_refbook_characteristics';
        $result = $this->request($action);
        return $result;
    }
    public function getBrands()
    {
        $action = 'get_brands';
        $result = $this->request($action);
        return $result;
    }

    public function getSuppliers()
    {
        $action = 'get_suppliers';
        $result = $this->request($action);
        return $result;
    }

    public function GetProductsBySupplier($supplier_id, $params=['quantity'=>1])
    {
        $action = 'get_products_by_supplier/' . $supplier_id;
        $result = $this->request($action,[],$params);
        return $result;

    }

    public function GetProduct($product_id, $params=['watermark'=>'bottom'])
    {
        $action = 'get_product/' . $product_id;
        $result = $this->request($action,[],$params);
        return $result;
    }
}