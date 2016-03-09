<?php

namespace Backend\Controller;

use My\Controller\MyController;

class SubscribeController extends MyController {
    
    /* @var $serviceSubscribe \My\Models\Subscribe */

    public function __construct() {
        $this->externalJS = [
            'backend:subscribe:index' => STATIC_URL . '/b/js/my/??subscribe.js'
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('subs_status' => 1, 'email_name_like' => trim($this->params()->fromQuery('s')));
        $intLimit = 15;
        $serviceSubscribe = $this->serviceLocator->get('My\Models\Subscribe');
        $intTotal = $serviceSubscribe->getTotal($arrCondition);
        $arrSubscribe = $serviceSubscribe->getListLimit($arrCondition, $intPage, $intLimit, 'subs_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());
        return array(
            'arrSubscribe' => $arrSubscribe,
            'params' => $params,
            'paging' => $paging
        );
    }

    public function deleteAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if (empty($params['subscribeID'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceSubscribe = $this->serviceLocator->get('My\Models\Subscribe');
            $arrCondition = array('subs_status' => -1);
            $intResult = $serviceSubscribe->edit($arrCondition, $params['subscribeID']);
            if ($intResult) {
                
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                            'user_id'=>UID,
                            'logs_controller'=>'Subcribe',
                            'logs_action'=>'delete',
                            'logs_time'=>time(),
                            'logs_detail'=>'Xóa Email subcribe có id = '.$params['subscribeID'],
                        );
                $serviceLogs -> add($arrLogs);
                
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa email đăng ký hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function exportExcelAction() {
        header('Content-Type: application/excel');
        header('Content-Disposition: attachment; filename="' . date('d-m-Y', time()) . '_export.csv"');
        $arrCondition = array('subs_status' => 1);
        $serviceSubscribe = $this->serviceLocator->get('My\Models\Subscribe');
        $arrSubscribe = $serviceSubscribe->getList($arrCondition);
        foreach ($arrSubscribe as $subs) {
            echo $subs['subs_email'] . "\r\n";
        }
        die();
    }

}
