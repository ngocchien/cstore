<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate;

class ProfileController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:profile:index' => 'jquery.range.js,insilde.js,category.js',
                'frontend:profile:edit' => 'insilde.js',
            ];
            $this->defaultCSS = [
                'frontend:profile:index' => 'jquery.range.css,checkbox.css,profile.css',
                'frontend:profile:edit' => 'jquery.range.css,checkbox.css,profile.css',
            ];
            $this->externalJS = [
                'frontend:profile:edit' => STATIC_URL . '/f/v1/js/my/??profile.js',
                'frontend:profile:index' => STATIC_URL . '/f/v1/js/my/??profile.js',
            ];
        }
        if (FRONTEND_TEMPLATE == 'mobile') {
            $this->externalJS = [
                'frontend:profile:edit' => STATIC_URL . '/f/mobile/js/my/??profile.js',
                'frontend:profile:index' => STATIC_URL . '/f/mobile/js/my/??profile.js',
                'frontend:profile:changepassword' => STATIC_URL . '/f/mobile/js/my/??profile.js',
            ];
        }
    }

    public function indexAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID, 'user_status' => 1);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $serviceGroup = $this->serviceLocator->get('My\Models\Group');
        $arrDetailUser = $serviceUser->getDetail($arrCondition);
        $arrGroup = $serviceGroup->getList(array('grou_status' => 1));
        if (empty($arrDetailUser)) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Đặt hàng - Thông tin tài khoản ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Thông tin tài khoản !'));
        return array(
            'params' => $params,
            'arrDetailUser' => $arrDetailUser,
            'arrGroup' => $arrGroup,
        );
    }

    public function changeavatarAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = $this->params()->fromPost();
        $files = $this->params()->fromFiles();
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $user_avatar = General::ImageUpload($files['file-0'], 'profile', $resize = 1);   //xem phần profile.js - phần upload dưới cùng
        $arrData = array(
            'user_avatar' => json_encode($user_avatar),
            'user_updated' => time(),
        );
        $serviceUser->edit($arrData, UID);
    }

    public function editAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = $this->params()->fromRoute();
