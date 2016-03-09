<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class OrdertrackingController extends MyController {
    /* @var $serviceProductOrder \My\Models\ProductOrder */
    /* @var $serviceOrder \My\Models\Order */

    public function __construct() {
        $this->externalJS = [
            'frontend:ordertracking:index' => STATIC_URL . '/f/v1/js/my/??ordertracking.js',
        ];
        $this->defaultCSS = [
            'frontend:ordertracking:index' => 'style.css',
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'view', 'id' => $params['id'], 'email' => $params['email']));
        }
        return array(
            'params' => $params,
        );
    }

    public function viewAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $strOrderID = General::clean($params['id']);
        $strEmail = General::clean($params['email']);
        //p($strEmail);die;
        $validator = new Validate();

        if (empty($strOrderID) || empty($strEmail)) {
            $this->flashMessenger()->setNamespace('ordertracking-error')->addMessage('Vui lòng nhập đầy đủ thông tin.');
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'index'));
        }

        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $order_id = (int)substr($strOrderID, 2);
        $getDetailOrder = $serviceOrder->getDetail(array('orde_id' => $order_id));
//        p($getDetailOrder['orde_code']);die();
        if (substr($strOrderID, 0, 2) != $getDetailOrder['orde_code']) {
            $this->flashMessenger()->setNamespace('ordertracking-error')->addMessage('Mã hóa đơn không đúng quy định.');
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'index'));
        }

        if (!is_numeric($order_id)) {
            $this->flashMessenger()->setNamespace('ordertracking-error')->addMessage('Mã hóa đơn không đúng.');
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'index'));
        }

        if (!$validator->emailAddress($strEmail)) {
            $this->flashMessenger()->setNamespace('ordertracking-error')->addMessage('Email không hợp lệ.');
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'index'));
        }

        $email = json_decode($getDetailOrder['orde_detail'], TRUE)['email'];
//        p($strEmail);die();
        if ($email == $strEmail) {
            $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
            $arrProductOrderList = $serviceProductOrder->getList(array('orde_id' => $order_id));

            $serviceOrderLog = $this->serviceLocator->get('My\Models\OrderLog');
            $arrOrderLogList = $serviceOrderLog->getList(array('orde_id' => $order_id));

            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $listProductID = '';
            foreach ($arrProductOrderList as $val) {
                $listProductID .= $val['prod_id'].',';
            }
            $listProductID = rtrim($listProductID,',');
            $arrDetailProduct = $serviceProduct->getList(array('listProductID' => $listProductID));
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('listProductID' => explode(',',$listProductID)));
//            $arrDetailProduct = $instanceSearchProduct->getList();
            
            if ($arrProductOrderList) {
                return array(
                    'params' => $params,
                    'getdetailOrder' => $getDetailOrder,
                    'arrProductOrderList' => $arrProductOrderList,
                    'arrOrderLogList' => $arrOrderLogList,
                    'arrDetailProduct' => $arrDetailProduct,
                );
            }

            return array(
                'params' => $params,
            );
        } else {
            $this->flashMessenger()->setNamespace('ordertracking-error')->addMessage('Đơn hàng này không tồn tại');
            return $this->redirect()->toRoute('ordertracking', array('controller' => 'ordertracking', 'action' => 'index'));
        }
    }

}
