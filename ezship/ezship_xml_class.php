<?php
/**
 * ezship xml 串接類. [傳送訂單XML的參數]
 * 參考官方API技術文件 https://www.ezship.com.tw/file/ezship_WebOrder_XML.pdf
 *
 * 詳細教學位置 [待補]
 *
 * @author      Aidec Li (https://blog.aidec.tw)
 * @copyright   Copyright (c) 2017 aidec.tw
 * @version     1.0
 */ 

class ezShip
{
    
	/*
        串接到ezShip #XML版
        以POST傳遞，但參數須以網址 ?web_map_xml 方式傳遞，否則會出現傳遞的電文為空。 [GET模式?真奇怪!]
    */
    public function send($setting=array())
    {
        $storeData = array();
        //ezShip 帳號
        $ezShipAccount = (isset($setting['suID'])) ? $setting['suID'] : '';
        //訂單ID
        $orderID = (isset($setting['orderID'])) ? $setting['orderID'] : ''; 
        //訂單狀態
        $orderStatus = (isset($setting['orderStatus'])) ? $setting['orderStatus'] : '';
        //訂單類別 1:取貨付款[需開通商務帳號] 3:取貨不付款
        $orderType = (isset($setting['orderType'])) ? $setting['orderType'] : ''; 
        //訂單金額
        $orderAmount = (isset($setting['orderAmount'])) ? $setting['orderAmount'] : '';
        //取件人
        $rvName = (isset($setting['rvName'])) ? $setting['rvName'] : '';
        //取件人電子郵件
        $rvEmail = (isset($setting['rvEmail'])) ? $setting['rvEmail'] : '';
        //取件人行動電話
        $rvMobile = (isset($setting['rvMobile'])) ? $setting['rvMobile'] : '';
        //門市通路別
        $stCate = (isset($setting['stCate'])) ? $setting['stCate'] : '';
        //門市代號
        $stCode= (isset($setting['stCode'])) ? $setting['stCode'] : '';
        //門市代號
        $stCodeFull = $stCate.$stCode;
        //收件人宅配收件地址
        $rvAddr= (isset($setting['rvAddr'])) ? $setting['rvAddr'] : '';
        //收件人宅配地址郵遞區號
        $rvZip= (isset($setting['rvZip'])) ? $setting['rvZip'] : '';
        //回傳網址路徑
        $rtURL= (isset($setting['rtURL'])) ? $setting['rtURL'] : '';
        //網站所需額外判別資料
        $webPara= (isset($setting['webPara'])) ? $setting['webPara'] : '';
        //商品明細
        $shopping_list= (isset($setting['shopping_list'])) ? $setting['shopping_list'] : '';


        //API網址
        $api_url = 'https://www.ezship.com.tw/emap/ezship_xml_order_api_ex.jsp';
        
        //參數
        $param = '?web_map_xml=';
        $param .= urlencode( preg_replace('/[\n\s]+/', '',
                '<ORDER>
                   <suID>'.$ezShipAccount.'</suID>
                   <orderID>'.$orderID.'</orderID>
                   <orderStatus>'.$orderStatus.'</orderStatus>
                   <orderType>'.$orderType.'</orderType>
                   <orderAmount>'.$orderAmount.'</orderAmount>
                   <rvName><![CDATA['.$rvName.']]></rvName>
                   <rvEmail>'.$rvEmail.'</rvEmail>
                   <rvMobile>'.str_replace('-', '', $rvMobile).'</rvMobile>'));
        
        if($orderStatus=='A05' || $orderStatus=='A06'){
            $param .= urlencode( preg_replace('/[\n\s]+/', '',
                '<rvAddr><![CDATA['.str_replace(' ', '', $rvAddr).']]></rvAddr>
                 <rvZip>'.$rvZip.'</rvZip>'));
        }else{
            $param .= urlencode( preg_replace('/[\n\s]+/', '',
                '<stCode>'.$stCodeFull.'</stCode>'));
        } 

        $param .= urlencode( preg_replace('/[\n\s]+/', '',
                '<rtURL>'.$rtURL.'</rtURL>
                   <webPara>'.$webPara.'</webPara>
                '));
         
        //附加商品明細
        if(is_array($shopping_list) && count($shopping_list)>0 ){
            foreach ($shopping_list as $key => $value){
                $param .= urlencode( preg_replace('/[\n\s]+/', '',
                '<Detail>
                  <prodItem>'.$value['prodItem'].'</prodItem>
                  <prodNo>'.$value['prodItem'].'</prodNo>
                  <prodName><![CDATA['.$value['prodName'].']]></prodName>
                  <prodPrice>'.$value['prodPrice'].'</prodPrice>
                  <prodQty>'.$value['prodQty'].'</prodQty>
                  <prodSpec><![CDATA['.$value['prodSpec'].']]></prodSpec>
                </Detail>'));             
            } 
        } 

        $param .= urlencode( preg_replace('/[\n\s]+/', '','</ORDER>'));  

        //非必要
        $post_data['web_map_xml'] =  $param; 

        //完整的傳遞連結
        $api_url = $api_url.$param;


        //CURL POST 傳遞到ezShip 建立物流訂單
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $api_url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $return = curl_exec ( $ch );
        //取得響應資訊 (由於ezship是用網址get回傳因此需解析響應資訊裡的redirect_url)
        $response = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        //只取redirect_url的資料。 eg: http://xshop.aidec.tw/cart/ezship?order_id=12&sn_id=207669587&order_status=S01&webPara=
        $responseUrl = $response;
        //解析網址取得 QUERY 的部分。 eg:order_id=12&sn_id=207669587&order_status=S01&webPara=
        $parseParam = parse_url($responseUrl,PHP_URL_QUERY);
        // echo $api_url;
        // echo '<pre>',print_r($response),'</pre>';
        // echo '<pre>',print_r($responseUrl),'</pre>';
        // echo '<pre>',print_r($parseParam),'</pre>';
        $parseParamArr = array();
        //將QUERY字串轉換成array存入parseParamArr
        parse_str($parseParam,$parseParamArr);
        //echo '<pre>',print_r($parseParamArr),'</pre>';
        curl_close ($ch);

        //回傳的狀態 S01:表示成功
        $return_order_status = (isset($parseParamArr['order_status'])) ? $parseParamArr['order_status'] : '';

        if($return_order_status)!='S01'){
            $errorMsg = '';
            switch ($return_order_status) {
                case 'E05':
                    $errorMsg = ($orderType==1) ? '串接ezShip物流失敗! 錯誤原因:訂單金額只能介於10~8000之間' : '串接ezShip物流失敗! 錯誤原因:訂單金額只能介於0~2000之間';
                    break;
                case 'E00':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:參數傳遞內容有誤或欄位短缺';
                    break;
                case 'E01':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:串接帳號不存在';
                    break;
                case 'E02':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:帳號無建立取貨付款權限 或 無網站串接權限 或 無ezShip宅配權限 ';
                    break;
                case 'E03':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:帳號無可用之 輕鬆袋 或 迷你袋 ';
                    break;
                case 'E04':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:取件門市有誤 ';
                    break;
                case 'E06':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:電子郵件信箱有誤 ';
                    break;
                case 'E07':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:收件人電話有誤 ';
                    break;
                case 'E08':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:狀態有誤 ';
                    break;
                case 'E09':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:型態有誤 ';
                    break;
                case 'E10':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:收件人有誤 ';
                    break;
                case 'E11':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:配送地址有誤 ';
                    break;
                case 'E98':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:系統發生錯誤無法載入';
                    break;
                case 'E99':
                    $errorMsg = '串接ezShip物流失敗! 錯誤原因:系統錯誤 ';
                    break;                                               
                default:
                    # code...
                    break;
            }

            //錯誤回傳輸出，請自行修改此處 
            $output['title']='錯誤'; 
            $output['msg'] = $errorMsg;
            $output['status']='error';
            echo json_encode($output);
            die();
              
        }

        //成功回傳輸出，請自行修改此處 
        echo '建立成功!';
    } 
}

/**
	使用範例
*/

$param = array(
	//[必填]ezShip 帳號 
    'suID'=>'example@gmail.com',
    //[必填]訂單ID， 購物網站本身的訂單編號
    'orderID'=>'',
    //[必填]訂單狀態 A01~A06
    'orderStatus'=>'',
    //[必填]訂單類別 1:取貨付款[需開通商務帳號] 3:取貨不付款 
    'orderType'=>'',
    //[必填]訂單金額
    'orderAmount'=>'',
    //[必填]取件人
    'rvName'=>'',
    //[必填]取件人電子郵件
    'rvEmail'=>'',
    //[必填]取件人行動電話
    'rvMobile'=>'',
    //門市通路別 [orderStatus 為 A01、A02、A03、A04 時必須 ( 店到店資料 )]
    'stCate'=>'',
    //門市代號 [orderStatus 為 A01、A02、A03、A04 時必須 ( 店到店資料 )]
    'stCode'=>'', 
    //收件人宅配收件地址 [orderStatus 為 A05、A06 時必須]
    'rvAddr'=>'',
    //收件人宅配地址郵遞區號 [orderStatus 為 A05、A06 時必須]
    'rvZip'=>'',
    //[必填]回傳網址路徑
    'rtURL'=>'',
    //[選填]網站所需額外判別資料
    'webPara'=>'',
    //[選填]商品明細
    'shopping_list'=>array(
    	array(
    		//訂單商品序號
    		'prodItem'=>'',
    		//商品編號
    		'prodNo'=>'',
    		//商品名稱
    		'prodName'=>'',
    		//商品價格
    		'prodPrice'=>'',
    		//商品數量
    		'prodQty'=>'',
    		//商品規格
    		'prodSpec'=>'',
    	),
    	array(
    		//訂單商品序號
    		'prodItem'=>'',
    		//商品編號
    		'prodNo'=>'',
    		//商品名稱
    		'prodName'=>'',
    		//商品價格
    		'prodPrice'=>'',
    		//商品數量
    		'prodQty'=>'',
    		//商品規格
    		'prodSpec'=>'',
    	),
    ),
);

//初始化
$ezship = new ezShip();
//執行
$ezship->send($param);	