//        p(UID);die;
        $arrCondition = array('user_id' => UID);
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');
        $getDetailtUser = $serviceUser->getDetail(array('user_id' => UID));
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p($params);die;
            if ($params && is_array($params)) {
                $errors = array();
                $validator = new Validate();
                $arrExclude = array('field' => 'user_id', 'value' => UID);
                $strName = trim($params['name']);
//                $strEmail = trim($params['email']);
//                $strTelephone = trim($params['telephone']);
                $strAddress = trim($params['address']);
//                p($strAddress);die;
                $strOldPassword = trim($params['old_password']);
                $strNewPassword = trim($params['new_password']);
                $strReNewPassword = trim($params['re_new_password']);
                $intDay = $params['day'];
                $intMonth = $params['month'];
                $intYear = $params['year'];

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

//                if (!$validator->notEmpty($strEmail)) {
//                    $errors[] = 'Địa chỉ email không được bỏ trống.';
//                } else if (!$validator->emailAddress($strEmail)) {
//                    $errors[] = 'Địa chỉ email không đúng qui định.';
//                } else if (!$validator->Between(strlen($strEmail), 0, 255)) {
//                    $errors[] = 'Email quá dài ... đề nghị đổi email khác.';
//                }
//                if (!$validator->notEmpty($strTelephone)) {
//                    $errors[] = 'Số điện thoại không được bỏ trống.';
//                } else if (!$validator->Digits($strTelephone)) {
//                    $errors[] = 'Số điện thoại không đúng qui định.';
//                } else if (!$validator->Between(strlen($strTelephone), 8, 11)) {
//                    $errors[] = 'Số điện thoại không đúng qui định ... đề nghị đổi số khác.';
//                }
                if (!$validator->notEmpty($strAddress)) {
                    $errors[] = 'Địa chỉ nhà không được bỏ trống.';
                } else if (strlen($strAddress) > 255) {
                    $errors[] = 'Địa chỉ quá dài ... đề nghị đổi địa chỉ khác.';
                } else if (strlen($strAddress) < 5) {
                    $errors[] = 'Bạn vui lòng điền đầy đủ số nhà và đường.';
                }
//                p($errors);die;
                if (!$validator->notEmpty($params['gender'])) {
                    $errors[] = 'Vui lòng chọn giới tính.';
                }

                if (!$validator->notEmpty($intDay) || !$validator->notEmpty($intMonth) || !$validator->notEmpty($intYear)) {
                    $errors[] = 'Vui lòng điền đầy đủ ngày tháng năm sinh.';
                }

//                if (!$validator->notEmpty($params['city'])) {
//                    $errors['city'] = 'Vui lòng chọn Tỉnh / Thành.';
//                }
//
//                if (!$validator->notEmpty($params['district'])) {
//                    $errors['district'] = 'Vui lòng chọn Quận / Huyện.';
//                }
//                $emailIsNotExist = $validator->noRecordExists($strEmail, 'tbl_users', 'user_email', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), $arrExclude);
//                if (!$emailIsNotExist) {
//                    $errors[] = 'Email này đã tồn tại ... xin vui lòng ghi email khác';
//                }
//                $telephoneIsNotExist = $validator->noRecordExists($strTelephone, 'tbl_users', 'user_phone', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), $arrExclude);
//                if (!$telephoneIsNotExist) {
//                    $errors[] = 'Số điện thoại này đã tồn tại ... xin vui lòng ghi số điện thoại khác khác';
//                }
//                p($errors);
//                die;
                if (empty($errors)) {

                    $arrCity = $serviceCity->getDetail(array('city_id' => $params['city']));

                    $arrDistrict = $serviceDistrict->getDetail(array('dist_id' => $params['district']));

//                    list($day, $month, $year) = explode('-', $params['birthdate']);
                    $birthDate = mktime(0, 0, 0, $intMonth, $intDay, $intYear);

                    $strOldPassword ? $strPassword = md5($strNewPassword) : $strPassword = $getDetailtUser['user_password'];
                    $arrData = array(
                        'user_fullname' => $strName,
                        'user_password' => $strPassword,
                        'user_birthdate' => $birthDate,
                        'user_gender' => (int) $params['gender'],
//                        'user_phone' => $strTelephone,
//                        'user_email' => $strEmail,
                        'user_address' => $strAddress,
                        'city_id' => (int) $params['city'],
                        'dist_id' => (int) $params['district'],
                        'user_updated' => time(),
                    );

                    $intResult = $serviceUser->edit($arrData, UID);
                    // print_r($intResult);die;
                    if ($intResult > 0) {
                        if ($arrData['user_password'] == md5($strNewPassword)) {
                            //General::sendMail($arrData['user_email'], 'Đổi mật khẩu thành công', 'Mật khẩu hiện tại của bạn là : ' . $arrData['user_password']) 
                            $strEmail = $arrData['user_email'];
                            $strTitle = 'Megavita.vn Đổi mật khẩu thành công';
                            $strMessage = 'Mật khẩu hiện tại của bạn là : ' . $arrData['user_password'];
                            $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                            $instanceJobSendMail = new \My\Job\JobSendEmail();
                            $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);
                        }




                        $arrData['grou_id'] = $getDetailtUser["grou_id"];
                        $arrData['user_id'] = UID;
                        unset($arrData['user_password']);
                        $this->getAuthService()->getStorage()->write($arrData); //write lại auth
                        $this->flashMessenger()->setNamespace('success-edit-profile')->addMessage('Chỉnh sửa thông tin thành công !');
                        return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
                    }
                }
            }
        }
        $arrUser = $serviceUser->getDetail($arrCondition);
        if (empty($arrUser)) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $arrCity = $serviceCity->getList(array('city_status' => 0));
        $arrDistrict = $serviceDistrict->getList(array('city_id' => $arrUser['city_id'], 'dist_status' => 0));

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Cập nhật thông tin tài khoản ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Cập nhật thông tin tài khoản !'));

        return array(
            'params' => $params,
            'errors' => $errors,
            'arrCity' => $arrCity,
            'arrDistrict' => $arrDistrict,
            'arrUser' => $arrUser,
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

    public function changePasswordAction() {
        if (UID < 1) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $params = $this->params()->fromRoute();
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin !';
            }
            if (empty($params['oldPass'])) {
                $errors['oldPass'] = 'Vui lòng nhập mật khẩu hiện tại !';
            }
            $strOldPassword = trim($params['oldPass']);
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUserDetail = $serviceUser->getDetail(array('user_id' => UID));
            if (md5($strOldPassword) != $arrUserDetail['user_password']) {
                $errors['oldPass'] = 'Nhập mật khẩu hiện tại chưa chính xác !';
            }
            if (empty($params['newPass'])) {
                $errors['newPass'] = 'Vui lòng nhập mật khẩu mới !';
            }
            $strNewPassword = trim($params['newPass']);
            if (strlen($strNewPassword) < 6) {
                $errors['newPass'] = 'Mật khẩu phải từ 6 ký tự trở lên !';
            }
            if (trim($params['renewPass']) != $strNewPassword) {
                $errors['renewPass'] = 'Hai mật khẩu chưa giống nhau !';
            }
            if (empty($errors)) {
                $arrData = array(
                    'user_password' => md5($strNewPassword),
                    'user_updated' => time(),
                );
                $inResult = $serviceUser->edit($arrData, UID);
                if ($inResult) {
                    $arrData['grou_id'] = $getDetailtUser["grou_id"];
                    $arrData['user_id'] = UID;
                    unset($arrData['user_password']);
                    $this->getAuthService()->getStorage()->write($arrData); //write lại auth
                    $this->flashMessenger()->setNamespace('success-edit-profile')->addMessage('Cập nhật mật khẩu thành công !');
                    return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
                }
                $error[] = 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !';
            }
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Đổi mật khẩu tài khoản ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Đổi mật khẩu tài khoản !'));
        return array(
            'params' => $params,
            'errors' => $errors
        );
    }

}
