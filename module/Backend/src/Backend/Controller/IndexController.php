<?php

namespace Backend\Controller;

use My\Controller\MyController;

class IndexController extends MyController {

    public function __construct() {
        $this->defaultJS = [
            'backend:index:add' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,',
            'backend:index:index' => 'jquery.sumoselect.min.js,bootstrap-select.js,jquery.number.js,xteam_fireworks.min.js'
        ];
        $this->defaultCSS = [
            'backend:index:add' => 'sumoselect.css,bootstrap-fileupload.css',
            'backend:index:index' => 'sumoselect.css,bootstrap-fileupload.css,style.css',
        ];
        $this->externalJS = [
            'backend:index:index' => array(
                STATIC_URL . '/b/js/my/??index.js',
                STATIC_URL . '/b/js/library/??highcharts.js',
                STATIC_URL . '/b/js/my/??statistic.js',
                STATIC_URL . '/b/js/library/??bootstrap-datepicker.js',
            ),
            'backend:category:add' => array(
            ),
            'backend:category:edit' => array(
            )
        ];
    }

    public function indexAction() {
//        $params = $this->params()->fromRoute();
//        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//        $serviceUser = $this->serviceLocator->get('My\Models\User');
//
//        $NumProduct = $serviceProduct->getTotal(array('not_prod_status' => -1));
//        $NumUser = $serviceUser->getTotal(array('not_user_status' => -1));
//        $NumComment = $serviceComment->getTotal(array());
//        $NumCommentNo = $serviceComment->getTotal(array('comm_status' => 0));
//        $NumOrderNo = $serviceOrder->getTotal(array('is_payment' => 0));
//        $NumOrder = $serviceOrder->getTotal(array());
//        $date = array(
//            'from' => date('01/m/Y', strtotime('this month')),
//            'to' => date('d/m/Y')
//        );
//        if ($this->request->isPost()) {
//            $params = $this->params()->fromPost();
//            if (!empty($params['from']) && !empty($params['to'])) {
//                $arrCondition = array(
//                    'from' => strtotime(General::formatDateM_D_Y($params['from'])),
//                    'to' => strtotime('+1 day', strtotime(General::formatDateM_D_Y($params['to']))),
//                );
//                $date = array(
//                    'from' => $params['from'],
//                    'to' => $params['to']
//                );
//            }
//        }
//        return array(
//            'params' => $params,
//            'NumProduct' => $NumProduct,
//            'NumUser' => $NumUser,
//            'NumComment' => $NumComment,
//            'NumCommentNo' => $NumCommentNo,
//            'NumOrder' => $NumOrder,
//            'NumOrderNo' => $NumOrderNo,
//            'arrNotifyProduct' => $arrNotifyProduct,
//            'NumNotifyProduct' => $NumNotifyProduct,
//            'date' => $date
//        );
    }

}
