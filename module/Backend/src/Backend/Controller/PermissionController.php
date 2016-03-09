<?php

namespace Backend\Controller;

class PermissionController extends \My\Controller\MyController {
    
    /* @var $serviceGroup \My\Models\Group */
    /* @var $servicePer \My\Models\Permission */
    
    public function __construct() {
        // $this->defaultJS = STATIC_URL . '/b/js/my/??permission.js';
        $this->css = '';
        $this->externalJS = [
            'backend:permission:index' => STATIC_URL . '/b/js/my/??permission.js',
            'backend:permission:add' => STATIC_URL . '/b/js/my/??permission.js',
            'backend:permission:edit' => STATIC_URL . '/b/js/my/??permission.js'
        ];

//        $this->defaultJS = [
//            'backend:district:index' => 'jquery.validate.min.js',
//        ];
//        $this->externalJS = STATIC_URL . '/b/js/my/??district.js';
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $arrConditions = array(
            'role_not_status' => -1,
        );
        $serviceRole = $this->serviceLocator->get('My\Models\Role');
        $roleList = $serviceRole->getList($arrConditions);


        $perList = array();
        if (isset($params['gid'])) {
            $intGroupRole = (int) $params['gid'];
            $arrConditions = array(
                'grou_id' => $intGroupRole,
            );
            $serviceGroup = $this->serviceLocator->get('My\Models\Group');
            $groupDetail = $serviceGroup->getDetail($arrConditions);

    
            if (empty($intGroupRole) || !isset($groupDetail['grou_id'])) {
                $this->flashMessenger()->setNamespace('empty-or-wrong-role')->addMessage('Nhóm này không tồn tại');
                return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index'));
            }

            $servicePer = $this->serviceLocator->get('My\Models\Permission');
            if ($this->request->isPost()) {
                $params = $this->params()->fromPost();
                if (isset($params["role"])) {
                    $servicePer->editBy(array("perm_status" => 0), "grou_id = " . $groupDetail['grou_id'] . "");
                    foreach ($params["role"] as $k => $val) {
                        $arrDetailRole = $servicePer->getDetail(array("role_id" => $k, "grou_id" => $groupDetail['grou_id']));
                        if (isset($arrDetailRole["perm_id"])) {
                            $arrData = array("perm_status" => 1);
                            $servicePer->edit($arrData, $arrDetailRole["perm_id"]);
                        } else {
                            $arrData = array("role_id" => $k, "grou_id" => $groupDetail['grou_id'], "perm_status" => 1);
                            $servicePer->add($arrData);
                        }
                    }
                }

                $this->flashMessenger()->setNamespace('success')->addMessage('Cập nhật phân quyền thành công');
                return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index', 'gid' => $groupDetail['grou_id']));
            }


            $perList = $servicePer->getList(array("grou_id" => $groupDetail['grou_id'], "perm_status" => 1));
        }

        return array('params' => $params, 'arrRoleList' => $this->formatRole($roleList), 'arrPerList' => $this->formatPer($perList));
    }

    function formatRole($array = array()) {
        $data = array();
        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $data[$value["module"]][$value["controller"]]["name"] = $value["controller"];
                $data[$value["module"]][$value["controller"]]["children"][] = $value;
            }
        }
        return $data;
    }

    function formatPer($array = array()) {
        $data = array();
        if (count($array) > 0) {
            foreach ($array as $key => $value) {
                $data[] = $value["role_id"];
            }
        }
        return $data;
    }

