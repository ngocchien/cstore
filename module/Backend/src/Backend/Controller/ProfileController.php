<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController,
    My\Validator\Validate;

class ProfileController extends MyController {
    
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */
    /* @var $servicePermission \My\Models\Permission */
    /* @var $serviceGroup \My\Models\Group */
    

    
    public function __construct() {
        $this->defaultJS = [
        ];
        $this->defaultCSS = [
        ];
        $this->externalJS = [
            'backend:profile:index' => array(
                STATIC_URL . '/b/js/my/??profile.js',
            ),
        ];
    }

    public function indexAction() {
        if (UID == 0) {
            $this->redirect()->toRoute('backend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = $this->params()->fromRoute();

        $arrCondition = array('user_id' => UID,'user_status' => 1);
        $serviceUser = $this->serviceLocator->get('My\Models\User');

        if ($this->request->isPost()) {
            $params = array_merge($this->params()->fromPost(),$params);
            $files = $this->params()->fromFiles();
            $user_avatar = General::ImageUpload($files['file-0'], 'profile');   //xem phần profile.js - phần upload dưới cùng
            $arrData = array(
                'user_avatar' => json_encode($user_avatar),
            );
            $serviceUser->edit($arrData, UID);
        }

        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'index', 'action' => 'index'));
        }
//p(json_decode($arrUser['user_avatar'], TRUE));die();
        return array(
            'params' => $params,
            'arrUser' => $arrUser,
        );
    }

    public function editAction() {
        $this->layout('layout/empty');
        if (UID == 0) {
            $this->redirect()->toRoute('backend', array('controller' => 'index', 'action' => 'index'));
        }
        $arrCondition = array('user_id' => UID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');
        $getDetailtUser = $serviceUser->getDetail(array('user_id' => UID));
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {
//p($params);die();
                $errors = array();
                $validator = new Validate();
                $arrExclude = array('field' => 'user_id', 'value' => UID);
                $strName = trim($params['name']);
                $strEmail = trim($params['email']);
                $strTelephone = trim($params['telephone']);
                $strAddress = trim($params['address']);
                $strOldPassword = trim($params['old_password']);
                $strNewPassword = trim($params['new_password']);
                $strReNewPassword = trim($params['re_new_password']);

                if (!$validator->notEmpty($strName)) {
                    $errors[] = 'Tên cá nhân không được bỏ trống.';
                } else if (!$validator->Between(strlen($strName), 3, 255)) {
                    $errors[] = 'Tên cá nhân phải từ 3 - 255 kí tự.';
                }

                if ($strOldPassword) {
                    if (md5($strOldPassword) != $getDetailtUser['user_password'])
                        $errors[] = 'Mật khẩu hiện tại không đúng ... vui lòng nhập lại.';
                    else if (!$strNewPassword)
                        $errors[] = 'Vui lòng nhập mật khẩu mới';
                    else if (strlen($strNewPassword) <= 6)
                        $errors[] = 'Mật khẩu mới phải trên 6 ký tự';
                    else if ($strNewPassword != $strReNewPassword)
                        $errors[] = 'Nhập lại mật khẩu mới không trùng ... vui lòng nhập lại';
                }

                if (!$validator->notEmpty($strEmail)) {
                    $errors[] = 'Địa chỉ email không được bỏ trống.';
                } else if (!$validator->emailAddress($strEmail)) {
                    $errors[] = 'Địa chỉ email không đúng qui định.';
                } else if (!$validator->Between(strlen($strEmail), 0, 255)) {
                    $errors[] = 'Email quá dài ... đề nghị đổi email khác.';
                }

                if (!$validator->notEmpty($strTelephone)) {
                    $errors[] = 'Số điện thoại không được bỏ trống.';
                } else if (!$validator->Digits($strTelephone)) {
                    $errors[] = 'Số điện thoại không đúng qui định.';
                } else if (!$validator->Between(strlen($strTelephone), 10, 11)) {
                    $errors[] = 'Số điện thoại không đúng qui định ... đề nghị đổi số khác.';
                }

                if (!$validator->notEmpty($strAddress)) {
                    $errors[] = 'Địa chỉ nhà không được bỏ trống.';
                } else if (strlen($strAddress) > 255) {
                    $errors[] = 'Địa chỉ quá dài ... đề nghị đổi địa chỉ khác.';
                } else if (strlen($strAddress) < 5) {
                    $errors[] = 'Bạn vui lòng điền đầy đủ số nhà và đường.';
                }

                $emailIsNotExist = $validator->noRecordExists($strEmail, 'tbl_users', 'user_email', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), $arrExclude);
                if (!$emailIsNotExist) {
                    $errors[] = 'Email này đã tồn tại ... xin vui lòng ghi email khác';
                }

                $telephoneIsNotExist = $validator->noRecordExists($strTelephone, 'tbl_users', 'user_phone', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), $arrExclude);
                if (!$telephoneIsNotExist) {
                    $errors[] = 'Số điện thoại này đã tồn tại ... xin vui lòng ghi số điện thoại khác khác';
                }

                if (empty($errors)) {

                    $arrCity = $serviceCity->getDetail(array('city_id' => $params['city']));

                    $arrDistrict = $serviceDistrict->getDetail(array('dist_id' => $params['district']));

                    list($day, $month, $year) = explode('-', $params['birthdate']);
                    $birthDate = mktime(0, 0, 0, $month, $day, $year);

                    $strOldPassword ? $strPassword = md5($strNewPassword) : $strPassword = $getDetailtUser['user_password'];
                    $arrData = array(
                        'user_fullname' => $strName,
                        'user_password' => $strPassword,
                        'user_birthdate' => $birthDate,
                        'user_gender' => (int) $params['gender'],
                        'user_phone' => $strTelephone,
                        'user_email' => $strEmail,
                        'user_address' => $strAddress,
                        'city_id' => (int) $params['city'],
                        'dist_id' => (int) $params['district'],
                        'user_updated' => time(),
                    );

                    $intResult = $serviceUser->edit($arrData, UID);

                    if ($intResult > 0) {
                        $servicePermission = $this->serviceLocator->get('My\Models\Permission');
                        $arrData['permission'] = $servicePermission->getListjoinRole(array("grou_id" => $getDetailtUser["grou_id"], "perm_status" => 1));
                        $arrData['grou_id'] = $getDetailtUser["grou_id"];
                        $arrData['user_id'] = UID;
                        unset($arrData['user_password']);
                        $this->getAuthService()->getStorage()->write($arrData); //write lại auth
                       
                        
                        return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'data' => $arrData, 'message' => 'Chỉnh sửa thành công')));
                    }
                }
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'data' => $errors)));
            }
        }
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
        }

        $arrCity = $serviceCity->getList();
        $arrDistrict = $serviceDistrict->getList(array('city_id' => $arrUser['city_id']));

        return array(
            'arrCity' => $arrCity,
            'arrDistrict' => $arrDistrict,
            'arrUser' => $arrUser,
        );
    }

    public function infoAction() {
        $this->layout('layout/empty');
        if (UID == 0) {
            $this->redirect()->toRoute('backend', array('controller' => 'index', 'action' => 'index'));
        }

        $arrCondition = array('user_id' => UID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            $this->redirect()->toRoute('backend', array('controller' => 'index', 'action' => 'index'));
        }
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $roleList = $serviceGroup->getList($groupCondition);
        $groups = array();
        foreach ($roleList as $role) {
            $groups[$role['grou_id']] = $role;
        }

        return array(
            'arrUser' => $arrUser,
            'arrGroup' => $groups,
        );
    }

    public function changecityAction() {
        $params = $this->params()->fromPost();
        if ($params && is_array($params)) {
            $serviceDistrict = $this->serviceLocator->get('My\Models\District');
            $arrDistrict = $serviceDistrict->getList(array('city_id' => $params['city']));
            if ($arrDistrict)
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'data' => $arrDistrict)));
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Tỉnh / Thành này chưa có Quận / Huyện')));
        }
    }

}
