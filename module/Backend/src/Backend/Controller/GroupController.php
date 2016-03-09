<?php

namespace Backend\Controller;

use My\Validator\Validate;
use My\General;

class GroupController extends \My\Controller\MyController {
    /* @var $serviceGroup \My\Models\Group */
    /* @var $serviceUser \My\Models\User */

    public function __construct() {
        $this->defaultJS = [
                // 'backend:city:index' => 'jquery.validate.min.js',
        ];
        $this->externalJS = [
//            'backend:group:index' => array(
//                STATIC_URL . '/b/js/my/??group.js'
//            ),
//            'backend:group:add' => array(
//                STATIC_URL . '/b/js/my/??group.js',
//            ),
//            'backend:group:edit' => array(
//                STATIC_URL . '/b/js/my/??group.js',
//            ),
            'backend:group:index' => STATIC_URL . '/b/js/my/??group.js',
            'backend:group:add' => STATIC_URL . '/b/js/my/??group.js',
            'backend:group:edit' => STATIC_URL . '/b/js/my/??group.js'
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromQuery();
        $intLimit = 15;

        $route = 'backend';
        $intPage = $this->params()->fromRoute('page', 1);
        $arrConditions = array('not_grou_status' => -1, 'match_name' => General::clean(trim($this->params()->fromQuery('s'))));
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrGroupList = $serviceGroup->getListLimit($arrConditions, $intPage, $intLimit, 'grou_id DESC');
        $intTotal = $serviceGroup->getTotal($arrConditions);

        $params = $this->params()->fromRoute();

        //merge params
        $params = array_merge($params, $this->params()->fromQuery());

        $helper = $this->serviceLocator->get('viewhelpermanager')
                ->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        return array('params' => $params, 'paging' => $paging,
            'arrGroupList' => $arrGroupList,);
    }

    public function addAction() {

        $paramsRoute = $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {

                $errors = array();
                $validator = new Validate();

                if (!$validator->notEmpty($params['groupName'])) {
                    $errors['groupName'] = 'Tên nhóm không được bỏ trống.';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'grou_name' => trim($params['groupName']),
                        'grou_css' => trim($params['groupCss']),
                        'is_acp' => (int) $params['isAcp'],
                        'is_fullaccess' => (int) $params['isFullaccess'],
                        'grou_status' => (int) $params['status'],
                        'user_created' => UID,
                        'grou_created' => time(),
                    );
                    $serviceUser = $this->serviceLocator->get('My\Models\Group');
                    $intResult = $serviceUser->add($arrData);
                    if ($intResult > 0) {

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Thêm Nhóm người dùng có id = ' . $intResult,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-add-group')->addMessage('Thêm nhóm thành công.');

                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'add'));
                        }
                    } else {
                        $errors[] = 'Không thể thêm dữ liệu. Xin vui lòng kiểm tra lại';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
        );
    }

    public function editAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
        }
        $intGroupID = (int) $params['id'];
        $arrCondition = array('grou_id' => $intGroupID);
        $serviceGroups = $this->serviceLocator->get('My\Models\Group');
        $arrGroups = $serviceGroups->getDetail($arrCondition);

        if (empty($arrGroups)) {
            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
        }

        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {
                $validator = new Validate();
                if (!$validator->notEmpty($params['groupName'])) {
                    $errors['groupName'] = 'Tên nhóm không được bỏ trống.';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'grou_name' => trim($params['groupName']),
                        'grou_css' => trim($params['groupCss']),
                        'is_fullaccess' => (int) $params['isFullaccess'],
                        'is_acp' => trim($params['isAcp']),
                        'grou_status' => (int) $params['status'],
                        'user_updated' => UID,
                        'grou_updated' => time(),
                    );

                    $intResult = $serviceGroups->edit($arrData, $intGroupID);
                    if ($intResult > 0) {

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Chỉnh sửa Nhóm người dùng có id = ' . $intGroupID,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-edit-group')->addMessage('Chỉnh sửa nhóm thành công.');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'group', 'action' => 'edit', 'id' => $intGroupID));
                        }
                    } else {
                        $errors[] = 'Không thể sửa dữ liệu. Xin vui lòng kiểm tra lại';
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'arrGroup' => $arrGroups,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
        );
    }

    public function deleteAction() {
        $paramsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $groupID = $params['grou_id'] > 0 ? $params['grou_id'] : 0;

            $arrData = array('grou_status' => -1, 'user_updated' => UID, 'grou_updated' => time());
            $serviceGroups = $this->serviceLocator->get('My\Models\Group');

            if ($serviceGroups->edit($arrData, $groupID)) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => $paramsRoute['__CONTROLLER__'],
                    'logs_action' => $paramsRoute['action'],
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Nhóm người dùng có id = ' . $groupID,
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Đã xóa thành công!!!')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không thể xóa nhóm này. Xin vui lòng thử lại')));
        }
        return $this->getResponse()->setContent('Wrong Method');
    }

}
