<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class MenuController extends MyController {
    /* @var $serviceMenu \My\Models\Menu */

    public function __construct() {
        $this->externalJS = [
            'backend:menu:index' => array(
                STATIC_URL . '/b/js/my/??menu.js'
            ),
            'backend:menu:add' => array(
                STATIC_URL . '/b/js/my/??menu.js',
            ),
            'backend:menu:edit' => array(
                STATIC_URL . '/b/js/my/??menu.js',
            )
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $serviceMenu = $this->serviceLocator->get('My\Models\Menu');
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array('menu_status' => 0, 'menu_name_like' => General::clean(trim($this->params()->fromQuery('s'))));
        $intTotal = $serviceMenu->getTotal($arrCondition);
        $arrMenuList = $serviceMenu->getListLimit($arrCondition, $intPage, $intLimit, 'menu_id ASC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrMenuList' => $arrMenuList
        );
    }

    public function addAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p(!isset($params['menuType']));die;

            $errors = array();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['MenuName'])) {
                $errors['MenuName'] = 'Tên Menu không được bỏ trống !';
            }

            if(!isset($params['menuType'])){
                $errors['menuType']='Mời chọn phiên bản hiển thị !';
            }

            if (!isset($params['Status'])) {
                $errors['Status'] = 'Chưa chọn trạng thái cho Menu !';
            }
            
            $serviceMenu = $this->serviceLocator->get('My\Models\Menu');
            $inResult = $serviceMenu->getTotal(array('menu_name'=>trim($params['MenuName']),'not_menu_status'=>-1,'menu_type'=>$params['menuType']));
            
            if($inResult > 0){
                $errors['MenuName'] = 'Menu này đã tồn tại trong trong phiên bản này! Vui lòng chọn menu khác !';
            }
            
            if (empty($errors)) {
                $arrData = array(
                    'menu_name' => trim($params['MenuName']),
                    'menu_url' => trim($params['Url']),
                    'localtion' => $params['localtion'],
                    'menu_status' => $params['Status'],
                    'class_icon' => $params['class_icon'],
                    'menu_type'=>$params['menuType'],
                    'menu_created' => time()
                );
                
                $inResult = $serviceMenu->add($arrData);
                if ($inResult > 0) {

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Menu',
                        'logs_action' => 'add',
                        'logs_time' => time(),
                        'logs_detail' => 'Thêm Menu có id = ' . $inResult,
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-add-menu')->addMessage('Thêm Menu thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'menu', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'menu', 'action' => 'add'));
                    }
                }
                $errors[] = 'Xảy ra lỗi trong quá trinh xử lý, hoặc tên Menu này đã tồn tai! Vui lòng thử lại !';
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
        );
    }

    public function editAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'menu', 'action' => 'index'));
        }
        $serviceMenu = $this->serviceLocator->get('My\Models\Menu');
        $detailMenu = $serviceMenu->getDetail(array('menu_id' => $params['id']));

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['MenuName'])) {
                $errors['MenuName'] = 'Tên Menu không được bỏ trống !';
            }

//            if(empty($params['Url'])){
//                $errors['Url']='Địa chỉ URL không được bỏ trống !';
//            }

            if (!isset($params['Status'])) {
                $errors['Status'] = 'Chưa chọn trạng thái cho Menu !';
            }

//            $validator = new Validate();
//            $isNotExist = $validator->noRecordExists($params['MenuName'], 'tbl_menus', 'menu_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), array('field' => 'menu_id', 'value' => $detailMenu['menu_id'],));
//            
//            if (empty($isNotExist)) {
//                $errors['MenuName'] = 'Menu này đã tồn tại !';
//            }
//            
            if (empty($errors)) {
                $arrData = array(
                    'menu_name' => trim($params['MenuName']),
                    'menu_url' => trim($params['Url']),
                    'localtion' => $params['localtion'],
                    'class_icon' => $params['class_icon'],
                    'menu_status' => $params['Status'],
//                    'menu_created'=>time()
                );
                $serviceMenu = $this->serviceLocator->get('My\Models\Menu');
                $inResult = $serviceMenu->edit($arrData, $detailMenu['menu_id']);

                if ($inResult >= 0) {

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Menu',
                        'logs_action' => 'edit',
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa Menu có id = ' . $detailMenu['menu_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-menu')->addMessage('Sửa Menu thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'menu', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'menu', 'action' => 'edit', 'id' => $detailMenu['menu_id']));
                    }
                }
                $errors[] = 'Xảy ra lỗi trong quá trinh xử lý, hoặc tên Menu này đã tồn tai! Vui lòng thử lại !';
            }
        }

        return array(
            'errors' => $errors,
            'params' => $params,
            'detailMenu' => $detailMenu,
        );
    }

    public function deleteAction() {
        $params = $this->params()->fromPost();
        $errors = array();
        if (empty($params['menuID'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        $serviceMenu = $this->serviceLocator->get('My\Models\Menu');
        $arrData = array('menu_status' => -1);
        $intResult = $serviceMenu->edit($arrData, $params['menuID']);
        if ($intResult) {

            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                'user_id' => UID,
                'logs_controller' => 'Menu',
                'logs_action' => 'delete',
                'logs_time' => time(),
                'logs_detail' => 'Xóa Menu có id = ' . $params['menuID'],
            );
            $serviceLogs->add($arrLogs);

            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Menu hoàn tất')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

}
