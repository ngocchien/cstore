<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate;
use Zend\View\Model\ViewModel;

class CheckoutController extends MyController {
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceOrder \My\Models\Order */
    /* @var $serviceProductOrder \My\Models\ProductOrder */
    /* @var $serviceDistrict \My\Models\District */
    /* @var $serviceWard \My\Models\Ward */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {

            $this->defaultJS = [
                'frontend:checkout:index' => 'jquery.lazyload.js',
                'frontend:checkout:step-one' => 'jquery.lazyload.js',
                'frontend:checkout:step-two' => 'jquery.lazyload.js'
            ];
            $this->externalJS = [
                'frontend:checkout:index' => array(
                    STATIC_URL . '/f/v1/js/my/??checkout.js',
                ),
                'frontend:checkout:step-one' => array(
                    STATIC_URL . '/f/v1/js/my/??checkout.js',
                ),
                'frontend:checkout:step-two' => array(
                    STATIC_URL . '/f/v1/js/my/??checkout.js',
                ),
            ];
        } else {
            $this->externalJS = [
                'frontend:checkout:index' => array(
                    STATIC_URL . '/f/mobile/js/my/??checkout.js',
                ),
                'frontend:checkout:step-one' => array(
                    STATIC_URL . '/f/mobile/js/my/??checkout.js',
                ),
                'frontend:checkout:step-two' => array(
                    STATIC_URL . '/f/mobile/js/my/??checkout.js',
                ),
            ];
        }
    }

    public function indexAction() {
//        $params = $this->params()->fromRoute();
////        p($_COOKIE['cookieCart']);die;
//        if (!isset($_COOKIE['cookieCart']) && !isset($params['prod_id'])) {
//            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
//        }
//        $arrProductListCookie = unserialize($_COOKIE['cookieCart']);
//        if (isset($params['prod_id']) && !empty($params['prod_id']) && empty($this->request->isPost())) {
//            $producID = $params['prod_id'];
//            $quantity = 1;
//            $viewCart = array(
//                'prod_id' => $producID,
//                'quantity' => $quantity,
//            );
//
//            if (!isset($_COOKIE['cookieCart'])) {
//                setcookie('cookieCart', serialize(array($producID => $viewCart)), time() + 604800, "/");
//                $arrProductListCookie[$producID] = $viewCart;
//            } else {
//                if (!in_array($arrProductListCookie[$producID], $arrProductListCookie)) {
//                    $arrProductListCookie[$producID] = $viewCart;
//                    setcookie('cookieCart', serialize($arrProductListCookie), time() + 604800, "/");
//                } else {
//                    $arrProductListCookie[$producID]['quantity'] = $arrProductListCookie[$producID]['quantity'] + $quantity;
//                    if ($arrProductListCookie[$producID]['quantity'] > 20) {
//                        $arrProductListCookie[$producID]['quantity'] = 20;
//                    }
//                    setcookie('cookieCart', serialize($arrProductListCookie), time() + 604800, "/");
//                }
//            }
//        }
//
//        $errors = array();
//        foreach ($arrProductListCookie as $key => $value) {
//            $listId[] = $key;
//        }
//
//        if (count($listId) <= 0) {
//            $errors[] = 'Xảy ra lỗi trong quá trình xử lý !';
//        }
//
//        if (count($listId) > 0) {
//            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//            $strListId = implode(',', $listId);
//            $arrCondition = array('listProductID' => $strListId);
//            $listProductCart = $serviceProduct->getList($arrCondition);
//        }
//
//        if ($this->request->isPost()) {
//            $params = $this->params()->fromPost();
//
//            if (empty($params['payment'])) {
//                $errors['payment'] = 'Vui lòng chọn thông tin thanh toán';
//            }
//            $isPayment = $params['payment'];
//
//            if (empty($errors)) {
//                $_SESSION["payment"] = $isPayment;
//                return $this->redirect()->toRoute('frontend', array('controller' => 'checkout', 'action' => 'step-one'));
//            }
//            $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại';
//        }
//
//        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
//        $this->renderer->headTitle(html_entity_decode('Đặt hàng - Xác nhận hình thức thanh toán ') . General::TITLE_META);
//        $this->renderer->headMeta()->appendName('description', html_entity_decode('Lựa chọn hình thức thanh toán cho đơn hàng !'));
//
//        return array(
//            'params' => $params,
//            'errors' => $errors,
//            'arrProductListCookie' => $arrProductListCookie,
//            'listProductCart' => $listProductCart
//        );
    }

    public function StepOneAction() {
        $params = $this->params()->fromRoute();
        if (!isset($_COOKIE['cookieCart'])) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $errors = array();
        $arrProductListCookie = unserialize($_COOKIE['cookieCart']);

        foreach ($arrProductListCookie as $key => $value) {
            $listId[] = $key;
        }

        if (count($listId) <= 0) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
        }

        if (count($listId) > 0) {
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $strListId = implode(',', $listId);
            $arrCondition = array('listProductID' => $strListId);
            $listProductCart = $serviceProduct->getList($arrCondition);
//            $instanceSearchProduct = new \My\Search\Products();
//            $arrCondition = array();
//            $arrCondition['listProductID'] =  $listId;
//            $instanceSearchProduct->setParams($arrCondition);
//            $listProductCart = $instanceSearchProduct->getList();
        }
        $totalPrice = 0;
        foreach ($listProductCart as $product) {
            $totalPrice = ($product['prod_is_promotion'] == 1) ? $totalPrice + ($product['prod_promotion_price'] * $arrProductListCookie[$product['prod_id']]['quantity']) : $totalPrice + ($product['prod_price'] * $arrProductListCookie[$product['prod_id']]['quantity'] );
        }
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $arrCity = $serviceCity->getList(array('not_city_status' => 1));
        $arrCityFormat = array();
        foreach ($arrCity as $value) {
            $arrCityFormat[$value['city_id']] = $value;
        }

        $arrDistrict = array();
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');
        if (CITY_ID > 0) {
            $arrDistrict = $serviceDistrict->getList(array('city_id' => CITY_ID));
        }

        $arrWardList = array();
        $serviceWard = $this->serviceLocator->get('My\Models\Ward');
        if (DISTRICT_ID > 0)
            $arrWardList = $serviceWard->getList(array('dist_id' => DISTRICT_ID));


        $totalPriceRedu = $totalPrice;
        $moneyRedu = 0;
        $serviceVoucher = $this->serviceLocator->get('My\Models\Voucher');
        if (!empty($_COOKIE['voucher'])) {
            $detailVoucher = $serviceVoucher->getDetail(array('vouc_code' => $_COOKIE['voucher']));
            if (!empty($detailVoucher)) {
                $valueVouc = (!empty($detailVoucher['vouc_percent'])) ? $detailVoucher['vouc_percent'] : $detailVoucher['vouc_money'];
                ($valueVouc < 100) ? $moneyRedu = ($totalPrice * $valueVouc) / 100 : $moneyRedu = $valueVouc;
                if ($detailVoucher['vouc_type'] == 2) {
                    $availble = $detailVoucher['vouc_value'] - $detailVoucher['vouc_available'];
                    if ($availble < $moneyRedu)
                        $moneyRedu = $availble;
                }
                $totalPriceRedu = $totalPrice - $moneyRedu;
            }
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            //p($params);die;
            $validator = new Validate();
            $errors = array();
            if (empty($params['strFullname'])) {
                $errors['fullname'] = 'Họ và tên người nhận hàng không được bỏ trống !';
            }
            if (empty($params['strPhone'])) {
                $errors['phoneNumber'] = 'Chưa nhập số điện thoại người nhận hàng !';
            }
            if (empty($params['strEmail'])) {
                $errors['Email'] = 'Chưa nhập Email người nhận hàng !';
            }
            if (empty($params['strAddress'])) {
                $errors['address'] = 'Chưa nhập địa chỉ người nhận hàng !';
            }

            if (empty($params['intCity'])) {
                $errors['intCity'] = 'Chưa chọn Tỉnh / Thành !';
            }

//            if (empty($params['intDist'])) {
//                $errors['intDist'] = 'Quận / Huyện không hợp lệ !';
//            }

            if (empty($params['intWard'])) {
                $errors['intWard'] = 'Chưa chọn Tỉnh / Thành !';
            }
            if (empty($params['payment'])) {
                $errors['payment'] = 'Vui lòng chọn thông tin thanh toán';
            }

            $isPayment = $params['payment'];
            if (!empty($params['intCity']))
                $arrDistrict = $serviceDistrict->getList(array('city_id' => (int) $params['intCity']));


            if (!empty($params['intDist']))
                $arrWardList = $serviceWard->getList(array('dist_id' => (int) $params['intDist']));


            $serviceShippingFee = $this->serviceLocator->get('My\Models\ShippingFee');
            $arrShipFee = $serviceShippingFee->getDetail(array('city_id' => (int) $params['intCity'], 'dist_id' => (int) $params['intDist']));
            $shipFee = empty($arrShipFee) ? 0 : $arrShipFee["ship_fee"];
            $intShipingFee = \My\Shipping\Domestic::_calculateFeeFromRange($totalPrice, $shipFee);

            $intDist = trim($params['intDist']);
            $intCity = trim($params['intCity']);
            $intWard = trim($params['intWard']);
            $strFullname = trim($params['strFullname']);
            $strPhoneNumber = trim($params['strPhone']);
            $strEmail = trim($params['strEmail']);
            $strAddress = trim($params['strAddress']);
            $strNote = (!empty($params['strNote'])) ? trim($params['strNote']) : NULL;

            if (!$validator->Digits($intCity)) {
                $errors['intCity'] = 'Tỉnh / Thành không hợp lệ !';
            }


            if (!$validator->Digits($intDist)) {
                $errors['intDist'] = 'Quận / Huyện không hợp lệ !';
            }

            $arrWardDetail = array();
            if (!empty($params['intWard'])) {
                if (!$validator->Digits($params['intWard'])) {
                    $errors['intWard'] = 'Xã / Phường không hợp lệ !';
                }
                $arrWardDetail = $serviceWard->getDetail(array('ward_id' => (int) $params['intWard'], 'not_ward_status' => 1, 'dist_id' => $intDist));
                if (empty($arrWardDetail)) {
                    $errors['intWard'] = 'Xã / Phường không hợp lệ !';
                }
            }

            $arrDistDetail = $serviceDistrict->getDetail(array('dist_id' => $intDist, 'not_dist_status' => 1, 'city_id' => $intCity));

            if (empty($arrDistDetail)) {
                $errors['intDist'] = 'Quận / Huyện không hợp lệ !';
            }

            if (strlen($strFullname) < 3) {
                $errors['fullname'] = 'Vui lòng nhập đầy đủ họ và tên người nhận hàng !';
            }

            if (strpos($strPhoneNumber, '+') === false) {
                if (!$validator->Digits($strPhoneNumber)) {
                    $errors['phoneNumber'] = 'Số điện thoại phải là số! Từ 8 đến 11 số!';
                }

                if (!$validator->Between(strlen($strPhoneNumber), 8, 11)) {
                    $errors['phoneNumber'] = 'Số điện thoại phải là số! Từ 8 đến 11 số!';
                }
            } else {
                $phone = ltrim($strPhoneNumber, '+');
                if (!$validator->Digits($phone)) {
                    $errors['phoneNumber'] = 'Số điện thoại chưa chính xác !';
                }

                if (!$validator->Between(strlen($phone), 8, 11)) {
                    $errors['phoneNumber'] = 'Nhập Số điện thoại chưa chính xác !';
                }
            }

            if (!$validator->emailAddress($strEmail)) {
                $errors['Email'] = 'Vui lòng nhập đúng địa chỉ Email người nhận hàng !';
            }

            if (strlen($strAddress) < 3) {
                $errors['address'] = 'Vui lòng nhập đầy đủ thông tin địa chỉ người nhận hàng !';
            }
            if (empty($errors)) {
                $orderDetail = array(
                    'fullname' => $strFullname,
                    'address' => $strAddress . ' , ' . (empty($arrWardDetail) ? NULL : ($arrWardDetail['ward_name'] . ' , ')) . $arrDistDetail['dist_name'] . ' , ' . $arrCityFormat[$intCity]['city_name'],
                    'phone' => $strPhoneNumber,
                    'email' => $strEmail,
                    'note' => $strNote
                );
                $arrayShipInfo = ['city_id' => $intCity, 'dist_id' => $intDist, 'ward_id' => $intWard];

                $serviceOrder = $this->serviceLocator->get('My\Models\Order');
                // Tính phí vận chuyển            
                $arrOrder = array(
                    'orde_detail' => json_encode($orderDetail),
                    'ship_info' => json_encode($arrayShipInfo),
                    'city_id' => $intCity,
                    'dist_id' => $intDist,
                    'ship_fee' => $intShipingFee,
                    'orde_phone' => $strPhoneNumber,
                    'orde_created' => time(),
                    'user_id' => UID ? (int) UID : NULL,
                    'is_acp' => (int) IS_ACP,
                    'orde_total_price' => $totalPriceRedu + $intShipingFee,
                    'orde_payment' => $isPayment,
                    'user_fullname' => $strFullname,
                    'user_phone' => $strPhoneNumber,
                    'user_email' => $strEmail,
                    'orde_code' => General::ORDER_CODE
                );
                if (IS_ACP == '1') {
                    $arrOrder['user_updated'] = UID;
                }
                if (!empty($_COOKIE['voucher'])) {
                    $arrOrder['vouc_value'] = $moneyRedu;
                }

                $inResult = $serviceOrder->add($arrOrder);
                if ($inResult > 0) {
                    $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
                    foreach ($listProductCart as $key => $value) {
                        $arrProductOrder = array(
                            'orde_id' => $inResult,
                            'prod_id' => $value['prod_id'],
                            'user_id' => UID ? (int) UID : NULL,
                            'prod_name' => $value['prod_name'],
                            'prod_quantity' => $arrProductListCookie[$value['prod_id']]['quantity'],
                            'prod_price' => ($value['prod_is_promotion'] == 1) ? $value['prod_promotion_price'] : $value['prod_price'],
                            'prod_call_price' => $value['prod_call_price'],
                            'total_price' => ($value['prod_is_promotion'] == 1) ? $arrProductListCookie[$value['prod_id']]['quantity'] * $value['prod_promotion_price'] : $arrProductListCookie[$value['prod_id']]['quantity'] * $value['prod_price'],
                            'prod_slug' => $value['prod_slug'],
                            'prod_image' => $value['prod_image'],
                        );
                        $serviceProductOrder->add($arrProductOrder);
                    }
                    if (!empty($_COOKIE['voucher'])) {
                        $serviceVoucherLogs = $this->serviceLocator->get('My\Models\VoucherLogs');
                        $arrDataVoucLogs = array(
                            'orde_id' => $inResult,
                            'vouc_id' => $detailVoucher['vouc_id'],
                            'vouc_value' => $moneyRedu,
                            'vouc_created' => time(),
                        );
                        $serviceVoucherLogs->add($arrDataVoucLogs);
                        if ($detailVoucher['vouc_type'] == 1) {
                            setcookie('voucher', '', time() - 86400, '/');
                            $serviceVoucher->edit(array('vouc_status' => 1), $detailVoucher['vouc_id']);
                        } else {
                            $temp = $detailVoucher['vouc_available'] + $moneyRedu;
                            if ($detailVoucher['vouc_value'] <= $temp) {
                                setcookie('voucher', '', time() - 86400, '/');
                                $serviceVoucher->edit(array('vouc_available' => $temp, 'vouc_status' => 1), $detailVoucher['vouc_id']);
                            } else {
                                $serviceVoucher->edit(array('vouc_available' => $temp), $detailVoucher['vouc_id']);
                            }
                        }
                    }
                    //get template Email
                    $arrTemplateEmail = array(
                        'listProductCart' => $listProductCart,
                        'arrProductListCookie' => $arrProductListCookie,
                        'inResult' => $inResult,
                        'orderDetail' => $orderDetail,
                        'totalPrice' => $totalPrice
                    );

                    $view = new ViewModel($arrTemplateEmail);
                    $view->setTemplate('frontend/template_email');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $content = $viewRender->render($view);
                    //Send Mail
                    $General = new General();
                    //tiêu đề
                    $strTitle = '[Megavita.Vn] Qúy khách vừa đặt hàng thành công với mã đơn hàng là ' . General::ORDER_CODE . sprintf("%03d", $inResult);
                    //Nội dung email
                    $strMessage = $content;
                    //  $result = $General->sendMail($strEmail, $strTitle, $strMessage);
                    $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                    $instanceJobSendMail = new \My\Job\JobSendEmail();
                    $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);
                    setcookie('cookieCart', '', time() - 3600, '/');
                    $_SESSION["LastOrderID"] = $inResult;
                    $this->redirect()->toRoute('checkout_step_two', array('controller' => 'checkout', 'action' => 'step-two'));
                }
            }
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Đặt hàng - Xác nhận địa chỉ nhận hàng ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Xác nhận địa chỉ nhận hàng !'));

        return array(
            'errors' => $errors,
            'params' => $params,
            'arrProductListCookie' => $arrProductListCookie,
            'listProductCart' => $listProductCart,
            'arrDistrict' => $arrDistrict,
            'arrCity' => $arrCity,
            'arrWardList' => $arrWardList,
            'moneyRedu' => $moneyRedu,
            'totalPriceRedu' => $totalPriceRedu,
            'intShipingFee' => $intShipingFee
        );
    }

    public function StepTwoAction() {
        $params = $this->params()->fromRoute();

        if (!$_SESSION['LastOrderID']) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $arrOrder = $serviceOrder->getDetail(array('orde_id' => $_SESSION['LastOrderID']));
        unset($_SESSION['LastOrderID']);

        if (!$arrOrder) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $arrDetailOrder = $serviceProductOrder->getList(array('orde_id' => $arrOrder['orde_id']));

        if (!$arrDetailOrder) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Đặt hàng - Hoàn tất đơn hàng ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Thông tin đơn hàng - Hoàn tất đơn hàng !'));

        return array(
            'arrOrder' => $arrOrder,
            'arrDetailOrder' => $arrDetailOrder,
        );
    }

    public function getListDistrictAction() {
        $params = $this->params()->fromPost();


        if (empty($params['cityID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa chọn Tỉnh / thành!')));
        }
        $validator = new Validate();
        if (!$validator->Digits($params['cityID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chọn Tỉnh / thành không hợp!')));
        }
        $arrData = array('city_id' => $params['cityID'], 'not_dist_status' => 1);
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');
        $arrDistrictList = $serviceDistrict->getList($arrData);
        if (empty($arrDistrictList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }
        // Tính toán giá shipping
        $totalPriceOrder = (int) $params['totalOrder'];
        $totalRedu = (int) $params['totalRedu'];
        $serviceShippingFee = $this->serviceLocator->get('My\Models\ShippingFee');
        $arrShipFee = $serviceShippingFee->getDetail(array('city_id' => (int) $params['cityID'], 'dist_id' => (int) $params['distID']));
        $shipFee = empty($arrShipFee) ? 0 : $arrShipFee["ship_fee"];
        $shipingFee = \My\Shipping\Domestic::_calculateFeeFromRange($totalPriceOrder, $shipFee);

        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrDistrictList, 'total_redu' => ['int' => $totalRedu, 'string' => number_format($totalRedu + $shipingFee, 0, ",", ".")], 'ship_fee' => ['int' => $shipingFee, 'string' => number_format($shipingFee, 0, ",", ".")])));
        die();
    }

    public function getListWardAction() {
        $params = $this->params()->fromPost();


        if (empty($params['distID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa chọn Quận / Huyện!')));
        }
        $validator = new Validate();
        if (!$validator->Digits($params['distID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chọn Quận / Huyện không hợp!')));
        }
        $arrData = array('dist_id' => $params['distID'], 'not_ward_status' => 1);
        $serviceWard = $this->serviceLocator->get('My\Models\Ward');
        $arrWardList = $serviceWard->getList($arrData);

        if (empty($arrWardList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }
        // tính toán shing FEE
        $totalPriceOrder = (int) $params['totalOrder'];
        $totalRedu = (int) $params['totalRedu'];
        $serviceShippingFee = $this->serviceLocator->get('My\Models\ShippingFee');
        $arrShipFee = $serviceShippingFee->getDetail(array('city_id' => (int) $params['cityId'], 'dist_id' => (int) $params['distID']));
        $shipFee = empty($arrShipFee) ? 0 : $arrShipFee["ship_fee"];
        $shipingFee = \My\Shipping\Domestic::_calculateFeeFromRange($totalPriceOrder, $shipFee);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrWardList, 'total_redu' => ['int' => $totalRedu, 'string' => number_format($totalRedu + $shipingFee, 0, ",", ".")], 'ship_fee' => ['int' => $shipingFee, 'string' => number_format($shipingFee, 0, ",", ".")])));
        die();
    }

}
