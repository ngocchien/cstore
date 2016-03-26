<?php

namespace Backend\Validate;

use Zend\Validator;

class Product {

    // chua thong bao loi
    protected $_messagesError = null;
    // mang du lieu sau khi kiem tra
    protected $_arrData;

    public function __construct($arrParam = null) {

        $validatorEmpty = new Validator\NotEmpty();
        $validatorIsInt = new \Zend\I18n\Validator\Int();
        $validatorStringLength = new \Zend\Validator\StringLength();

        // ========================================
        // KIEM TRA prod_name
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['prod_name'])) {
            $this->_messagesError['prod_name'] = 'Tên sản phẩm không được bỏ trống!';
        }

        // ========================================
        // KIEM TRA cate_id
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['cate_id'])) {
            $this->_messagesError['cate_id'] = 'Chưa chọn danh mục cho sản phẩm!';
        }

        // ========================================
        // KIEM TRA bran_id
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['bran_id'])) {
            $this->_messagesError['bran_id'] = 'Chưa chọn thương hiệu cho sản phẩm!';
        }

        // ========================================
        // KIEM TRA tag_id
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['tags_id'])) {
            $this->_messagesError['tags_id'] = 'Chưa chọn tags cho sản phẩm!';
        }

        // ========================================
        // KIEM TRA prod_price
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['prod_price'])) {
            $this->_messagesError['prod_price'] = 'Chưa nhập giá cho sản phẩm!';
        } else {
            if (!$validatorIsInt->isValid($arrParam['prod_price'])) {
                $this->_messagesError['prod_price'] = 'Giá sản phẩm phải là số nguyên!';
            }
        }

        // ========================================
        // KIEM TRA prod_promotion_price
        // ========================================
        if ($arrParam['prod_promotion_price']) {
            if (!$validatorIsInt->isValid($arrParam['prod_promotion_price'])) {
                $this->_messagesError['prod_promotion_price'] = 'Giá khuyến mãi phải là số nguyên!';
            }
        }

        if (!$validatorEmpty->isValid($arrParam['prod_detail'])) {
            $this->_messagesError['prod_detail'] = 'Chưa nhập nội dung chi tiết cho sản phẩm!';
        } else {
            $validatorStringLength->setMin(30);
            if (!$validatorStringLength->isValid($arrParam['prod_detail'])) {
                $this->_messagesError['prod_detail'] = 'Nội dung sản phẩm phải 30 ký tự trở lên!';
            }
        }

        // ========================================
        // KIEM TRA prod_image
        // ========================================
        if (!$validatorEmpty->isValid($arrParam['prod_image'])) {
            $this->_messagesError['prod_image'] = 'Vui lòng chọn hình cho sản phẩm';
        }
        
        $this->_arrData = $arrParam;
    }

    public function isExisted($strValidate, $intID, $strfield, $options) {
        if (empty($strValidate) || empty($options) || empty($strfield)) {
            return false;
        }
        $arrCondition = array(
            $strfield => $strValidate,
            'isDeleted' => 0,
            'isActived' => 1,
        );

        $customer = $options->findOneBy($arrCondition);
        if ($strValidate == 'amyhuong82@gmail.com') {
            p($customer);
        }
        if ($customer) {
            if ($intID) {   //edit
                if ($customer->getCustomerId() == (int) $intID) {
                    return true;
                } else {
                    return false;
                }
            }
            return false; //add
        } else {
            return false;
        }
    }

    // Kiem tra Error
    // return true neu co loi xuat hien
    public function isError() {
        if ($this->_messagesError !== null) {
            return true;
        } else {
            return false;
        }
    }

    // Tra ve mot mang cac loi
    public function getMessageError() {
        return $this->_messagesError;
    }

    // Tra ve mot mang du lieu sau khi kiem tra
    public function getData($options = null) {
        return $this->_arrData;
    }

}
