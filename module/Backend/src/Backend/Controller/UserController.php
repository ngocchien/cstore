<?php

namespace Backend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    My\General;

class UserController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceGroup \My\Models\Group */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */

    public function __construct() {
        $this->defaultJS = [
            'backend:user:add' => 'jquery.validate.min.js,jquery.inputmask.bundle.min.js',
            'backend:user:edit' => 'jquery.validate.min.js,jquery.inputmask.bundle.min.js',
            'backend:user:index' => 'jquery.validate.min.js,jquery.inputmask.bundle.min.js,bootstrap-select.js',
        ];
        $this->defaultCSS = [
            'backend:user:index' => 'bootstrap-select.css',
        ];
        $this->externalJS = [
            'backend:user:index' => STATIC_URL . '/b/js/my/??user.js',
            'backend:user:add' => STATIC_URL . '/b/js/my/??user.js',
            'backend:user:edit' => STATIC_URL . '/b/js/my/??user.js',
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);
        $digisFilter = new \Zend\Filter\Digits();
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrGroup = $serviceGroup->getList();
        $phoneNumber = $digisFilter->filter($this->params()->fromQuery('phoneNumber', 0));
        $arrConditions = array(
            'not_user_status' => -1,
            'user_phone' => $phoneNumber,
            'name_or_email_or_phone' => General::clean(trim($params['s'])),
            'listGroupID' => $params['group'],
        );
        $route = 'backend-user-search';
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUserList = $serviceUser->getListLimit($arrConditions, $intPage, $intLimit, 'user_id DESC');
        $intTotal = $serviceUser->getTotal($arrConditions);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        // get group list
        $groupCondition = array(
            'status !=' => -1,
        );
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $roleList = $serviceGroup->getList($groupCondition);
        $groups = array();
        foreach ($roleList as $role) {
            $groups[$role['grou_id']] = $role;
        }

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrUserList' => $arrUserList,
            'arrRole' => $groups,
            'arrGroup' => $arrGroup
        );
    }

    public function addAction() {
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $arrCityList = $serviceCity->getList(array('city_status' => 0));
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $roleList = $serviceGroup->getList($groupCondition);
        $paramsRoute = $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {
                $errors = array();
                $validator = new Validate();

                $strPassword = trim($params['password']);

                if (!$validator->notEmpty($params['fullName'])) {
                    $errors['fullName'] = 'Tên người dùng không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['email'])) {
                    $errors['email'] = 'Địa chỉ email không được bỏ trống.';
                } elseif (!$validator->emailAddress($params['email'])) {
                    $errors['email'] = 'Địa chỉ email không không đúng.';
                }

                if (!$validator->notEmpty($params['phoneNumber'])) {
                    $errors['phoneNumber'] = 'Số điện thoại không được bỏ trống.';
                }

                if (!$validator->notEmpty($params['gender'])) {
                    $errors['gender'] = 'Vui lòng chọn giới tính.';
                }
                if (!$validator->notEmpty($strPassword)) {
                    $errors['password'] = 'Mật khẩu không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['rePassword'])) {
                    $errors['rePassword'] = 'Vui lòng nhập lại mật khẩu.';
                }
                if ($strPassword !== $params['rePassword']) {
                    $errors['passwordNotMatch'] = 'Mật khẩu không trùng khớp';
                }
                if (!$validator->notEmpty($params['group'])) {
                    $errors['role'] = 'Vui lòng chọn nhóm người dùng.';
                }
                if (empty($errors)) {
                    $serviceUser = $this->serviceLocator->get('My\Models\User');
                    //check email trong database
                    $arrUsermail = $serviceUser->getList(array('user_email' => $params['email']));
                    if (count($arrUsermail) > 0) {
                        $errors['email'] = 'Emal này đã tồn tại trong hệ thống.';
                        return array(
                            'params' => $params,
                            'errors' => $errors,
                            'arrCityList' => $arrCityList,
                            'arrRole' => $roleList
                        );
                    }
                    //check phone number in database
                    $arrUserphone = $serviceUser->getList(array('user_phone' => $params['phoneNumber']));
                    if (count($arrUserphone) > 0) {
                        $errors['phoneNumber'] = 'Số điện thoại này đã tồn tại trong hệ thống.';
                        return array(
                            'params' => $params,
                            'errors' => $errors,
                            'arrCityList' => $arrCityList,
                            'arrRole' => $roleList
                        );
                    }

                    $arrCity = $serviceCity->getDetail(array('city_id' => $params['city']));

                    $serviceDistrict = $this->serviceLocator->get('My\Models\District');
                    $arrDistrict = $serviceDistrict->getDetail(array('dist_id' => $params['district']));


                    list($day, $month, $year) = explode('-', $params['birthdate']);
                    $birthDate = mktime(0, 0, 0, $month, $day, $year);

                    $strPassword = $this->createPassword($strPassword);

                    $arrData = array(
                        'user_fullname' => trim($params['fullName']),
                        'user_email' => trim($params['email']),
                        'user_phone' => $params['phoneNumber'],
                        'user_birthdate' => $birthDate,
                        'user_gender' => (int) $params['gender'],
                        'city_id' => (int) $params['city'],
                        'dist_id' => (int) $params['district'],
                        'user_address' => trim($params['address']),
                        'user_password' => $strPassword,
                        'grou_id' => (int) $params['group'],
                        'user_status' => (int) $params['user_status'],
                        'user_created' => time(),
                    );

                    //insert user in table users
                    $serviceUser = $this->serviceLocator->get('My\Models\User');
                    $intResult = $serviceUser->add($arrData);


                    if ($intResult > 0) {

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Thêm User id = ' . $intResult,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-add-user')->addMessage('Thêm người dùng thành công.');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'add'));
                        }
                        //$this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'edit', 'id' => $intResult));
                    }
                    $errors[] = 'Không thể thêm dữ liệu. Hoặc email đã tồn tại. Xin vui lòng kiểm tra lại';
                }
            }
        }

        return array(
            'params' => $params,
            'errors' => $errors,
            'arrCityList' => $arrCityList,
            'arrRole' => $roleList
        );
    }

    public function editAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $intUserID = (int) $params['id'];
        $arrCondition = array('user_id' => $intUserID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $errors = array();
        $arrDistrict = array();
        $arrWardList = array();

        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');

        $arrCityList = $serviceCity->getList(array('city_status' => 0));

        if ($arrUser['city_id']) {
            $arrDistrict = $serviceDistrict->getList(array('city_id' => $arrUser['city_id']));
        }


        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if ($params && is_array($params)) {

                $validator = new Validate();
                if (!$validator->notEmpty($params['fullName'])) {
                    $errors['fullname'] = 'Tên người dùng không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['email'])) {
                    $errors['email'] = 'Email người dùng không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['phoneNumber'])) {
                    $errors['phoneNumber'] = 'Số điện thoại không được bỏ trống.';
                }
                if (!$validator->notEmpty($params['birthdate'])) {
                    $errors['birthdate'] = 'Vui lòng nhập ngày sinh.';
                }
                if (!$validator->notEmpty($params['gender'])) {
                    $errors['gender'] = 'Vui lòng chọn giới tính.';
                }
                if (!$params['group']) {
                    $errors['group'] = 'Vui lòng chọn nhóm người dùng.';
                }

                if (empty($errors)) {
                    $arrCity = $serviceCity->getDetail(array('city_id' => $params['city']));
                    $arrDistrict = $serviceDistrict->getDetail(array('dist_id' => $params['district']));

                    list($day, $month, $year) = explode('-', $params['birthdate']);
                    $birthDate = mktime(0, 0, 0, $month, $day, $year);

                    $strPassword = trim($params['password']);
                    if ($validator->notEmpty($strPassword)) {
                        $strPassword = $this->createPassword($strPassword);
                    } else {
                        $strPassword = $arrUser['user_password'];
                    }

                    $arrData = array(
                        'user_fullname' => trim($params['fullName']),
                        'user_email' => trim($params['email']),
                        'user_phone' => $params['phoneNumber'],
                        'user_password' => $strPassword,
                        'user_birthdate' => $birthDate,
                        'user_updated' => time(),
                        'user_gender' => (int) $params['gender'],
                        'city_id' => (int) $params['city'],
                        'dist_id' => (int) $params['district'],
                        'user_address' => trim($params['address']),
                        'user_status' => (int) $params['user_status'],
                        'grou_id' => (int) $params['group'],
                        'user_updated' => time(),
                    );
                    $intResult = $serviceUser->edit($arrData, $intUserID);

                    if ($intResult > 0) {

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Chỉnh sửa User id = ' . $intUserID,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-edit-user')->addMessage('Chỉnh sửa người dùng thành công.');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'edit', 'id' => $intResult));
                        }
                    } else {
                        $errors[] = 'Không thể sửa dữ liệu hoặc địa chỉ email đã tồn tại. Xin vui lòng kiểm tra lại';
                    }
                }
            }
        }

        $groupCondition = array(
            'is_deleted' => 0,
            'status' => 1,
        );

        $serviceGroup = $this->serviceLocator->get('My\Models\Group');

        $roleList = $serviceGroup->getList($groupCondition);
        return array(
            'params' => $params,
            'arrUser' => $arrUser,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
            'arrCityList' => $arrCityList,
            'arrDistrictList' => $arrDistrict,
            'arrRole' => $roleList
        );
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $intUserID = (int) $params['id'];
        $arrCondition = array('user_id' => $intUserID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'user', 'action' => 'index'));
        }
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $roleList = $serviceGroup->getList($groupCondition);
        $groups = array();
        foreach ($roleList as $role) {
            $groups[$role['grou_id']] = $role;
        }
        return array(
            'arrUser' => $arrUser,
            'arrGroup' => $groups
        );
    }

    public function deleteAction() {
        $result = null;
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['user_id'])) {
                return $this->getResponse()->setContent($result);
            }
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $result = $serviceUser->edit(array('user_status' => -1), $params['user_id']);
            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                'user_id' => UID,
                'logs_controller' => 'User',
                'logs_action' => 'delete',
                'logs_time' => time(),
                'logs_detail' => 'Xóa User có id = ' . json_encode($params['user_id']),
            );
            $serviceLogs->add($arrLogs);
        }
        return $this->getResponse()->setContent($result);
    }

    public function getUserInfoAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUser = $serviceUser->getDetail(array('email' => $params['email']));
            unset($arrUser['password']);
            return $this->getResponse()->setContent(json_encode($arrUser));
        }
        return $this->getResponse()->setContent('');
    }

    public function statisticAction() {
        $params = $this->params()->fromQuery();
        $intLimit = 30;

        $digisFilter = new \Zend\Filter\Digits();
        $phoneNumber = $digisFilter->filter($this->params()->fromQuery('phoneNumber', 0));

        $arrConditions = array(
            'is_deleted' => 0,
            'phone' => $phoneNumber,
            'email' => trim($this->params()->fromQuery('email')),
            'fullname' => trim($this->params()->fromQuery('fullname')),
            'payment_status' => 6
        );

        //check if user using filter
        $isFilter = $this->isFilter($arrConditions);
        $arrUserList = array();

        if ($isFilter) {
            $route = 'backend-user-search';

            $searchUser = new \My\Search\User();
            $searchUser->setParams($params)
                    ->setLimit($intLimit);

            $arrUserList = $searchUser->getSearchData();
            $intTotal = $searchUser->getTotalHits();
        } else {
            $route = 'backend';
            $intPage = $this->params()->fromRoute('page', 1);

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUserList = $serviceUser->statisticOrder($arrConditions, $intPage, $intLimit);

            $intTotal = $serviceUser->getTotalStatisticOrder($arrConditions);
        }

        $params = $this->params()->fromRoute();
        //merge params
        $params = array_merge($params, $this->params()->fromQuery());

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrUserList' => $arrUserList,
            'isFilter' => $isFilter,
        );
    }

    /**
     * Check user using filter
     * @param array $arrConditions
     * @return boolean is filter
     */
    private function isFilter($arrConditions) {
        return $arrConditions['user_phone'] ||
                $arrConditions['user_fullname'] ||
                $arrConditions['user_role'] ||
                $arrConditions['user_email'] ? true : false;
    }

    private function createPassword($strPassword) {
        //$bcrypt = new Bcrypt(array('salt' => microtime(true) . SECRET_KEY, 'cost' => 12));
        //$strPassword = $bcrypt->create($strPassword);
        $strPassword = md5($strPassword);
        return $strPassword;
    }

    public function boughtAction() {
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 10);
        // p($params);die();
        $arrCondition = array();

        if (!empty($params['fullname']) && $params['fullname'] != '') {
            $arrCondition['buser_fullname'] = $params['fullname'];
        }
        if (!empty($params['email']) && $params['email'] != '') {
            $arrCondition['buser_email'] = $params['email'];
        }
        if (!empty($params['phone']) && $params['phone'] != '') {
            $arrCondition['buser_phone'] = $params['phone'];
        }
        if (!empty($params['id']) && $params['id'] != '') {
            $arrCondition['buser_id'] = $params['id'];
        }
//        p($arrCondition);die();
        $route = 'backend-bought-search';
        $arrUserList = $serviceUser->getUserBought($arrCondition, $intPage, $intLimit);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $intTotal = $serviceUser->getTotalUserBought($arrCondition)[0]['total'];
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        return array(
            'arrUserList' => $arrUserList,
            'paging' => $paging,
            'params' => $params
        );
    }

}
