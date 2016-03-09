<?php

namespace Backend\Controller;

use My\General,
    My\Shipping\Proship,
    My\Barcode\Barcode,
    My\Validator\Validate,
    My\Controller\MyController,
    Zend\View\Model\ViewModel,
    Zend\View;

class OrderController extends MyController {
    /* @var $serviceOrder \My\Models\Order */
    /* @var $serviceProductOrder \My\Models\ProductOrder */
    /* @var $serviceOrderLog \My\Models\OrderLog */
    /* @var $serviceShipping \My\Models\Shipping */
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceNoteOrder \My\Models\NoteOrder */

    public function __construct() {
        $this->defaultJS = [
            'backend:order:index' => 'bootstrap-datepicker.js,jquery.sumoselect.min.js,bootstrap-select.js,jquery.number.js',
            'backend:order:view' => 'bootstrap-datepicker.js,jquery.sumoselect.min.js,bootstrap-select.js,jquery.number.js',
            'backend:order:product_full' => 'bootstrap-datepicker.js,jquery.sumoselect.min.js,bootstrap-select.js,jquery.number.js',
        ];

        $this->defaultCSS = [
            'backend:order:index' => 'sumoselect.css,bootstrap-select.css',
            'backend:order:view' => 'sumoselect.css,bootstrap-select.css',
            'backend:order:product_full' => 'bootstrap-select.css',
        ];

        $this->externalJS = [
            'backend:order:index' => STATIC_URL . '/b/js/my/??order.js',
            'backend:order:product' => array(STATIC_URL . '/b/js/library/??bootstrap-datepicker.js',
                STATIC_URL . '/b/js/library/??jquery.number.js',
                STATIC_URL . '/b/js/my/??order.js'),
            'backend:order:product_full' => array(STATIC_URL . '/b/js/library/??bootstrap-datepicker.js',
                STATIC_URL . '/b/js/library/??jquery.number.js',
                STATIC_URL . '/b/js/my/??order.js'),
            'backend:order:test' => STATIC_URL . '/b/js/my/??order.js',
            'backend:order:view' => array(STATIC_URL . '/b/js/my/??order.js',
                STATIC_URL . '/b/js/library/??jquery.number.js',
                STATIC_URL . '/b/js/library/??bootstrap-datepicker.js'),
            'backend:order:test' => STATIC_URL . '/b/js/my/??order.js',
            'backend:order:product_full' => STATIC_URL . '/b/js/my/??order.js',
            'backend:order:noteorder' => array(STATIC_URL . '/b/js/my/??order.js',
                STATIC_URL . '/b/js/library/??jquery.number.js',
                STATIC_URL . '/b/js/library/??bootstrap-datepicker.js'),
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $params = array_merge($params, $this->params()->fromQuery());
        $route = 'backend-order-search';
        $arrCondition = array();
        if (!empty($params['user_fullname'])) {
            $arrCondition['user_fullname'] = $params['user_fullname'];
        }
        if (!empty($params['user_email'])) {
            $arrCondition['user_email'] = $params['user_email'];
        }
        if (!empty($params['user_phone'])) {
            $arrCondition['user_phone'] = $params['user_phone'];
        }

        if (isset($params['is_acp']) && $params['is_acp'] != "") {
            $arrCondition['is_acp'] = (int) $params['is_acp'];
            if ($arrCondition['is_acp'] == -1) {
                unset($arrCondition['is_acp']);
            }
        }

        if (isset($params['orde_ship']) && $params['orde_ship'] != "") {
            $arrCondition['orde_ship'] = (int) $params['orde_ship'];
        }



        if (!empty($params['Sales'])) {
            if (!in_array(0, explode(',', $params['Sales']))) {
                $arrCondition['strListUserID'] = $params['Sales'];
            }
        }
        if (!empty($params['date_from'])) {
            list($day, $month, $year) = explode('/', $params['date_from']);
            $dateFrom = mktime(0, 0, 0, $month, $day, $year);
            $arrCondition['date_from'] = $dateFrom;
        }
        if (!empty($params['date_to'])) {
            list($day, $month, $year) = explode('/', $params['date_to']);
            $dateTo = mktime(23, 59, 59, $month, $day, $year);
            $arrCondition['date_to'] = $dateTo;
        }

        if (isset($params['Status']) && $params['Status'] != '') {
            if (!in_array('', explode(',', $params['Status']))) {
                $arrCondition['strListStatus'] = $params['Status'];
            }
        }

        if (!empty($params['s'])) {
            $arrCondition['orde_id'] = (int) str_replace(General::PREFIX_IN, '', $params['s']);
        }


        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        if (!empty($params['prod_code'])) {
            // lấy mã sản phẩm từ bảng sản phẩm 
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $arrProd = $serviceProduct->getDetail(['prod_code' => $params['prod_code']]);
            if (!empty($arrProd)) {
                $prodId = $arrProd["prod_id"];
                $arrProdOrder = $serviceProductOrder->getList(array('prod_id' => $prodId));
                if (!empty($arrProdOrder)) {
                    // print_r($arrProdOrder);
                    $odID = [];
                    foreach ($arrProdOrder as $value) {
                        $odID[] = $value['orde_id'];
                    }

                    $arrCondition['list_orde_id'] = implode(",", $odID);
                    // echo $arrCondition['list_orde_id'];die();
                }
            }
        }

        // p($arrCondition);die;
        // p($params);die();
//        if (!empty($params['from']) && !empty($params['to'])) {
//            $arrCondition = array(
//                'from' => strtotime(General::formatDateM_D_Y($params['from'])),
//                'to' => strtotime('+1 day', strtotime(General::formatDateM_D_Y($params['to']))),
//            );
//            $date = array(
//                'from' => $params['from'],
//                'to' => $params['to']
//            );
//        }


        $intPage = $params['page'];
        $intLimit = $this->params()->fromQuery('limit', 15);
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $arrTotalOrder = $serviceOrder->getCountTotalOrder($arrCondition);

        $intTotal = $serviceOrder->getTotal($arrCondition);
        $arrOrderList = $serviceOrder->getListLimit($arrCondition, $intPage, $intLimit, 'orde_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUserSaleList = $serviceUser->getList(array('grou_id' => array(6), 'user_status' => 1));

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        if (!empty($arrOrderList)) {
            $arrListProductOrder = array();
            $strProdID = '';
            foreach ($arrOrderList as $arrListOrder) {
                $arrListProductOrder[$arrListOrder['orde_id']] = $serviceProductOrder->getList(array('orde_id' => $arrListOrder['orde_id']));
                $arrlistProdOrder = $serviceProductOrder->getList(array('orde_id' => $arrListOrder['orde_id']));
                foreach ($arrlistProdOrder as $value) {
                    $strProdID .= $value['prod_id'] . ',';
                }
            }
            $arrProducts = $serviceProduct->getList(array('listProductID' => rtrim($strProdID, ',')));
            foreach ($arrProducts as $arrProduct) {
                $arrListProduct[$arrProduct['prod_id']] = $arrProduct['prod_code'];
            }
        }
        return array(
            'params' => $params,
            'arrOrderList' => $arrOrderList,
            'paging' => $paging,
            'arrUserSaleList' => $arrUserSaleList,
            'arrTotalOrder' => $arrTotalOrder,
            'arrListProductOrder' => $arrListProductOrder,
            'arrListProduct' => $arrListProduct,
        );
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'index'));
        }
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;

