<?php

/**
 * WDS GROUP
 *
 * @name        NganLuong.php
 * @category    
 * @package     package_name
 * @author      Toan Pham Duc <toanbk@wds.vn>
 * @copyright   Copyright (c)2008-2010 WDS GROUP. All rights reserved
 * @license     http://wds.vn/license/     WDS Software License
 * @version     $1.0.0$
 * 1:53:48 AM - Apr 20, 2012
 *
 * LICENSE
 *
 * This source file is copyrighted by WDS, full details in LICENSE.txt.
 * It is also available through the Internet at this URL:
 * http://wds.vn/license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the Internet, please send an email
 * to license@wds.vn so we can send you a copy immediately.
 *
 */

namespace My\Payment;

use WDS\Model\Business\PaymentType;

class Nganluong
{
    // URL chheckout của nganluong.vn
    //private $_payGateUrl = 'https://www.nganluong.vn/checkout.php';
    private $_payGateUrl = 'http://sandbox.nganluong.vn/checkout.php';

    // Mã merchante site
    //private $_merchantSiteCode;	// Biến này được nganluong.vn cung cấp khi bạn đăng ký merchant site
    private $_merchantSiteCode = '642'; //** Biến này được nganluong.vn cung cấp khi bạn đăng ký merchant site


    // Mật khẩu bảo mật
    //private $_securePass; // Biến này được nganluong.vn cung cấp khi bạn đăng ký merchant site
    private $_securePass = '123456789'; // Biến này được nganluong.vn cung cấp khi bạn đăng ký merchant site
    
    public function __construct(){
        
    }

    //Hàm xây dựng url, trong đó có tham số mã hóa (còn gọi là public key)
    public function buildCheckoutUrl($return_url, $receiver, $transaction_info, $order_code, $price)
    {

        // Mảng các tham số chuyển tới nganluong.vn
        $arr_param = array (
                'merchant_site_code' => strval ( $this->_merchantSiteCode ), 'return_url' => strtolower ( urlencode ( $return_url ) ), 'receiver' => strval ( $receiver ), 'transaction_info' => strval ( $transaction_info ), 'order_code' => strval ( $order_code ), 'price' => strval ( $price )
        );
        $secure_code = '';
        $secure_code = implode ( ' ', $arr_param ) . ' ' . $this->_securePass;
        $arr_param ['secure_code'] = md5 ( $secure_code );

        /* Bước 2. Kiểm tra  biến $redirect_url xem có '?' không, nếu không có thì bổ sung vào*/
        $redirect_url = $this->_payGateUrl;
        if (strpos ( $redirect_url, '?' ) === false)
        {
            $redirect_url .= '?';
        }
        else if (substr ( $redirect_url, strlen ( $redirect_url ) - 1, 1 ) != '?' && strpos ( $redirect_url, '&' ) === false)
        {
            // Nếu biến $redirect_url có '?' nhưng không kết thúc bằng '?' và có chứa dấu '&' thì bổ sung vào cuối
            $redirect_url .= '&';
        }

        /* Bước 3. tạo url*/
        $url = '';
        foreach ( $arr_param as $key => $value )
        {
            if ($url == '')
                $url .= $key . '=' . $value;
            else
                $url .= '&' . $key . '=' . $value;
        }

        return $redirect_url . $url;
    }

    /*Hàm thực hiện xác minh tính đúng đắn của các tham số trả về từ nganluong.vn*/

    public function verifyPaymentUrl($transaction_info, $order_code, $price, $payment_id, $payment_type, $error_text, $secure_code)
    {
        // Tạo mã xác thực từ chủ web
        $str = '';
        $str .= ' ' . strval ( $transaction_info );
        $str .= ' ' . strval ( $order_code );
        $str .= ' ' . strval ( $price );
        $str .= ' ' . strval ( $payment_id );
        $str .= ' ' . strval ( $payment_type );
        $str .= ' ' . strval ( $error_text );
        $str .= ' ' . strval ( $this->_merchantSiteCode );
        $str .= ' ' . strval ( $this->_securePass );

        // Mã hóa các tham số
        $verify_secure_code = '';
        $verify_secure_code = md5 ( $str );

        // Xác thực mã của chủ web với mã trả về từ nganluong.vn
        if ($verify_secure_code === $secure_code)
            return true;

        return false;
    }
	/**
     * @return the $_payGateUrl
     */
    public function getPayGateUrl ()
    {
        return $this->_payGateUrl;
    }

	/**
     * @param string $_payGateUrl
     */
    public function setPayGateUrl ($_payGateUrl)
    {
        $this->_payGateUrl = $_payGateUrl;
    }

	/**
     * @return the $_merchantSiteCode
     */
    public function getMerchantSiteCode ()
    {
        return $this->_merchantSiteCode;
    }

	/**
     * @param string $_merchantSiteCode
     */
    public function setMerchantSiteCode ($_merchantSiteCode)
    {
        $this->_merchantSiteCode = $_merchantSiteCode;
    }

	/**
     * @return the $_securePass
     */
    public function getSecurePass ()
    {
        return $this->_securePass;
    }

	/**
     * @param string $_securePass
     */
    public function setSecurePass ($_securePass)
    {
        $this->_securePass = $_securePass;
    }

}