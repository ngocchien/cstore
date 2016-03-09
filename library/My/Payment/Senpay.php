<?php

namespace My\Payment;

class Senpay {
    
    private $checkout_url = 'http://sandbox.senpay.vn/CheckOut/CheckOut.aspx';
        
    private $reciver_id ='1404087681'; // Thay mã tài khoản (mã tài khoản nhận tiền tại SenPay) mà bạn đã đăng ký tại đây

    private $merchant_site_code = '10000136'; // Mã website của bạn đăng ký trong chức năng tích hợp thanh toán của SenPay.vn.

    private $site_pass= '123456';

    public function getOrder($p_intPaymentID, $p_strOrderCode) {      
        $result = array();
        try {
            $strSecureCode = $this->getSecureCode(array($p_intPaymentID, $p_strOrderCode, $this->reciver_id, $this->site_code, $this->server_addr)); 
            $strRequest = '<request>
                                <payment_id>' . $p_intPaymentID . '</payment_id>
                                <order_code>' . $p_strOrderCode . '</order_code>
                                <receiverid>' . $this->reciver_id . '</receiverid>
                                <merchant_code>' . $this->site_code . '</merchant_code>
                                <secure_code>' . $strSecureCode . '</secure_code>
                                <ip>' . $this->server_addr . '</ip>
                            </request>';
            
            $client = new Zend\Soap\Client($this->merchant_url,  array('soap_version' => SOAP_1_1));
        
            $objResponse = $client->PayCheckOut_CheckOrder(array('XMlData' => $strRequest));

            if (is_object($objResponse) && !empty($objResponse->PayCheckOut_CheckOrderResult)) {
                $arrResponse = (array) Vendor_Xml2Array::convert($objResponse->PayCheckOut_CheckOrderResult);
                $result = $arrResponse['result'];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    public function getCheckoutURL($p_strReturnURL, $p_strTransactionInfo, $p_strOrderCode, $p_intPrice) {
        $result = '';
        try {
            $arrParam =  array(
                'merchant_site_code' => $this->merchant_site_code,
                'return_url'         => strval(strtolower(urlencode($p_strReturnURL))),
                'receiver'           => strval($this->reciver_id),
                'transaction_info'   => $p_strTransactionInfo,
                'order_code'         => strval($p_strOrderCode),
                'price'              => strval($p_intPrice),
            );

            $strSecureCode = $this->getSecureCode($arrParam);
            $arrParam['return_url']  = strval(strtolower($p_strReturnURL));
            $arrParam['secure_code'] = $strSecureCode;
            
            $strParam = http_build_query($arrParam); 
            $result = $this->checkout_url.'?'.$strParam;
            
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $result;
    }

    public function validateDataSuccess($p_strSecureCode, $p_strTransactionInfo, $p_strOrderCode, $p_intPrice, $p_intPaymentID, $p_intPaymentType, $p_intErrorCode) {
        $result = false;

        try {
            $arrData = array(
                $p_strTransactionInfo,
                $p_strOrderCode,
                $p_intPrice,
                $p_intPaymentID,
                $p_intPaymentType,
                $p_intErrorCode,
                $this->site_code
            );
            
            $strSecureCode = $this->getSecureCode($arrData);         

            if ($p_strSecureCode == $strSecureCode) {
                $result = true;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    public function validateDataError($p_strSecureCode, $p_strOrderCode, $p_intErrorCode) {
        $result = false;

        try {
            $arrData = array(
                $p_strOrderCode,
                $p_intErrorCode
            );

            $strSecureCode = $this->getSecureCode($arrData);

            if ($p_strSecureCode == $strSecureCode) {
                $result = true;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    public function getSecureCode($p_arrData) {        
        $result  = '';
        $strData = implode(' ', $p_arrData);  
        $result  = sha1(' ' . $strData . ' ' . $this->site_pass);
        return $result;
    }
}