        $proship = Proship::getCityList();
        $arrCityList = $proship;

        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $detailOrder = $serviceOrder->getDetail(array('orde_id' => $params['id']));
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');

        $serviceCus = $this->serviceLocator->get('My\Models\TakecareCustomer');
        $arr = $serviceCus->getList(array('orde_id' => $params['id']));
        foreach ($arr as $key => $arr) {
            $arrTakeCus[$arr['prod_id']] = $arr['prod_care_time'];
        }

        $intTotalProductOrder = $serviceOrder->getTotal(array('orde_id' => $params['id']));
        $arrProductList = $serviceProductOrder->getListLimit(array('orde_id' => $params['id']), $intPage, $intLimit, 'product_order_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotalProductOrder, $intPage, $intLimit, $route, $params);
        $arrListCallPrice = array();
        $totalprice = 0;
        foreach ($arrProductList as $value) {
            $arrListCallPrice[] = $value['prod_call_price'];
            $totalprice = $totalprice + $value['total_price'];
        }
        $userInfo = json_decode($detailOrder['orde_detail'], true);
        if ($this->request->isPost()) {
            $errors = array();
            if (in_array('1', $arrListCallPrice)) {
                $errors['CallPrice'] = 'Vui lòng sửa giá liên hệ trước khi duyệt đơn hàng !';
            }
            if (empty($errors)) {
                $arrData = array(
                    'orde_total_price' => $totalprice,
                    'user_change_status' => FULLNAME,
                    'orde_updated' => time(),
                    'user_updated' => UID,
                    'is_payment' => 1
                );
                $result = $serviceOrder->edit($arrData, $detailOrder['orde_id']);
                if ($result) {
                    $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                    $arrOrderLog = array(
                        'user_id' => UID,
                        'orde_id' => $detailOrder['orde_id'],
                        'orde_log_action' => 'Duyệt Đơn hàng ' . $detailOrder['orde_code'] . $detailOrder['orde_id'],
                        'orde_log_created' => time()
                    );
                    $serviceOrderLog->add($arrOrderLog);

                    $arrTemplateEmail = array(
                        'orde_id' => $detailOrder['orde_id']
                    );
                    $view = new ViewModel($arrTemplateEmail);
                    $view->setTemplate('mail_success/html');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $content = $viewRender->render($view);
                    //Send Mail
                    $General = new General();

                    //mailto
                    $strEmail = json_decode($detailOrder['orde_detail'], true)['email'];
                    //tiêu đề
                    $strTitle = '[Megavita.Vn] Duyệt đơn hàng MG' . sprintf("%03d", $detailOrder['orde_id']) . ' thành công';
                    //Nội dung email
                    $strMessage = $content;
                    $General->sendMail($strEmail, $strTitle, $strMessage);

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Order',
                        'logs_action' => 'view',
                        'logs_time' => time(),
                        'logs_detail' => 'Duyệt đơn hàng có id = ' . $detailOrder['orde_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-add-order')->addMessage('Duyệt đơn hàng thành công !');
                    $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'view', 'id' => $detailOrder['orde_id']));
                }
                $errors[] = 'Không duyệt được. Hoặc chưa cập nhật giá liên hệ . Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'errors' => $errors,
            'paging' => $paging,
            'detailOrder' => $detailOrder,
            'params' => $params,
            'userInfo' => $userInfo,
            'arrProductList' => $arrProductList,
            'arrCityList' => $arrCityList,
            'arrTakeCus' => $arrTakeCus
        );
    }

    public function shipingAction() {
        $params = $this->params()->fromRoute();
        $params = array_merge($params, $this->params()->fromPost());
        // print_r($params);die();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'index'));
        }
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $detailOrder = $serviceOrder->getDetail(array('orde_id' => $params['id']));
        if (empty($detailOrder)) {
            $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'index'));
        }
        $arrError = [];
        if (empty($params['proship_address'])) {
            $arrError[] = "Vui lòng nhập địa chỉ";
        }

        if (empty($params['proship_province'])) {
            $arrError[] = "Vui lòng chọn tỉnh thành";
        }


        if (empty($params['proship_district'])) {
            $arrError[] = "Vui lòng chọn quận huyện";
        }


        if (empty($params['proship_ward'])) {
            $arrError[] = "Vui lòng chọn đường";
        }

        if (empty($params['proship_package'])) {
            $arrError[] = "Vui lòng chọn gói dịch vụ";
        }

        if (empty($params['proship_payment'])) {
            $arrError[] = "Vui lòng chọn hình thức thanh toán";
        }
        if (!empty($arrError)) {
            $strMsg = '';
            foreach ($arrError as $value) {
                $strMsg.='- ' . $value . '<br/>';
            }
            $this->flashMessenger()->setNamespace('error-shiping-order')->addMessage($strMsg);
            return $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'view', 'id' => $params['id']));
        }

        $arrOrder = json_decode($detailOrder['orde_detail'], true);
        $orderCode = $detailOrder['orde_code'] . $detailOrder['orde_id'];
        $receiverAddress = $params['proship_address'];
        $receiverCity = $params['proship_province'];
        $receiverDistrict = $params['proship_district'];
        $receiverWard = $params['proship_ward'];
        $shippingMethod = $params['proship_package'];
        $payBy = $params['proship_payment'];
        $weigh = !isset($params['proship_weigh']) ? 0.5 : $params['proship_weigh'];
        // Format Array input proship
        $shipId = (int) $params['shipping'];

        $WarehouseId = 0;
        if ($shipId == 1) {
            // Nếu là proship sẽ chuyển dữ  liệu  =1
            $WarehouseId = 1;
        }

        if ($shipId == 2) {
            // Nếu là giaonhan sẽ chuyển dữ  liệu  =16
            $WarehouseId = 16;
        }


        $arrProship = array(
            'receiverPhone' => $arrOrder['phone'],
            'weigh' => $weigh,
            'receiverAddress' => $receiverAddress,
            'receiverCity' => $receiverCity,
            'receiverDistrict' => $receiverDistrict,
            'receiverWard' => $receiverWard,
            'shippingMethod' => $shippingMethod,
            'productName' => 'Megavita Order ' . $orderCode,
            'orderCode' => $orderCode,
            'payBy' => $payBy,
            'codCost' => $detailOrder['orde_total_price'],
            'receiverName' => $arrOrder['fullname'],
            'WarehouseId' => $WarehouseId
        );

        $result = json_decode(\My\Shipping\Proship::createRequest($arrProship), true);
        if (!empty($result)) {
            if ($result['code'] == 0) {
                $arrDataShip = array(
                    'orde_ship' => $shipId,
                    'orde_id' => $params['id'],
                    'ship_created' => time(),
                    'ship_address' => $receiverAddress,
                    'ship_city' => $receiverCity,
                    'ship_dist' => $receiverDistrict,
                    'ship_ward' => $receiverWard,
                    'ship_service' => 'Proship',
                    'ship_note' => json_encode($result),
                    'city_name' => $params['cityName'],
                    'dist_name' => $params['distName'],
                    'ward_name' => $params['wardName'],
                    'user_created' => UID,
                    'ship_fullname' => $arrOrder['fullname'],
                );
                //print_r($arrDataShip);die();
                $serviceShipping = $this->serviceLocator->get('My\Models\Shipping');
                $serviceShipping->add($arrDataShip);
                $serviceOrder->edit(array('orde_ship' => $shipId, 'user_change_status' => FULLNAME, 'user_updated' => UID, 'is_payment' => 2), $params['id']);
                $this->flashMessenger()->setNamespace('success-shiping-order')->addMessage('Tạo vận đơn thành công!');
            } else {
                $this->flashMessenger()->setNamespace('error-shiping-order')->addMessage($result['message']);
            }
        } else {
            $this->flashMessenger()->setNamespace('error-shiping-order')->addMessage('Tạo vận đơn thất bại!');
        }
        return $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'view', 'id' => $params['id']));
        die;
    }

    public function editAction() {
        $params = $this->params()->fromPost();
        if (empty($params['prOderID']) || empty($params['ProdPrice']) || empty($params['Note'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Vui lòng nhập đầy đủ các thông tin !')));
        }

        $validator = new Validate();
        if (!$validator->Digits($params['ProdPrice'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Giá sản phẩm phải là số, từ 1000 VNĐ đến 999,999,999 VNĐ!')));
        }

        if (!$validator->Between(strlen($params['ProdPrice']), 4, 9)) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Giá sản phẩm phải là số, từ 1000 VNĐ đến 999,999,999 VNĐ! !')));
        }

        if (!$validator->Digits($params['DisCount'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Chiết khấu phải là số !')));
        }

        if (!$validator->Between(strlen($params['Note']), 6, 255)) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Nhập đầy đủ ghi chú, lý do chỉnh sửa giá sản phẩm từ 6->255 kí tự !')));
        }

        $totalPrice = ($params['DisCount'] == 0) ? $params['prodQuantity'] * $params['ProdPrice'] : ($params['prodQuantity'] * $params['ProdPrice']) - ($params['prodQuantity'] * $params['ProdPrice'] * $params['DisCount'] / 100);

        $arrData = array(
            'prod_price' => $params['ProdPrice'],
            'prod_call_price' => 0,
            'discount' => $params['DisCount'],
            'orde_note' => $params['Note'],
            'total_price' => $totalPrice,
        );
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $result = $serviceProductOrder->edit($arrData, $params['prOderID']);
        if ($result) {
            $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
            $arrOrderLog = array(
                'user_id' => UID,
                'orde_id' => $params['OrderID'],
                'orde_log_action' => 'sửa giá sản phẩm product_order_id = ' . $params['prOderID'] . ' trong Đơn hàng ' . General::PREFIX_IN . $params['OrderID'],
                'orde_log_created' => time()
            );
            $serviceOrderLog->add($arrOrderLog);

//            $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
//            $arrOrderNote = array(
//                'user_id' => UID,
//                'user_name' => USERNAME,
//                'note_content' => 'Chỉnh sửa sản phẩm product_order_id = ' . $params['prOderID'] . ' trong Đơn hàng MG' . $params['OrderID'],
//                'note_created' => time()
//            );
//            $serviceNoteOrder->add($arrOrderNote);


            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                'user_id' => UID,
                'logs_controller' => 'Order',
                'logs_action' => 'edit',
                'logs_time' => time(),
                'logs_detail' => 'Chỉnh sửa đơn hàng có id = ' . $params['prOderID'],
            );
            $serviceLogs->add($arrLogs);

            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Cập nhật thành công !')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

    public function updateAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p($params);die;
            if (!isset($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại!')));
            }
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $arrData = array(
//                'orde_id' => $params['orderID'], 
                'user_updated' => UID
            );
            if (isset($params['ispayment'])) {
                if ($params['ispayment'] == 2 || $params['ispayment'] == 3 || $params['ispayment'] == 4) {
                    $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
                    $serviceProductOrder->editToWhere(array('prod_status' => 1), array('orde_id' => $params['orderID']));
                }
                $arrData += array('is_payment' => $params['ispayment']);
                $arrData += array('user_change_status' => FULLNAME);
                $status = empty(General::getStatusOrder()[$params['ispayment']]) ? "" : General::getStatusOrder()[$params['ispayment']];
            }
            if (isset($params['orderCreated'])) {
                $arrData += array('orde_created' => time());
            }
            if (isset($params['method_ship'])) {
                $arrData += array('orde_ship' => (int) $params['method_ship']);
            }
            if (isset($params['comment_content'])) {
                $arrData += array('content_cancel' => $params['comment_content']);
            }

            if (isset($params['fullname']) && isset($params['address']) && isset($params['phone']) && isset($params['email'])) {
                $arrData += array('orde_detail' => json_encode(array('fullname' => $params['fullname'], 'address' => $params['address'], 'phone' => $params['phone'], 'email' => $params['email'], 'note' => $params['note'])));
                $arrData += array('user_fullname' => $params['fullname']);
                $arrData += array('user_phone' => $params['phone']);
                $arrData += array('user_email' => $params['email']);
                $arrData += array('orde_phone' => $params['phone']);

                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $arrDetailUser = $serviceUser->getDetail(array('getEmail' => $params['email'], 'getPhone' => $params['phone']));
                if (!empty($arrDetailUser)) {
                    $arrData += array('user_id' => $arrDetailUser['user_id']);
                }
            }
            if (isset($params['strNote'])) {
                $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
                $arrNoteOrder = array(
                    'note_position' => 'Sửa thông tin khách hàng trong [' . General::PREFIX_IN . $params['orderID'] . ']',
                    'user_id' => UID ? UID : NULL,
                    'user_name' => FULLNAME,
                    'note_created' => time(),
                    'note_reason' => $params['strNote'],
                    'note_type' => 0,
                    'note_status' => 'Sửa thông tin khách hàng'
                );
                $serviceNoteOrder->add($arrNoteOrder);
            }
//            p($arrData);die;
            $result = $serviceOrder->edit($arrData, $params['orderID']);
            if ($result) {
                if (isset($params['ispayment'])) {
                    $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                    $arrOrderLog = array(
                        'user_id' => UID,
                        'orde_id' => $params['orderID'],
                        'orde_log_action' => $status . ' ' . General::PREFIX_IN . $params['orderID'],
                        'orde_log_created' => time()
                    );
                    $serviceOrderLog->add($arrOrderLog);

                    // Chuyển trạng thái thanh toán
                    $arrPayment = array('2', '3', '4');
                    if (in_array($params['ispayment'], $arrPayment)) {
                        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
                        $serviceStore = $this->serviceLocator->get('My\Models\Store');
                        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
                        $arrListProductOrders = $serviceProductOrder->getList(array('orde_id' => $params['orderID']));
                        if (!empty($arrListProductOrders)) {
                            foreach ($arrListProductOrders as $arrList) {
                                if ($arrList['updated_store'] == 0) {
                                    $arrDetailStore = $serviceStore->getDetail(array('prod_id' => $arrList['prod_id'], 'store_status' => 1));
                                    if (!empty($arrDetailStore)) {
                                        $intStoreExport = (int) $arrDetailStore['store_export'];
                                        $intProdQuantity = (int) $arrList['prod_quantity'];
                                        $intProvPrice = (int) $arrDetailStore['prov_price'];
                                        $money = ($intStoreExport + $intProdQuantity) * $intProvPrice;
                                        $arrParams = array(
                                            'store_export' => $intStoreExport + $intProdQuantity,
                                            'store_import' => (int) $arrDetailStore['store_import'] - $intProdQuantity,
                                            'prov_total' => $money,
                                            'prov_not_payment' => (int) ($money - $arrDetailStore['prov_payment']),
                                        );
                                        $serviceStore->edit($arrParams, $arrDetailStore['store_id']);
                                        $serviceProductOrder->editToWhere(array('updated_store' => 1), array('prod_id' => $arrList['prod_id'], 'orde_id' => $params['orderID']));
                                    }
                                }
                            }
                        }
                    }
                    /* end update store */
                }
                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại!')));
        }
    }

    public function updateShipingFeeAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $intOrderId = (int) $params['orderID'];
            $intShipingFee = (int) $params['feeShiping'];
            if (empty($intOrderId)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'ID không tồn tại!')));
            }
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');

            $detailOrder = $serviceOrder->getDetail(array('orde_id' => $intOrderId));
            if (empty($detailOrder)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không tìm thấy order này')));
            }
            // Trừ phí vận chuyển cũ từ tổng order
            $totalOrder = $detailOrder["orde_total_price"] - $detailOrder["ship_fee"];
            // Tiến hành tính phí shiping mới
            $totalOrder+=$intShipingFee;
            $result = $serviceOrder->edit(['orde_total_price' => $totalOrder, 'ship_fee' => $intShipingFee], $intOrderId);
            if ($result) {
                // Chi log lại
                $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                $arrOrderLog = array(
                    'user_id' => UID,
                    'orde_id' => $intOrderId,
                    'orde_log_action' => 'Cập nhật phí vận chuyển:' . $intShipingFee . ' ' . General::PREFIX_IN . $intOrderId,
                    'orde_log_created' => time()
                );
                $serviceOrderLog->add($arrOrderLog);
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại!')));
    }

    public function getDetailAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $result = $serviceOrder->getDetail(array('orde_id' => $params['orderID']));
            if (count($result) > 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'detailOrder' => $result)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Có lỗi xảy ra. Vui lòng nhấn F5 thử lại!')));
        }
    }

    public function getListAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $arrListOrders = $serviceOrder->getList(array('user_id' => $params['userID']));
            if (count($arrListOrders) > 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'listOrders' => $arrListOrders)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Không có đơn hàng!')));
        }
    }

    public function addProductOrderAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (!isset($params['proID']) || !isset($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại !')));
            }
            $proID = str_replace(General::PREFIX_IN, "", $params['proID']);
            if (!(int) $proID) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không tìm thấy sản phẩm có mã sản phẩm: ' . $params['proID'])));
            }
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $detailOrder = $serviceOrder->getDetail(array('orde_id' => (int) $params['orderID']));
            if ($detailOrder['is_payment'] == 2) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý , vui lòng thử lại !')));
            }
            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $ProDetail = $serviceProduct->getDetail(array('prod_id' => trim($proID)));
            if (empty($ProDetail)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không tìm thấy sản phẩm có mã sản phẩm: ' . $params['proID'])));
            }
            $listProd = $serviceProductOrder->getList(array('orde_id' => $params['orderID']));
            foreach ($listProd as $key => $listProd) {
                if ($listProd['prod_id'] == trim($proID)) {
                    $arrData = array('prod_quantity' => (int) $listProd['prod_quantity'] + (int) $params['quantity']);
                    $result = $serviceProductOrder->edit($arrData, $listProd['product_order_id']);
                    if ($result) {
                        return $this->getResponse()->setContent(json_encode(array('st' => 1)));
                    }
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại !')));
                }
            }
            $arrData = array(
                'orde_id' => $params['orderID'],
                'prod_id' => $params['proID'],
                'user_id' => UID,
                'prod_name' => $ProDetail['prod_name'],
                'prod_slug' => $ProDetail['prod_slug'],
                'prod_image' => $ProDetail['prod_image'],
                'prod_quantity' => $params['quantity'],
                'prod_price' => 0,
                'total_price' => 0
            );
            $result = $serviceProductOrder->add($arrData);
            if ($result) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại !')));
        }
    }

    public function deleteAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if (empty($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            if (empty($params['comm_content'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Vui lòng nhập lý do hủy đơn hàng !')));
            }
//            if ((int) $params['comm_content']) {
//                switch ($params['comm_content']) {
//                    case 1:
//                        $strCommentCencal = 'Không xác thực được số điện thoại !';
//                        break;
//                    case 2:
//                        $strCommentCencal = 'Không xác thực được địa chỉ !';
//                        break;
//                    case 3:
//                        $strCommentCencal = 'Hết hàng trong kho !';
//                        break;
//                    case 4:
//                        $strCommentCencal = 'Sản phẩm đã ngừng kinh doanh !';
//                        break;
//                    case 5:
//                        $strCommentCencal = 'Nhận được yêu cầu hủy từ quý khách !';
//                        break;
//                }
//            } else {
//                $strCommentCencal = $params['comm_content'];
//            }

            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $arrDetailOrder = $serviceOrder->getDetail(array('orde_id' => $params['orderID']));

            /* update store */
            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $serviceStore = $this->serviceLocator->get('My\Models\Store');
            $arrListProductOrders = $serviceProductOrder->getList(array('orde_id' => $params['orderID']));
            if (!empty($arrListProductOrders)) {
                foreach ($arrListProductOrders as $arrList) {
                    if ($arrList['updated_store'] == 1) {
                        $arrDetailStore = $serviceStore->getDetail(array('prod_id' => $arrList['prod_id'], 'store_status' => 1));
                        if (!empty($arrDetailStore)) {
                            $intStoreExport = (int) $arrDetailStore['store_export'];
                            $intProdQuantity = (int) $arrList['prod_quantity'];
                            $intProvPrice = (int) $arrDetailStore['prov_price'];
                            $money = ($intStoreExport - $intProdQuantity) * $intProvPrice;
                            $arrParams = array(
                                'store_export' => $intStoreExport - $intProdQuantity,
                                'store_import' => (int) $arrDetailStore['store_import'] + $intProdQuantity,
                                'prov_total' => $money,
                                'prov_not_payment' => (int) ($money - $arrDetailStore['prov_payment']),
                            );

                            $serviceStore->edit($arrParams, $arrDetailStore['store_id']);
                            $serviceProductOrder->editToWhere(array('updated_store' => 0), array('prod_id' => $arrList['prod_id'], 'orde_id' => $params['orderID']));
                        }
                    }
                }
            }
            // end update store
            $arrData = array('is_payment' => -1, 'user_change_status' => FULLNAME, 'user_updated' => UID, 'orde_updated' => time(), 'content_cancel' => $params['comm_content']);
            $intResult = $serviceOrder->edit($arrData, $params['orderID']);
            if ($intResult) {
                $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                $arrOrderLog = array(
                    'user_id' => UID,
                    'orde_id' => $params['orderID'],
                    'orde_log_action' => 'Xóa Đơn hàng ' . General::PREFIX_IN . $params['orderID'],
                    'orde_log_created' => time()
                );
                $serviceOrderLog->add($arrOrderLog);

                $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
                $arrOrderNote = array(
                    'note_position' => 'DH[' . General::PREFIX_IN . $params['orderID'] . ']',
                    'note_reason' => $strCommentCencal,
                    'user_id' => UID,
                    'user_name' => USERNAME,
                    'note_created' => time(),
                    'note_type' => '0',
                    'note_status' => 'Đã hủy',
                );
                $serviceNoteOrder->add($arrOrderNote);

                $arrTemplateEmail = array(
                    'content_cancel' => $strCommentCencal,
                    'orde_id' => $params['orderID']
                );
                $view = new ViewModel($arrTemplateEmail);
                $view->setTemplate('mail_cencal/html');
                $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                $content = $viewRender->render($view);
//                p($content);die;
                //Send Mail
                $General = new General();
                //mailto
                $strEmail = json_decode($arrDetailOrder['orde_detail'], true)['email'];
                //tiêu đề
                $strTitle = '[Megavita.Vn] Hủy đơn hàng ' . General::PREFIX_IN . sprintf("%03d", $params['orderID']);
                //Nội dung email
                $strMessage = $content;

                $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                $instanceJobSendMail = new \My\Job\JobSendEmail();
                $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Order',
                    'logs_action' => 'delete',
                    'logs_time' => time(),
                    'logs_detail' => 'Hủy đơn hàng có id = ' . $params['orderID'],
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Hủy đơn hàng hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function updateProductAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['newPrice'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập giá cho sản phẩm !')));
            }
            if (empty($params['newQuantity'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập số lượng sản phẩm !')));
            }

            if (empty($params['order_note'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập lý do chỉnh sửa sản phẩm !')));
            }

            if (empty($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý , vui lòng thử lại !')));
            }
            if (empty($params['product_order_id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý , vui lòng thử lại !')));
            }

            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $detailOrder = $serviceOrder->getDetail(array('orde_id' => (int) $params['orderID']));
            if ($detailOrder['is_payment'] == 2) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý , vui lòng thử lại !')));
            }

            $totalPrice = ((float) $params['newDiscount'] == 0) ? (int) $params['newQuantity'] * (int) $params['newPrice'] : ((int) $params['newQuantity'] * (int) $params['newPrice']) - ((int) $params['newQuantity'] * (int) $params['newPrice'] * (float) $params['newDiscount'] / 100);

            $arrData = array(
                'prod_price' => (int) $params['newPrice'],
                'prod_call_price' => 0,
                'discount' => (float) $params['newDiscount'],
                'prod_quantity' => (int) $params['newQuantity'],
                'orde_note' => $params['order_note'],
                'total_price' => $totalPrice,
                'user_id' => UID,
            );


            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $arrProductOrder = $serviceProductOrder->getDetail(array('product_order_id' => $params['product_order_id']));
            $result = $serviceProductOrder->edit($arrData, $params['product_order_id']);
            if ($result) {
                $arrProductInOrder = $serviceProductOrder->getList(array('orde_id' => (int) $params['orderID']));
                $totalPriceOrder = 0;
                if (!empty($arrProductInOrder)) {
                    foreach ($arrProductInOrder as $value) {
                        $totalPriceOrder = $totalPriceOrder + $value['total_price'];
                    }
                }


                //check voucher
//                $serviceOrder = $this->serviceLocator->get('My\Models\Order');
//                $detailOrder = $serviceOrder->getDetail(array('orde_id' => (int) $params['orderID']));
                $totalPriceRedu = $totalPriceOrder;
                $moneyRedu = $detailOrder['vouc_value'];
                $serviceVoucherLogs = $this->serviceLocator->get('My\Models\VoucherLogs');
                $serviceVoucher = $this->serviceLocator->get('My\Models\Voucher');
                $detailVoucLogs = $serviceVoucherLogs->getDetail(array('orde_id' => (int) $params['orderID']));
                if (!empty($detailVoucLogs) && $detailOrder['vouc_value'] != 0) {
                    $detailVoucher = $serviceVoucher->getDetail(array('vouc_id' => $detailVoucLogs['vouc_id']));
                    if (!empty($detailVoucher)) {
                        $valueVouc = (!empty($detailVoucher['vouc_percent'])) ? $detailVoucher['vouc_percent'] : $detailVoucher['vouc_money'];
                        ($valueVouc < 100) ? $moneyRedu = ($totalPriceOrder * $valueVouc) / 100 : $moneyRedu = $valueVouc;
                        if ($detailVoucher['vouc_type'] == 2) {
                            $availble = $detailVoucher['vouc_value'] - $detailVoucher['vouc_available'];
                            if ($availble < $moneyRedu)
                                $moneyRedu = $availble;
                        }
                        $totalPriceRedu = $totalPriceOrder - $moneyRedu;
                        // edit voucher log
                        if ($detailVoucher['vouc_type'] == 2) {
                            if ($moneyRedu < $detailVoucLogs['vouc_value']) {
                                $afterRedu = $detailVoucLogs['vouc_value'] - $moneyRedu;
                                $temp = $detailVoucher['vouc_available'] - $afterRedu;
                                $serviceVoucher->edit(array('vouc_available' => $temp), $detailVoucher['vouc_id']);
                            } else {
                                $afterRedu = $moneyRedu - $detailVoucLogs['vouc_value'];
                                $temp = $detailVoucher['vouc_available'] + $afterRedu;
                                if ($detailVoucher['vouc_value'] <= $temp) {
                                    setcookie('voucher', '', time() - 86400, '/');
                                    $serviceVoucher->edit(array('vouc_available' => $temp, 'vouc_status' => 1), $detailVoucher['vouc_id']);
                                } else {
                                    $serviceVoucher->edit(array('vouc_available' => $temp), $detailVoucher['vouc_id']);
                                }
                            }
                        }
                        $serviceVoucherLogs->edit(array('vouc_value' => $moneyRedu), $detailVoucLogs['vouc_log_id']);
                    }
                }

                $totalPrice = $totalPriceRedu + $moneyRedu;
                // Tính lại phí vận chuyển cho đơn hàng mỗi khi xóa 1 sản phẩm hoặc thêm 1 sản phẩm vào order
                $serviceShippingFee = $this->serviceLocator->get('My\Models\ShippingFee');
                $arrShipFee = $serviceShippingFee->getDetail(array('city_id' => (int) $detailOrder['city_id'], 'dist_id' => (int) $detailOrder['dist_id']));
                $shipFee = empty($arrShipFee) ? 0 : $arrShipFee["ship_fee"];
                $intShipingFee = \My\Shipping\Domestic::_calculateFeeFromRange($totalPrice, $shipFee);
                $totalPriceRedu+=$intShipingFee;
                // Cập nhật lại đơn hàng
                $serviceOrder = $this->serviceLocator->get('My\Models\Order');
                $serviceOrder->edit(array('orde_total_price' => $totalPriceRedu, 'ship_fee' => $intShipingFee, 'vouc_value' => $moneyRedu, 'user_updated' => UID, 'orde_updated' => time(), 'user_change_status' => FULLNAME), (int) $params['orderID']);

                $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                $arrOrderLog = array(
                    'user_id' => UID,
                    'orde_id' => $params['OrderID'],
                    'orde_log_action' => 'sửa giá sản phẩm product_order_id = ' . $params['product_order_id'] . ' trong Đơn hàng ' . General::PREFIX_IN . $params['OrderID'],
                    'orde_log_created' => time()
                );
                $serviceOrderLog->add($arrOrderLog);

                $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
                $strStatus = NULL;
                if ($arrProductOrder['prod_price'] != $params['newPrice']) {
                    $strStatus .= 'giá: ' . $arrProductOrder['prod_price'] . ' -> ' . $params['newPrice'] . '<br>';
                }
                if ($arrProductOrder['discount'] != $params['newDiscount']) {
                    $strStatus .= 'chiết khấu: ' . $arrProductOrder['discount'] . ' -> ' . $params['newDiscount'] . '<br>';
                }
                if ($arrProductOrder['prod_quantity'] != $params['newQuantity']) {
                    $strStatus .= 'số lượng: ' . $arrProductOrder['prod_quantity'] . ' -> ' . $params['newQuantity'] . '<br>';
                }
                if (!empty($strStatus)) {
                    $strStatus = 'Thay đổi ' . $strStatus;
                }
                $arrOrderNote = array(
                    'note_position' => 'SP[' . $params['product_id'] . '] trong [' . General::PREFIX_IN . $params['orderID'] . ']',
                    'note_reason' => $params['order_note'],
                    'user_id' => UID,
                    'user_name' => USERNAME,
                    'note_created' => time(),
                    'note_type' => '1',
                    'note_status' => $strStatus,
                );
                $serviceNoteOrder->add($arrOrderNote);

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Order',
                    'logs_action' => 'edit',
                    'logs_time' => time(),
                    'logs_detail' => 'Chỉnh sửa đơn hàng có id = ' . $params['prOderID'],
                );
                $serviceLogs->add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Cập nhật thành công !')));
            }
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý , vui lòng thử lại !')));
    }

    public function removeProductAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['product_order_id'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
            }
            if (empty($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
            }
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $arrDetailOrder = $serviceOrder->getDetail(array('orde_id' => $params['orderID']));
            if ($arrDetailOrder) {
                if (in_array($arrDetailOrder['is_payment'], array('2', '3', '4'))) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Bạn không có quyền xóa sản phẩm của đơn hàng này.!')));
                }
            }



            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $result = $serviceProductOrder->del($params['product_order_id']);
            if ($result) {
                $arrProductInOrder = $serviceProductOrder->getList(array('orde_id' => (int) $params['orderID']));
                $totalPriceOrder = 0;
                if (!empty($arrProductInOrder)) {
                    foreach ($arrProductInOrder as $value) {
                        $totalPriceOrder = $totalPriceOrder + $value['total_price'];
                    }
                }

                //check voucher
                $serviceOrder = $this->serviceLocator->get('My\Models\Order');
                $detailOrder = $serviceOrder->getDetail(array('orde_id' => (int) $params['orderID']));
                $totalPriceRedu = $totalPriceOrder;
                $moneyRedu = $detailOrder['vouc_value'];
                $serviceVoucherLogs = $this->serviceLocator->get('My\Models\VoucherLogs');
                $serviceVoucher = $this->serviceLocator->get('My\Models\Voucher');
                $detailVoucLogs = $serviceVoucherLogs->getDetail(array('orde_id' => (int) $params['orderID']));
                if (!empty($detailVoucLogs) && $detailOrder['vouc_value'] != 0) {
                    $detailVoucher = $serviceVoucher->getDetail(array('vouc_id' => $detailVoucLogs['vouc_id']));
                    if (!empty($detailVoucher)) {
                        $valueVouc = (!empty($detailVoucher['vouc_percent'])) ? $detailVoucher['vouc_percent'] : $detailVoucher['vouc_money'];
                        ($valueVouc < 100) ? $moneyRedu = ($totalPriceOrder * $valueVouc) / 100 : $moneyRedu = $valueVouc;
                        if ($detailVoucher['vouc_type'] == 2) {
                            $availble = $detailVoucher['vouc_value'] - $detailVoucher['vouc_available'];
                            if ($availble < $moneyRedu)
                                $moneyRedu = $availble;
                        }
                        $totalPriceRedu = $totalPriceOrder - $moneyRedu;
                        // edit voucher log
                        $beforeRedu = $detailVoucLogs['vouc_value'];
                        $midRedu = $beforeRedu - $moneyRedu;
                        $serviceVoucherLogs->edit(array('vouc_value' => $moneyRedu), $detailVoucLogs['vouc_log_id']);
                        if ($detailVoucher['vouc_type'] == 2) {
                            $temp = $detailVoucher['vouc_available'] - $midRedu;
                            if ($detailVoucher['vouc_value'] <= $temp) {
                                $serviceVoucher->edit(array('vouc_available' => $temp, 'vouc_status' => 1), $detailVoucher['vouc_id']);
                            } else {
                                $serviceVoucher->edit(array('vouc_available' => $temp), $detailVoucher['vouc_id']);
                            }
                        }
                    }
                }

                // Giá sản phẩm nguyên gốc khi chưa khuyến mãi
                $totalPrice = $totalPriceRedu + $moneyRedu;
                // Tính lại phí vận chuyển cho đơn hàng mỗi khi xóa 1 sản phẩm hoặc thêm 1 sản phẩm vào order
                $serviceShippingFee = $this->serviceLocator->get('My\Models\ShippingFee');
                $arrShipFee = $serviceShippingFee->getDetail(array('city_id' => (int) $detailOrder['city_id'], 'dist_id' => (int) $detailOrder['dist_id']));
                $shipFee = empty($arrShipFee) ? 0 : $arrShipFee["ship_fee"];
                $intShipingFee = \My\Shipping\Domestic::_calculateFeeFromRange($totalPrice, $shipFee);
                $totalPriceRedu+=$intShipingFee;

                //end check
                $serviceOrder->edit(array('orde_total_price' => $totalPriceRedu, 'ship_fee' => $intShipingFee, 'vouc_value' => $moneyRedu, 'user_updated' => UID, 'orde_updated' => time(), 'user_change_status' => FULLNAME), (int) $params['orderID']);
                $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                $arrOrderLog = array(
                    'user_id' => UID,
                    'orde_id' => $params['OrderID'],
                    'orde_log_action' => 'Xóa sản phẩm product_order_id = ' . $params['product_order_id'] . ' trong Đơn hàng ' . General::PREFIX_IN . $params['OrderID'],
                    'orde_log_created' => time()
                );
                $serviceOrderLog->add($arrOrderLog);

                $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
                $arrOrderNote = array(
                    'note_position' => 'DH[' . $params['orderID'] . ']',
                    'note_reason' => $params['orderNote'],
                    'user_id' => UID,
                    'user_name' => USERNAME,
                    'note_created' => time(),
                    'note_type' => '1',
                    'note_status' => 'Xóa sản phẩm trong DH[' . $params['orderID'] . ']',
                );
                $serviceNoteOrder->add($arrOrderNote);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa sản phẩm khỏi giỏ hàng thành công !')));
            }

            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }
    }

    public function getOrderForChartAction() {
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $arrOrder = $serviceOrder->getOrderForChart(1, 15);
        $listOrder = [];
        $listOrderIsPay = [];
        $listDate = [];
        foreach ($arrOrder as $order) {
            $listDate[] = $order['date'];
            $listOrder[] = (int) $order['Num'];
            $listOrderIsPay[] = (int) $order['is_pay'];
        }
        $serial = array(array("name" => "Tổng đơn hàng", "data" => $listOrder), array("name" => "Đã thanh toán", "data" => $listOrderIsPay));
        $data = array("serial" => $serial, "cate" => $listDate);
        return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'data' => $data)));
    }

    public function printOrderAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        if (!isset($params['id'])) {
            return $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'index'));
        }
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $arrDetailOrder = $serviceOrder->getDetail(array('orde_id' => $params['id']));
        $arrProducOrder = $serviceProductOrder->getList(array('orde_id' => $params['id']));
        $arrUser = json_decode($arrDetailOrder['orde_detail']);
        return array(
            'arrUser' => $arrUser,
            'arrProducOrder' => $arrProducOrder,
            'arrDetailOrder' => $arrDetailOrder,
        );
    }

    public function printBarcodeAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        if (!isset($params['id'])) {
            return $this->redirect()->toRoute('backend', array('controller' => 'order', 'action' => 'index'));
        }
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $arrDetailOrder = $serviceOrder->getDetail(array('orde_id' => $params['id']));
        $arrUser = json_decode($arrDetailOrder['orde_detail']);

        /** make barcode** */
        $im = imagecreatetruecolor(100, 70);
        $black = ImageColorAllocate($im, 0x00, 0x00, 0x00);
        $white = ImageColorAllocate($im, 0xff, 0xff, 0xff);
        imagefilledrectangle($im, 0, 0, 100, 100, $white);
        $data = Barcode::gd($im, $black, 50, 35, 0, "code128", $arrDetailOrder['orde_id'], 2, 50);
        $box = imagettfbbox($fontSize, 0, $font, $data['hri']);
        $len = $box[2] - $box[0];
        Barcode::rotate(-$len / 2, ($data['height'] / 2) + $fontSize + $marge, $angle, $xt, $yt);
        $img = imagettftext($im, $fontSize, $angle, $x + $xt, $y + $yt, $blue, $font, $data['hri']);
        $r = imagepng($im, STATIC_PATH . '/uploads/barcode/' . General::PREFIX_IN . $arrDetailOrder['orde_id'] . '.png');
        /** end barcode * */
        return array(
            'arrUser' => $arrUser,
            'arrDetailOrder' => $arrDetailOrder
        );
    }

    public function delOrderAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Lỗi CMNR !')));
            }
            $intOrderID = (int) $params['orderID'];