//
//    public function grantAction() {
//        $params = $this->params()->fromRoute();
//        //print_r($params);die;
//        $roleName = '';
//        $perCondition = array();
//        $currentPart = '';
//        $intUserRole = 0;
//        // case grant permission to group
//        if (isset($params['gid'])) {
//        	$intUserRole = (int) $params['gid'];
//        	$currentPart = 'gid';
//        	$arrConditions = array(
//        			'grou_id' => $intUserRole,
//        			//'is_deleted' => 0,
//        			'grou_status' => 1,
//        	);
//        	$serviceGroup = $this->serviceLocator->get('My\Models\Group');
//        	$groupDetail = $serviceGroup->getDetail($arrConditions);
//        	$roleName = 'cho nhóm: <b>' . $groupDetail['group_name'] . '</b>';
//        	$perCondition = array('user_role' => $intUserRole);
//        	if (empty($intUserRole) || !isset($groupDetail['group_name']) || $groupDetail['group_name'] == '') {
//        		$this->flashMessenger()->setNamespace('empty-or-wrong-role')->addMessage('Phân quyền không chính xác');
//        		return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index'));
//        	}
//        }
//        
//        // case grant permission to user
//        if (isset($params['pid'])) {
//        	$intUserRole = (int) $params['pid'];
//        	$currentPart = 'pid';
//        	$arrConditions = array(
//        			'user_id' => $intUserRole,
//        			//'is_deleted' => 0,
//        			//'is_actived' => 1,
//        	);
//        	$serviceGroup = $this->serviceLocator->get('My\Models\User');
//        	$userDetail = $serviceGroup->getDetail($arrConditions);
//        	$roleName = 'cho thành viên: <b>' . $userDetail['user_fullname'] .'</b>';
//        	$perCondition = array('user_id' => $intUserRole);
//        	if (empty($intUserRole) || !isset($userDetail['user_fullname']) || $userDetail['user_fullname'] == '') {
//        		$this->flashMessenger()->setNamespace('empty-or-wrong-role')->addMessage('Phân quyền không chính xác');
//        		return $this->redirect()->toRoute('backend', array('controller' => 'permission', 'action' => 'index'));
//        	}
//        }
//        $arrAllowedResource = array();
//        $servicePermission = $this->serviceLocator->get('My\Models\Permission');
//        $arrResourceList = $servicePermission->getAllResource();
//        $arrPermissionList = $servicePermission->getList($perCondition);
//        
//        if (isset($arrPermissionList)) {
//            foreach ($arrPermissionList as $arrPermission) {
//                $arrAllowedResource[] = strtolower($arrPermission['module_name']) . ':' . strtolower($arrPermission['controller_name']) . ':' . strtolower($arrPermission['action_name']);
//            }
//        }
//        return array(
//            'params' => $params,
//            'roleName' => $roleName,
//            'arrResourceList' => $arrResourceList,
//            'arrAllowedResource' => $arrAllowedResource,
//        	'currentPart' => $currentPart,
//        	'intUserRole' => $intUserRole
//        );
//    }
//    public function addAction() {
//        if ($this->request->isPost()) {
//            $params = $this->params()->fromPost();
//            $urlParams = $this->params()->fromRoute();
//            // $intUserRole = (int) $this->params()->fromRoute('id', 0);
//            // check resource
//            if (empty($params['resource']) || !is_array($params)) {
//            	return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không thể thêm phân quyền. Xin vui lòng thử lại')));
//            }
//            // analys resource
//            list($moduleName, $controllerName, $actionName) = explode(':', $params['resource']);
//            $arrData = array(
//            		'module_name' => $moduleName,
//            		'controller_name' => $controllerName,
//            		'action_name' => $actionName,
//            		'is_allowed' => 1,
//            );
//
//            // case grant permission to group
//            if (isset($urlParams['gid'])) {
//            	$arrData['user_role'] = (int) $urlParams['gid'];
//            }
//        	// case grant permission to user
//            if(isset($urlParams['pid'])) {
//            	$arrData['user_id'] = (int) $urlParams['pid'];
//            }
//            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
//            if ($servicePermission->add($arrData)) {
//                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Đã thêm phân quyền mới thành công!!!')));
//            }
//            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không thể thêm phân quyền. Xin vui lòng thử lại')));
//        }
//        return $this->getResponse()->setContent('Wrong Method');
//    }
//    public function deleteAction() {
//        if ($this->request->isPost()) {
//            $params = $this->params()->fromPost();
//            $urlParams = $this->params()->fromRoute();
//            //$intUserRole = (int) $this->params()->fromRoute('id', 0);
//            if (empty($params['resource']) || !is_array($params)) {
//                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không thể xóa phân quyền này. Xin vui lòng thử lại')));
//            }
//            list($moduleName, $controllerName, $actionName) = explode(':', $params['resource']);
//            $arrData = array(
//                'module_name' => $moduleName,
//                'controller_name' => $controllerName,
//                'action_name' => $actionName,
//            );
//            // case grant permission to group
//            if (isset($urlParams['gid'])) {
//            	$arrData['user_role'] = (int) $urlParams['gid'];
//            }
//            // case grant permission to user
//            if(isset($urlParams['pid'])) {
//            	$arrData['user_id'] = (int) $urlParams['pid'];
//            }
//            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
//            if ($servicePermission->remove($arrData)) {
//                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Đã thêm phân quyền mới thành công!!!')));
//            }
//            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không thể xóa phân quyền này. Xin vui lòng thử lại')));
//        }
//        return $this->getResponse()->setContent('Wrong Method');
//    }
}
