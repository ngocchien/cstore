<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate,
    Zend\View\Model\ViewModel;

class OrderController extends MyController {
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceOrder \My\Models\Order */
    /* @var $serviceProductOrder \My\Models\ProductOrder */
    /* @var $serviceAdvisory \My\Models\Advisory */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:order:index' => 'insilde.js',
            ];
            $this->externalJS = [
                'frontend:order:index' => STATIC_URL . '/f/v1/js/my/??order.js',
            ];
        }

        if (FRONTEND_TEMPLATE == 'mobile') {
            $this->externalJS = [
                'frontend:order:view-cart' => STATIC_URL . '/f/mobile/js/my/??order.js',
            ];
        }
    }

    public function indexAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $s = General::clean(trim($params['s']));
        $intLimit = 15;
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $arrConditions = array('user_id' => UID);

        if ($s) {
            if (is_numeric($s))
                $getDetailtOrder = $serviceOrder->getDetail(array('orde_id' => $s));
            else
                $getDetailtOrder = $serviceOrder->getDetail(array('orde_id' => (int) substr($s, 2)));

            if (!$getDetailtOrder) {
                $this->flashMessenger()->setNamespace('order-error')->addMessage('Không tồn tại mã đơn hàng ' . $s);
                return $this->redirect()->toRoute('frontend-order', array('controller' => 'order', 'action' => 'index'));
            }

            if (strtoupper(substr($s, 0, 2)) == $getDetailtOrder['orde_code']) {
                $s = substr($s, 2);
            }
            $arrConditions = array('user_id' => UID, 'orde_id' => (int) $s);
        }

        $intTotal = $serviceOrder->getTotal($arrConditions);
        $countPage = ceil($intTotal / $intLimit);
        $arrOrderList = $serviceOrder->getListLimit($arrConditions, $intPage, $intLimit, 'orde_created DESC');
        $arrProductOrder = $serviceProductOrder->getList(array('user_id' => UID));
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'frontend-order', array('controller' => 'order', 'action' => 'index', 'page' => $intPage));

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Danh sách đơn hàng ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Danh sách đơn hàng của bạn tại Megavita !'));

        return array(
            'arrOrderList' => $arrOrderList,
            'arrProductOrder' => $arrProductOrder,
            'paging' => $paging,
            'params' => $params,
            'intPage' => $intPage,
            'countPage' => $countPage
        );
    }

    public function addProductCartAction() {

        $params = $this->params()->fromPost();

        if (empty($params)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/>')));
        }

        if (empty($params['ProductId'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/>')));
        }

        if (empty($params['Quantity'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/>')));
        }

        $producID = (int) $params['ProductId'];
        $quantity = (int) $params['Quantity'];
        $viewCart = array(
            'prod_id' => $producID,
            'quantity' => $quantity,
        );

        if (!isset($_COOKIE['cookieCart'])) {
            setcookie('cookieCart', serialize(array($producID => $viewCart)), time() + 604800, "/");
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }

        if (isset($_COOKIE['cookieCart'])) {
            if (!in_array(unserialize($_COOKIE['cookieCart'])[$producID], unserialize($_COOKIE['cookieCart']))) {
                $arrProductList = unserialize($_COOKIE['cookieCart']);
                $arrProductList[$producID] = $viewCart;
                setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            } else {
                $arrProductList = unserialize($_COOKIE['cookieCart']);
                $arrProductList[$producID]['quantity'] = $arrProductList[$producID]['quantity'] + $quantity;
                if ($arrProductList[$producID]['quantity'] > 20) {
                    $arrProductList[$producID]['quantity'] = 20;
                }
                setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }

        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không thể thêm sản phẩm vào giỏ hàng !')));
    }

    public function showCartAction() {
        $params = $this->params()->fromRoute();
        $arrProductListCookie = unserialize($_COOKIE['cookieCart']);

        if (empty($arrProductListCookie)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<br/><br/><b>Không có sản phẩm trong giỏ hàng !</b><br/><br/><br/>', 'total' => 0, 'quantity' => 0)));
        }

        foreach ($arrProductListCookie as $key => $value) {
            $listId[] = $key;
        }

        if (count($listId) <= 0) {
            return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => '<br/><br/><b>Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !</b><br/><br/><br/>')));
        }
        if (count($listId) > 0) {
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $strListId = implode(',', $listId);
            $arrCondition = array('listProductID' => $strListId);
            $listProductCart = $serviceProduct->getList($arrCondition);
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('listProductID' => $listId));
//            $listProductCart = $instanceSearchProduct->getList();
            
            $arrData = array(
                'arrProductListCookie' => $arrProductListCookie,
                'listProductCart' => $listProductCart,
            );

            foreach ($listProductCart as $value) {
                $listProductCartFormat[$value['prod_id']] = $value;
            }

            $totalProduct = count($arrProductListCookie);
            $quantityProduct = 0;
            $totalPrice = 0;
            foreach ($arrProductListCookie as $keyProduct => $valueProduct) {
                $totalPrice = ($listProductCartFormat[$valueProduct['prod_id']]['prod_is_promotion'] == 1) ? $totalPrice + ($listProductCartFormat[$valueProduct['prod_id']]['prod_promotion_price'] * $valueProduct['quantity'] ) : ($totalPrice + $listProductCartFormat[$valueProduct['prod_id']]['prod_price'] * $valueProduct['quantity']);
                $quantityProduct = $quantityProduct + $valueProduct['quantity'];
            }
            if (FRONTEND_TEMPLATE == 'mobile') {
                $totalPrice = number_format($totalPrice, 0, ",", ".") . ' VNĐ';
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'totalPrice' => $totalPrice)));
            }
            $view = new ViewModel($arrData);
            $view->setTemplate('frontend/order/show-cart');
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $content = $viewRender->render($view);
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content, 'total' => $totalProduct, 'quantity' => $quantityProduct)));
        }
        die();
    }

    public function removeProductCartAction() {

        $params = $this->params()->fromPost();

        if (empty($params['ProductId'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }

        $producID = (int) $params['ProductId'];
        $arrProductList = unserialize($_COOKIE['cookieCart']);

        if (!in_array($arrProductList[$producID], $arrProductList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }

        if (in_array($arrProductList[$producID], $arrProductList)) {
            unset($arrProductList[$producID]);
            if (count($arrProductList) == 0) {
                setcookie('cookieCart', '', time() - 3600, "/");
                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }
            setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }

        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Đã xóa sản phẩm khỏi giỏ hàng !')));
        die();
    }

    public function updateCartAction() {
        $params = $this->params()->fromPost();
        if (empty($params['ProductId']) || empty($params['Quantity'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        $producID = (int) $params['ProductId'];
        $quantity = (int) $params['Quantity'];

        $arrProductList = unserialize($_COOKIE['cookieCart']);

        if (!in_array($arrProductList[$producID], $arrProductList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        if (in_array($arrProductList[$producID], $arrProductList)) {
            $arrProductList[$producID]['quantity'] = $quantity;
            setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }

        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
    }

    public function updateCartInCheckoutAction() {
        $params = $this->params()->fromPost();

        if (empty($params['ProductId']) || empty($params['Quantity'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        $producID = (int) $params['ProductId'];
        $quantity = (int) $params['Quantity'];

        $arrProductList = unserialize($_COOKIE['cookieCart']);

        if (!in_array($arrProductList[$producID], $arrProductList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        if (in_array($arrProductList[$producID], $arrProductList)) {
            $arrProductList[$producID]['quantity'] = $quantity;
            setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            $totalProduct = count($arrProductList);
            $quantityProduct = 0;

            foreach ($arrProductList as $key => $value) {
                $quantityProduct = $quantityProduct + $value['quantity'];
                $listId[] = $key;
            }

            if (count($listId) <= 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => 0, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
            }

            if (count($listId) > 0) {
                $serviceProduct = $this->serviceLocator->get('My\Models\Product');
                $strListId = implode(',', $listId);
                $arrCondition = array('listProductID' => $strListId);
                $listProductCart = $serviceProduct->getList($arrCondition);
//                $instanceSearchProduct = new \My\Search\Products();
//                $instanceSearchProduct->setParams(array('listProductID' => $listId));
//                $listProductCart = $instanceSearchProduct->getList();

                $arrDetailProduct = array();
                $totalPrice = 0;

                foreach ($listProductCart as $key => $value) {
                    $arrDetailProduct[$value['prod_id']] = $value;
                    $totalPrice = ($value['prod_is_promotion'] == 1) ? $totalPrice + $value['prod_promotion_price'] * $arrProductList[$value['prod_id']]['quantity'] : $totalPrice + $value['prod_price'] * $arrProductList[$value['prod_id']]['quantity'];
                }

                $totalPriceProduct = ($arrDetailProduct[$producID]['prod_is_promotion'] == 1) ? $arrDetailProduct[$producID]['prod_promotion_price'] * $quantity : $arrDetailProduct[$producID]['prod_price'] * $quantity;
                //Xét voucher
                $totalPriceRedu = $totalPrice;
                if (!empty($_COOKIE['voucher'])) {
                    $serviceVoucher = $this->serviceLocator->get('My\Models\Voucher');
                    $detailVoucher = $serviceVoucher->getDetail(array('vouc_code' => $_COOKIE['voucher']));
                    if (!empty($detailVoucher)) {
                        (!empty($detailVoucher['vouc_percent'])) ? $valueVouc = $detailVoucher['vouc_percent'] : $valueVouc = $detailVoucher['vouc_money'];
                        ($valueVouc < 100) ? $moneyRedu = ($totalPrice * $valueVouc) / 100 : $moneyRedu = $valueVouc;
                        if ($detailVoucher['vouc_type'] == 2) {
                            $availble = $detailVoucher['vouc_value'] - $detailVoucher['vouc_available'];
                            if ($availble < $moneyRedu)
                                $moneyRedu = $availble;
                        }
                        $totalPriceRedu = $totalPrice - $moneyRedu;
                    }
                }
                $arrData = array(
                    'totalPriceProduct' => number_format($totalPriceProduct, 0, ",", ".") . 'VNĐ',
                    'totalProduct' => $totalProduct,
                    'quantityProduct' => $quantityProduct,
                    'totalPrice' => number_format($totalPrice, 0, ",", ".") . 'VNĐ',
                    'totalPriceRedu' => number_format($totalPriceRedu, 0, ",", ".") . 'VNĐ',
                    'moneyRedu' => number_format($moneyRedu, 0, ",", ".") . 'VNĐ',
                );
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrData)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
    }

    public function addAction() {
        
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id']) || UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params['id'] = (int) $params['id'];
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $detailOrder = $serviceOrder->getDetail(array('orde_id' => $params['id'], 'user_id' => UID));

        if (!$detailOrder) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $arrCondition = array('orde_id' => $params['id'], 'user_id' => UID);
        $arrProductOrderList = $serviceProductOrder->getList($arrCondition);
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');

        $listProductID = '';
        foreach ($arrProductOrderList as $val) {
            $listProductID .= $val['prod_id'] . ',';
        }
        $listProductID = rtrim($listProductID, ',');
        $arrDetailProduct = $serviceProduct->getList(array('listProductID' => $listProductID));
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('listProductID' => explode(',',$listProductID)));
//        $arrDetailProduct = $instanceSearchProduct->getList();
        
        $arrListCallPrice = array();
        $totalprice = 0;
        foreach ($arrProductOrderList as $value) {
            $arrListCallPrice[] = $value['prod_call_price'];
            $totalprice = $totalprice + $value['total_price'];
        }
        $userInfo = json_decode($detailOrder['orde_detail'], true);

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'frontend', array('controller' => 'order', 'action' => 'view', 'page' => $intPage, 'id' => $params['id']));
        return array(
            'paging' => $paging,
            'detailOrder' => $detailOrder,
            'params' => $params,
            'userInfo' => $userInfo,
            'arrProductOrderList' => $arrProductOrderList,
            'arrDetailProduct' => $arrDetailProduct,
        );
    }

    public function viewCartAction() {
        $params = $this->params()->fromRoute();

        $arrProductListCookie = unserialize($_COOKIE['cookieCart']);

        if (empty($arrProductListCookie)) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        foreach ($arrProductListCookie as $key => $value) {
            $listId[] = $key;
        }
        if (count($listId) <= 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $strListId = implode(',', $listId);
        $arrCondition = array('listProductID' => $strListId);
        $listProductCart = $serviceProduct->getList($arrCondition);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('listProductID' => $listId));
//        $listProductCart = $instanceSearchProduct->getList();

        if ($this->request->isPost()) {
            return $this->redirect()->toRoute('checkout', array('controller' => 'checkout', 'action' => 'index'));
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Giỏ hàng - Thông tin giỏ hàng ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Giỏ hàng - Thông tin giỏ hàng !'));

        return array(
            'arrProductListCookie' => $arrProductListCookie,
            'listProductCart' => $listProductCart,
        );
    }

    public function updateCartMobileAction() {
        $params = $this->params()->fromPost();
        if (empty($params['ProductId']) || empty($params['Quantity'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        $producID = $params['ProductId'];
        $quantity = $params['Quantity'];

        $arrProductList = unserialize($_COOKIE['cookieCart']);

        if (!in_array($arrProductList[$producID], $arrProductList)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
        }

        if (in_array($arrProductList[$producID], $arrProductList)) {
            $arrProductList[$producID]['quantity'] = $quantity;
            setcookie('cookieCart', serialize($arrProductList), time() + 604800, "/");
            foreach ($arrProductList as $key => $value) {
                $listId[] = $key;
            }

            if (count($listId) <= 0) {
                return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
            }

            if (count($listId) > 0) {
                $serviceProduct = $this->serviceLocator->get('My\Models\Product');
                $strListId = implode(',', $listId);
                $arrCondition = array('listProductID' => $strListId);
                $listProductCart = $serviceProduct->getList($arrCondition);
//                $instanceSearchProduct = new \My\Search\Products();
//                $instanceSearchProduct->setParams(array('listProductID' => $listId));
//                $listProductCart = $instanceSearchProduct->getList();
                
                $totalPrice = 0;
                foreach ($listProductCart as $value) {
                    ($value['prod_is_promotion'] == 1) ? $totalPrice = $totalPrice + ($value['prod_promotion_price'] * $arrProductList[$value['prod_id']]['quantity'] ) : $totalPrice = $totalPrice + ($value['prod_price'] * $arrProductList[$value['prod_id']]['quantity']);
                }
                $dataReturn = array(
                    'totalPrice' => number_format($totalPrice, 0, ",", ".") . ' Vnđ',
                );
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $dataReturn)));
            }
        }

        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý!')));
    }

    public function advisoryAction() {
        $params = $this->params()->fromPost();
//        p($params);die;
        if (empty($params)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/></center>')));
        }

        if (empty($params['ProductId'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/></center>')));
        }

        if (empty($params['phoneAdvisory'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Số điện thoại không được bỏ trống!</b><br/><br/></center>')));
        }

        $strPhoneNumber = trim($params['phoneAdvisory']);

        $validator = new Validate();
        if (!$validator->Digits($strPhoneNumber)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Số điện thoại phải là số! Từ 8 đến 11 số!</b><br/><br/></center>')));
        }
        if (!$validator->Between(strlen($strPhoneNumber), 8, 11)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Số điện thoại phải là số! Từ 8 đến 11 số!</b><br/><br/></center>')));
        }

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetailProduct = $serviceProduct->getDetail(array('prod_id' => $params['ProductId'], 'prod_status' => 1));
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_id' => $params['ProductId'], 'prod_status' => 1));
//        $arrDetailProduct = $instanceSearchProduct->getDetail();

        if (empty($arrDetailProduct)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Sản phẩm không tồn tại hoặc đã ngừng kinh doanh !</b><br/><br/></center>')));
        }

        $arrData = array(
            'advi_phone' => $strPhoneNumber,
            'prod_id' => $arrDetailProduct['prod_id'],
            'prod_name' => $arrDetailProduct['prod_name'],
            'prod_slug' => $arrDetailProduct['prod_slug'],
            'advi_created' => time()
        );
        $serviceAdvisory = $this->serviceLocator->get('My\Models\Advisory');
        $intTotal = $serviceAdvisory->getTotal(array('advi_phone' => $strPhoneNumber, 'prod_id' => $arrDetailProduct['prod_id'], 'advi_status' => 0));
        if ($intTotal > 0) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Yêu cầu của bạn đã được ghi nhận! Điện thoại viên sẽ liên hệ với bạn trong giây lát !</b><br/><br/></center>')));
        }
        $inResult = $serviceAdvisory->add($arrData);
        if ($inResult > 0) {
            $General = new General();
            $strTitle = '[Megavita.Vn] Yêu cầu hỗ trợ tư vấn sản phẩm !';
            $strMessage = '<h3>Yêu cầu hỗ trợ tư vấn sản phẩm!</h3>'
                    . 'Nhận được yêu cầu cần được hỗ trợ, tư vấn từ Số điện thoại : ' . $strPhoneNumber . ' về sản phẩm : ' . $arrDetailProduct['prod_name'] . ''
                    . '<br/> Mời đăng nhập vào phần Quản trị để xem thông tin chi tiết !';
            $strEmail = 'megavita.vn@gmail.com';
            //$result = $General->sendMail($strEmail, $strTitle, $strMessage);
            $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
            $instanceJobSendMail = new \My\Job\JobSendEmail();
            $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center><br/><br/><b>Yêu cầu của bạn đã được ghi nhận! Điện thoại viên sẽ liên hệ với bạn trong giây lát !</b><br/><br/></center>')));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center><br/><br/><b>Xảy ra lỗi trong quá trình xử lý !</b><br/><br/></center>')));
    }

    public function voucherAction() {
        $params = $this->params()->fromPost();
        if(UID != 0){
            if (!empty($params['code'])) {
                $serviceVoucher = $this->serviceLocator->get('My\Models\Voucher');
                $detailVoucher = $serviceVoucher->getDetail(array('vouc_code' => $params['code'], 'time' => time()));
                if (!empty($detailVoucher)) {
                    if ($detailVoucher['vouc_status'] == 0) {
                        if (empty($detailVoucher['user_id'])) {
                            $serviceVoucher->edit(array('user_id' => UID), $detailVoucher['vouc_id']);
                            setcookie("voucher", $detailVoucher['vouc_code'], time() + 86400, "/");
                            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center>Nhập mã voucher thành công</center>')));
                        }
                        if ($detailVoucher['user_id'] == UID) {
                            setcookie("voucher", $detailVoucher['vouc_code'], time() + 86400, "/");
                            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center>Nhập mã voucher thành công</center>')));
                        }
                        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mã voucher đã được sử dụng</center>')));
                    }
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mã voucher đã được sử dụng</center>')));
                }
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mã voucher không xác định hoặc đã hết hạn</center>')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập mã voucher</center>')));           
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng đăng nhập để sử dụng mã voucher</center>')));
    }

    public function delVoucherAction() {
        setcookie("voucher", "", time() - 86400, "/");
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center>Xóa voucher thành công</center>')));
    }

}