//            p($intOrderID);die;
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $intResult = $serviceOrder->delOrder($intOrderID);
            if ($intResult) {
                $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
                $serviceProductOrder->del($intOrderID);
                $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
                $arrOrderLog = array(
                    'user_id' => UID,
                    'orde_id' => $intOrderID,
                    'orde_log_action' => 'Xóa Đơn hàng ' . General::PREFIX_IN . $intOrderID . ' đồng thời xóa toàn bộ sản phẩm trong Đơn hàng ' . General::PREFIX_IN . $intOrderID,
                    'orde_log_created' => time()
                );
                $serviceOrderLog->add($arrOrderLog);
//
//                $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
//                $arrOrderNote = array(
//                    'user_id' => UID,
//                    'user_name' => USERNAME,
//                    'note_position' => 'Chỉnh sửa đơn hàng (Xóa đơn hàng) ' . General::PREFIX_IN . $intOrderID . ' đồng thời xóa toàn bộ sản phẩm trong Đơn hàng ' . General::PREFIX_IN . $intOrderID,
//                    'note_created' => time()
//                );
//                $serviceNoteOrder->add($arrOrderNote);

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Order',
                    'logs_action' => 'delOrder',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Đơn hàng ' . General::PREFIX_IN . $intOrderID . ' đồng thời xóa toàn bộ sản phẩm trong Đơn hàng ' . General::PREFIX_IN . $intOrderID
                );
                $serviceLogs->add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa đơn hàng thành công!')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Lỗi CMNR !')));
        }
    }

    /* edit products order */

    public function editProductOrderAction() {
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $arrCondition = array(
                'orde_id' => $params['orderID'],
                'prod_id' => $params['prodID'],
            );
            $data = array();
            if (isset($params['status'])) {
                $data += array('prod_status' => $params['status']);
            }
            $result = $serviceProductOrder->edit2($data, $arrCondition);
            if ($result) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Cập nhật thành công!')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Có lỗi xảy ra trong quá trình xử lý!')));
            die();
        }
    }

    public function productAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');

        $arrCondition = ['is_payment' => 0];
        if (!empty($params['date_from'])) {
            list($day, $month, $year) = explode('/', $params['date_from']);
            $dateFrom = mktime(0, 0, 0, $month, $day, $year);
            $arrCondition['date_from'] = $dateFrom;
        }
        if (!empty($params['date_to'])) {
            list($day, $month, $year) = explode('/', $params['date_to']);
            $dateTo = mktime(23, 59, 59, $month, $day, $year);
            $arrCondition['date_to'] = $dateTo;
        }

        $arrOrders = $serviceOrder->getList($arrCondition);

        $arrCondition = [];
        if (!empty($arrOrders)) {
            $listOrders = '';
            foreach ($arrOrders as $value) {
                $listOrders .= $value['orde_id'] . ',';
            }
            $listOrders = rtrim($listOrders, ',');
            $arrCondition = array(
                'list_orde_id' => $listOrders,
                'both_name_id_like' => General::clean(trim($this->params()->fromQuery('s')))
            );

            $paramsQuery = $this->params()->fromQuery();
            $arrCondition["prod_status"] = empty($paramsQuery['status']) ? "" : $paramsQuery['status'];

            //p($arrCondition);die();
            $intTotal = $serviceProductOrder->getTotal($arrCondition);
            $arrProd = $serviceProductOrder->getListLimit($arrCondition, $intPage, $intLimit, 'orde_id DESC');
            //p($arrProd);die;
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend-productorder-search', $params);
        return array(
            'params' => $params,
            'arrProd' => $arrProd,
            'paging' => $paging
        );
    }

    public function productFullAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');

        if (!empty($params['date_from'])) {
            list($day, $month, $year) = explode('/', $params['date_from']);
            $dateFrom = mktime(0, 0, 0, $month, $day, $year);
            $arrCondition['date_from'] = $dateFrom;
        }
        if (!empty($params['date_to'])) {
            list($day, $month, $year) = explode('/', $params['date_to']);
            $dateTo = mktime(23, 59, 59, $month, $day, $year);
            $arrCondition['date_to'] = $dateTo;
        }
        if (!empty($params['is_payment'])) {
            $arrCondition["arr_is_payment"] = $params['is_payment'];
        }

        $arrOrders = $serviceOrder->getList($arrCondition);

        $arrCondition = [];
        if (!empty($arrOrders)) {
            $listOrders = '';
            foreach ($arrOrders as $value) {
                $listOrders .= $value['orde_id'] . ',';
            }

            $listOrders = rtrim($listOrders, ',');
            $arrCondition = array(
                'list_orde_id' => $listOrders,
                'both_name_id_like' => General::clean(trim($this->params()->fromQuery('s')))
            );


            $paramsQuery = $this->params()->fromQuery();
            $params['status'] = $paramsQuery['status'];
            $arrCondition["prod_status"] = empty($params['status']) ? "" : $params['status'];

            //p($arrCondition);die();
            $intTotal = $serviceProductOrder->getTotalGroup($arrCondition);
            $arrProd = $serviceProductOrder->getListLimitGroup($arrCondition, $intPage, $intLimit, 'orde_id DESC');
            $getGroupSum = $serviceProductOrder->getGroupSum($arrCondition);
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend-productorder-search', $params);
        return array(
            'params' => $params,
            'arrProd' => $arrProd,
            'getGroupSum' => $getGroupSum,
            'paging' => $paging
        );
    }

    public function editStatusProdOrderAction() {
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = $serviceProductOrder->edit(array('prod_status' => $params['status']), $params['id']);
            if ($result) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Cập nhật thành công!')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Có lỗi xảy ra trong quá trình xử lý!')));
            die();
        }
    }

    public function noteOrderAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = $this->params()->fromRoute('limit', 15);
        $route = 'backend-note-search';
        $arrCondition = array();
        if (!empty($params['date_from'])) {
            list($day, $month, $year) = explode('/', $params['date_from']);
            $dateFrom = mktime(0, 0, 0, $month, $day, $year);
            $arrCondition['date_from'] = $dateFrom;
        }
        if (!empty($params['date_to'])) {
            list($day, $month, $year) = explode('/', $params['date_to']);
            $dateTo = mktime(0, 0, 0, $month, $day + 1, $year);
            $arrCondition['date_to'] = $dateTo;
        }
        if (!empty($params['s'])) {
            $arrCondition['note_position'] = $params['s'];
        }
        //if (!empty($params['note_type'])) {
        $arrCondition['note_type'] = $params['note_type'];
        // }
        $serviceNoteOrder = $this->serviceLocator->get('My\Models\NoteOrder');
        $arrNoteOrder = $serviceNoteOrder->getListLimit($arrCondition, $intPage, $intLimit, 'note_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrNoteOrder' => $arrNoteOrder,
        );
    }

    public function createorderAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = array_merge($params, $this->params()->fromPost());
            if (!isset($params['orderID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Có lỗi xảy ra trong quá trình xử lý!')));
            }
            $serviceOrder = $this->serviceLocator->get('My\Models\Order');
            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $arrOrder = $serviceOrder->getDetail(array('orde_id' => $params['orderID']));
            if ($arrOrder) {
                $arrDataOrder = array(
                    'orde_detail' => $arrOrder['orde_detail'],
                    'orde_phone' => $arrOrder['orde_phone'],
                    'orde_created' => time(),
                    'user_id' => UID,
                    'is_acp' => (int) IS_ACP,
                    'is_payment' => 0,
                    'orde_ship' => $arrOrder['orde_ship'],
                    'orde_total_price' => $arrOrder['orde_total_price'] - ($arrOrder['vouc_value'] ? $arrOrder['vouc_value'] : 0),
                    'orde_updated' => $arrOrder['orde_updated'],
                    'user_change_status' => $arrOrder['user_change_status'],
                    'orde_payment' => $arrOrder['orde_payment'],
                    'city_id' => $arrOrder['city_id'],
                    'dist_id' => $arrOrder['dist_id'],
                    'ship_info' => $arrOrder['ship_info'],
                    'orde_code' => $arrOrder['orde_code'],
                    'user_updated' => $arrOrder['user_updated'],
                    'content_cancel' => $arrOrder['content_cancel'],
                    'user_fullname' => $arrOrder['user_fullname'],
                    'user_phone' => $arrOrder['user_phone'],
                    'user_email' => $arrOrder['user_email'],
                    'ship_fee' => $arrOrder['ship_fee'],
                    'vouc_value' => 0,
                );
                $result = $serviceOrder->add($arrDataOrder);
                $arrProductOrder = $serviceProductOrder->getList(array('orde_id' => $params['orderID']));
                if (count($arrProductOrder) > 0) {
                    foreach ($arrProductOrder as $arrProductOrder) {
                        $arrProductData = array(
                            'orde_id' => $result,
                            'prod_id' => $arrProductOrder['prod_id'],
                            'user_id' => $arrProductOrder['user_id'],
                            'prod_name' => $arrProductOrder['prod_name'],
                            'prod_slug' => $arrProductOrder['prod_slug'],
                            'prod_image' => $arrProductOrder['prod_image'],
                            'prod_quantity' => $arrProductOrder['prod_quantity'],
                            'prod_price' => $arrProductOrder['prod_price'],
                            'prod_call_price' => $arrProductOrder['prod_call_price'],
                            'discount' => $arrProductOrder['discount'],
                            'orde_note' => $arrProductOrder['orde_note'],
                            'total_price' => $arrProductOrder['total_price'],
                            'prod_status' => $arrProductOrder['prod_status'],
                        );
                        $serviceProductOrder->add($arrProductData);
                    }
                }
                if ($result) {
                    return $this->getResponse()->setContent(json_encode(array('st' => 1, 'URL' => BASE_URL . '/backend/order/view/id/' . $result)));
                }
                return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Có lỗi xảy ra trong quá trình xử lý!')));
            }
        }
    }

    /* danh sach don hang theo user_id */

    public function listOrderAction() {
        $params = $this->params()->fromRoute();
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        if (!isset($params['id'])) {
            return;
        }
        $arrCondition = array(
            'user_id' => $params['id'],
            'strListStatus' => '3,4,2',
        );
        $arrListOrder = $serviceOrder->getList($arrCondition);
        if (!empty($arrListOrder)) {
            $arrListProductOrder = array();
            $strProdID = '';
            foreach ($arrListOrder as $arrLO) {
                $arrListProductOrder[$arrLO['orde_id']] = $serviceProductOrder->getList(array('orde_id' => $arrLO['orde_id']));
                $arrlistProdOrder = $serviceProductOrder->getList(array('orde_id' => $arrLO['orde_id']));
                foreach ($arrlistProdOrder as $value) {
                    $strProdID .= $value['prod_id'] . ',';
                }
            }
            //p($strProdID);die();
            $arrProducts = $serviceProduct->getList(array('listProductID' => rtrim($strProdID, ',')));
            foreach ($arrProducts as $arrProduct) {
                $arrListProduct[$arrProduct['prod_id']] = $arrProduct['prod_code'];
            }
        }
        $arrInfoUser = $serviceUser->getDetail(array('user_id' => $params['id']));
        $intTotalOrder = $serviceOrder->getTotalUserBought(array('user_id' => $params['id']));

        return array(
            'params' => $params,
            'arrListOrder' => $arrListOrder,
            'arrListProduct' => $arrListProduct,
            'arrListProductOrder' => $arrListProductOrder,
            'arrInfoUser' => $arrInfoUser,
            'intTotalOrder' => $intTotalOrder,
        );
    }

}
