<?php

namespace My\Shipping;

class Proship {

    // URL 
    const proship_url_dev = 'http://dev.api.proship.vn/v1/';
    // Mã merchante 
    const merchant_id_dev = '29201';
    // Mật khẩu bảo mật
    const secure_code_dev = '5b088870f5e786a8dce5ce4b91e8d2ce3a5b7ebc';
    // URL 
    const proship_url = 'http://api.proship.vn/v1/';
    // Mã merchante 
    const merchant_id = '29201';
    // Mật khẩu bảo mật
    const secure_code = '5b088870f5e786a8dce5ce4b91e8d2ce3a5b7ebc';

    public static function createRequest($data = []) {
        $proship_url = (APPLICATION_ENV != 'production') ? self::proship_url_dev : self::proship_url;
        $merchant_id = (APPLICATION_ENV != 'production') ? self::merchant_id_dev : self::merchant_id;
        $secure_code = (APPLICATION_ENV != 'production') ? self::secure_code_dev : self::secure_code;
        $receiverPhone = $data['receiverPhone'] ? trim($data['receiverPhone']) : '0123456789';
        $receiverAddress = $data['receiverAddress'] ? trim($data['receiverAddress']) : '123 Hai Bà Trưng';
        $receiverCity = $data['receiverCity'] ? (int) $data['receiverCity'] : 29;
        $receiverDistrict = $data['receiverDistrict'] ? (int) $data['receiverDistrict'] : 633;
        $receiverWard = $data['receiverWard'] ? (int) $data['receiverWard'] : 10114;
        $shippingMethod = $data['shippingMethod'] ? (int) $data['shippingMethod'] : 3;
        $productName = $data['productName'] ? $data['productName'] : 'SP-A';
        $orderCode = $data['orderCode'] ? $data['orderCode'] : 'MG0000';
        $receiverName = $data['receiverName'] ? $data['receiverName'] : 'No Name';
        $payBy = $data['payBy'] ? (int) $data['payBy'] : 1;
        $codCost = $data['codCost'] ? (float) $data['codCost'] : 0;
        $weigh = $data['weigh'] ? (float) $data['weigh'] : 0.5;
        $arrData = array(
            'merchantSiteCode' => $merchant_id,
            'secureCode' => $secure_code,
            'productName' => $productName, //Tên sản phẩm
            'orderCode' => $orderCode,
            'weigh' => $weigh, //khối lượng sản phẩm
            'codCost' => $codCost, //Số tiền thu hộ
            'description' => '', //Ghi chú
            'receiverName' => $receiverName, //Tên người nhận
            'receiverPhone' => $receiverPhone, //Số đt người nhận
            'receiverAddress' => $receiverAddress, //Địa chỉ người nhận
            'receiverCity' => $receiverCity, //tỉnh/TP người nhận
            'receiverDistrict' => $receiverDistrict, // Quận/Huyện người nhận
            'receiverWard' => $receiverWard, // Quận/Huyện người nhận
            'shippingMethod' => $shippingMethod, //Gói vận chuyển
            'payBy' => $payBy //Hình thức thanh toán
        );
        $strJsonData = json_encode($arrData);
        $ch = curl_init();

        if (false === $ch) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, $proship_url . 'invoice/add');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
                )
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strJsonData);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output == false || $output == null) {
            return [];
        }
        return $output;
    }

    public static function getCityList() {
        $proship_url = (APPLICATION_ENV != 'production') ? self::proship_url_dev : self::proship_url;
        $merchant_id = (APPLICATION_ENV != 'production') ? self::merchant_id_dev : self::merchant_id;
        $secure_code = (APPLICATION_ENV != 'production') ? self::secure_code_dev : self::secure_code;

        $arrData = array(
            'merchantSiteCode' => self::merchant_id,
            'secureCode' => self::secure_code,
        );
        $strJsonData = json_encode($arrData);

        $ch = curl_init();

        if (false === $ch) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, $proship_url . 'city/list?merchantSiteCode=' . $merchant_id . '&secureCode=' . $secure_code);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
                )
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $strJsonData);
        $output = curl_exec($ch);
        $arrData = json_decode($output, true);
        if ($arrData['code'] == 0)
            return $arrData['data'];
        return false;
    }

    public static function getDistricList($cityId = 0) {
        $proship_url = (APPLICATION_ENV != 'production') ? self::proship_url_dev : self::proship_url;
        $merchant_id = (APPLICATION_ENV != 'production') ? self::merchant_id_dev : self::merchant_id;
        $secure_code = (APPLICATION_ENV != 'production') ? self::secure_code_dev : self::secure_code;
        $ch = curl_init();

        if (false === $ch) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, $proship_url . 'district/list?merchantSiteCode=' . $merchant_id . '&secureCode=' . $secure_code . "&cityId=" . $cityId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
                )
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        $arrData = json_decode($output, true);
        if ($arrData['code'] == 0)
            return $arrData['data'];
        return false;
    }

    public static function getWardList($intDistrictID = 0) {
        $proship_url = (APPLICATION_ENV != 'production') ? self::proship_url_dev : self::proship_url;
        $merchant_id = (APPLICATION_ENV != 'production') ? self::merchant_id_dev : self::merchant_id;
        $secure_code = (APPLICATION_ENV != 'production') ? self::secure_code_dev : self::secure_code;
        $ch = curl_init();

        if (false === $ch) {
            throw new Exception('failed to initialize');
        }

        curl_setopt($ch, CURLOPT_URL, $proship_url . 'ward/list?merchantSiteCode=' . $merchant_id . '&secureCode=' . $secure_code . '&districtId=' . $intDistrictID);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Connection: Keep-Alive'
                )
        );

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        $arrData = json_decode($output, true);
        if ($arrData['code'] == 0)
            return $arrData['data'];
        return false;
    }
}
