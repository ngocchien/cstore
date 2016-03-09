<?php

namespace My\Shipping;

class Domestic extends Shipping {

    /**
     * calculate fee
     * @param float $weight (gr)
     * @param array $arrAreaFee
     * @return number
     */
    public static function _calculateFeeFromRange($priceCart, $shipFee) {
        if (empty($shipFee)) {
            $shipFee = 0;//45000; // nếu không lấy được phí ship từ db thì phí ship sẽ là 45k mặc định
        }
        $fee = $shipFee;
//        $fee = 0;
//        if (empty($priceCart)) {
//            // Nếu giá đơn hàng = 0 Thì ko có ship đâu mà tính nên cho =0
//            return $fee;
//        }
//        if (empty($shipFee)) {
//            $shipFee = 45000; // nếu không lấy được phí ship từ db thì phí ship sẽ là 45k mặc định
//        }
//
//        if ($priceCart < 300000) {
//            $fee = $shipFee;
//        } else if ($priceCart >= 300000 && $priceCart < 500000) {
//            $fee = $shipFee * 50 / 100;
//        } else if ($priceCart >= 500000) {
//            $fee = 0;
//        }
        return $fee;
    }

}
