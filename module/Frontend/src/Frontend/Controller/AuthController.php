<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate;
use Zend\View\Model\ViewModel;

class AuthController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $servicePermission \My\Models\Permission */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */

    public function __construct() {

        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:auth:confirmresetpassword' => 'jquery.range.js,insilde.js',
            ];
            $this->defaultCSS = [
                'frontend:auth:confirmresetpassword' => 'jquery.range.css,checkbox.css',
            ];
        }

        if (FRONTEND_TEMPLATE == 'mobile') {
            $this->externalJS = [
                'frontend:auth:register' => STATIC_URL . '/f/mobile/js/my/??auth.js',
                'frontend:auth:forgot-password' => STATIC_URL . '/f/mobile/js/my/??auth.js',
            ];
        }
    }

    public function indexAction() {
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('frontend', array('controller' => 'auth', 'action' => 'login'));
    }

    public function loginAction() {
        $params = $this->params()->fromRoute();
//        p($params);die;
        if (UID) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'profile', 'action' => 'index'));
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p($params);die;
            $validator = new Validate();
            $str = strip_tags(trim($params['strUsername']));
            $strPassword = strip_tags(trim($params['strPassWord']));
            $strRemember = $params['remember'];
            $arrReturn = array('params' => $params);

            if (empty($str) || empty($strPassword)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập đầy đủ thông tin !</center>')));
            }

            if (substr_count($str, '@') == 1) {
                if (!$validator->emailAddress($str)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email không hợp lệ ... vui lòng điền lại email hoặc tên tài khoản !</center>')));
                }
                $arrCondition = array('user_email' => $str, 'not_user_status' => -1);
            } else {
                if ($validator->Regex($str, '/(.*)[^a-zA-Z0-9](.*)/')) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tên tài khoản không hợp lệ ... vui lòng điền lại email hoặc tên tài khoản !</center>')));
                }
                $arrCondition = array('user_name' => $str, 'not_user_status' => -1);
            }

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUser = $serviceUser->getDetail($arrCondition);

            if (empty($arrUser)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Tài khoản hoặc mật khẩu không chính xác! Vui lòng thử lại !</center>')));
            }

            if ($arrUser["user_status"] == 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản tạm khóa.<br/>Vui lòng liên hệ quản trị !</center>')));
            }

            if (md5($strPassword) != $arrUser['user_password'] && $strPassword != $arrUser['user_password']) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản hoặc mật khẩu không đúng! Vui lòng thử lại !</center>')));
            }

            $login = $serviceUser->edit(array("user_last_login" => time(), "user_login_ip" => $this->getRequest()->getServer('REMOTE_ADDR')), $arrUser["user_id"]);

            if ($login) {
                if ($strRemember == 'true') {
                    $arrCookieUser = array(
                        'Username' => $str,
                        'Password' => $arrUser['user_password']
                    );
                    setcookie('cookieUser', serialize($arrCookieUser), time() + (604800 * 4), "/");
                }
                if ($strRemember == 'false') {
                    setcookie('cookieUser', '', time() - 3600);
                }
                $this->getAuthService()->clearIdentity();
                unset($arrUser['user_password']);
                $this->getAuthService()->getStorage()->write($arrUser);

                return $this->getResponse()->setContent(json_encode(array('st' => 1)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
        }
    }

    public function signupAction() {
        $params = $this->params()->fromRoute();
        if (UID) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'profile', 'action' => 'index'));
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p($params);die;
            if ($params && is_array($params)) {
                $errors = array();
                $validator = new Validate();
                $strPassword = strip_tags(trim($params['strPassWord']));
                $strRePassword = strip_tags(trim($params['strRePassword']));
                $strName = trim($params['strUsername']);
                $strFullName = strip_tags(trim($params['strFullname']));
                $strEmail = strip_tags(trim($params['strEmail']));
                $strPhoneNumber = strip_tags(trim($params['strPhone']));
                //validate Fullname
                if (!$validator->notEmpty($strFullName)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Họ và tên không được bỏ trống!</center>')));
                }

                //validate Username
                if (!$validator->notEmpty($strName)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tên tài khoản không được bỏ trống!</center>')));
                }

                if (!$validator->Between(strlen($strName), 6, 255)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tên tài khoản phải từ 6 ký tự trở lên!</center>')));
                }

                if ($validator->Regex($strName, '/(.*)[^a-zA-Z0-9](.*)/')) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tên tài khoản không được có kí tự đặt biệt ... vui lòng tài khoản khác !</center>')));
                }

                $nameIsNotExist = $validator->noRecordExists($strName, 'tbl_users', 'user_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));

                if (!$nameIsNotExist) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tên tài khoản này đã tồn tại ... xin vui lòng đổi tài khoản khác!</center>')));
                }

                //validate Email
                if (!$validator->notEmpty($strEmail)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không được bỏ trống !</center>')));
                }

                if (!$validator->emailAddress($strEmail)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không không đúng định dạng!</center>')));
                }

                $emailIsNotExist = $validator->noRecordExists($strEmail, 'tbl_users', 'user_email', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
                if (!$emailIsNotExist) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email này đã tồn tại ... xin vui lòng đổi email khác!</center>')));
                }

                //validate Password
                if (!$validator->notEmpty($strPassword)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mật khẩu không được bỏ trống!</center>')));
                }

                if (strlen($strPassword) < 6) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Mật khẩu phải từ 6 ký tự trở lên!</center>')));
                }

                if ($strPassword != $strRePassword) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Hai mật khẩu chưa giống nhau!</center>')));
                }

                //validate Phone number
                if (!$validator->notEmpty($strPhoneNumber)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại không được bỏ trống!</center>')));
                }

                if (!$validator->Digits($strPhoneNumber)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại phải là số! Từ 8 đến 11 số!</center>')));
                }
                if (!$validator->Between(strlen($strPhoneNumber), 8, 11)) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại phải là số! Từ 8 đến 11 số!</center>')));
                }

                $telephoneIsNotExist = $validator->noRecordExists($strPhoneNumber, 'tbl_users', 'user_phone', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));

                if (!$telephoneIsNotExist) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Số điện thoại này đã tồn tại ... xin vui lòng đổi số điện thoại khác khác!</center>')));
                }

                //validate birthday 
                if (empty($params['strMoth']) || empty($params['strDay']) || empty($params['strYear'])) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập đầy đủ ngày/tháng/năm sinh!</center>')));
                }

                $birthDate = mktime(0, 0, 0, $params['strMoth'], $params['strDay'], $params['strYear']);

                //validate Gender
                if (!$validator->notEmpty($params['strGender'])) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng chọn giới tính!</center>')));
                }

//                validate Captcha
                if (!$validator->notEmpty($params['strCaptcha'])) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập mã xác nhận/center>')));
                }

                if ($params['strCaptcha'] != $_SESSION['captcha']) {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Nhập mã xác nhận chưa chính xác!</center>')));
                }

                $arrData = array(
                    'user_name' => $strName,
                    'user_fullname' => $strFullName,
                    'user_email' => $strEmail,
                    'user_phone' => $strPhoneNumber,
                    'user_birthdate' => $birthDate,
                    'user_gender' => (int) $params['strGender'],
                    'user_password' => md5($strPassword),
                    'user_status' => 1,
                    'user_created' => time(),
                    'user_last_login' => time(),
                    'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                );

                //insert user in table users
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $intResult = $serviceUser->add($arrData);

                if ($intResult > 0) {
                    $arrUser = $serviceUser->getDetail(array('user_email' => $strEmail, 'not_user_status' => -1));
                    $this->getAuthService()->clearIdentity();
                    unset($arrUser['user_password']);
                    $this->getAuthService()->getStorage()->write($arrUser);
                    //get template Email SEND FOr USER
                    $arrData['user_id'] = $intResult;
                    $view = new ViewModel($arrData);
                    $view->setTemplate('frontend/template_register_user');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $strMessage = $viewRender->render($view);
                    //Send Mail
                    $strTitle = '[Megavita.Vn] Qúy khách vừa đang ký tài khoản thành công';
                    //Nội dung email
                    // $result = General::sendMail($strEmail, $strTitle, $strMessage);
                    // WORKER SEND MAIL
                    $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                    $instanceJobSendMail = new \My\Job\JobSendEmail();
                    $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);
                    return $this->getResponse()->setContent(json_encode(array('st' => 1)));
                }
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
        }
    }

    public function resetPasswordAction() {
        $params = $this->params()->fromPost();
//        p($params);die;
        if (empty($params)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Vui lòng nhập đầy đủ thông tin!</center>')));
        }

        if (empty($params['strEmail'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập địa chỉ email!</center>')));
        }

        if (empty($params['strCaptcha'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Chưa nhập mã xác nhận!</center>')));
        }

        $strEmail = trim($params['strEmail']);
        $strCaptcha = trim($params['strCaptcha']);

        if ($strCaptcha != $_SESSION['captcha']) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Nhập mã xác nhận chưa chính xác!</center>')));
        }

        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrDetailUser = $serviceUser->getDetail(array('user_email' => $strEmail));

        if (!$arrDetailUser) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Email này không tồn tại trong hệ thống !</center>')));
        }
        if ($arrDetailUser) {
            if ($arrDetailUser['user_status'] == -1) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Tài khoản của bạn hiện đang bị tạm khóa! Vui lòng liên hệ quản trị viên !</center>')));
            }
            $random_key = md5(rand(5, 1000)) . time();
            $intResult = $serviceUser->edit(array('user_randomkey' => $random_key), $arrDetailUser['user_id']);
            $url = $this->serviceLocator->get('viewhelpermanager')->get('URL');

//            p($intResult);die;
            if ($intResult) {
                //Send Mail
                $General = new General();
                //tiêu đề Email
                $strTitle = '[Megavita.Vn] Reset Mật khẩu !';
//                $router = $this->toRoute('auth', array('controller' => 'auth', 'action' => 'confirm-reset-password'));
//                p($router);die;
                //Nội dung email
                $strMessage = '<h3>Chúng tôi nhận được yêu cầu lấy lại mật khẩu từ bạn!</h3><br/>'
                        . 'Nếu đúng là yêu cầu của bạn! Xin vui lòng click vào link bên dưới để tiến hành reset mật khẩu:<br/>'
                        . '<a href="' . BASE_URL . $url('forget-pasword', array('controller' => 'Auth', 'action' => 'confirmResetPassword')) . '?randomkey=' . $random_key . '">Kích vào đây </a><br/><br/>'
                        . 'Nếu không phải là yêu cầu của bạn! Xin vui lòng bỏ qua!<br/><br/>'
                        . '<h2><b>Megavita - SIÊU THỊ SỐNG KHỎE VÀ ĐẸP </b></h2>';

                //   $result = $General->sendMail($strEmail, $strTitle, $strMessage);
                $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                $instanceJobSendMail = new \My\Job\JobSendEmail();
                $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => '<center><br/> <b>Reset mật khẩu thành công !</b><br/> <br/><b>Mời bạn kiểm tra email để lấy lại mật khẩu!</b><br/><br/></center>')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
    }

    public function confirmResetPasswordAction() {
        $params = $this->params()->fromQuery();
        if (UID) {
            return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
        }
        if (empty($params['randomkey'])) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $strRandomkey = $params['randomkey'];
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrDetailUser = $serviceUser->getDetail(array('user_randomkey' => $params['randomkey']));

        if (!$arrDetailUser) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();
            if (empty($params['strPassword']) || empty($params['strRePassword'])) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            $strPassword = trim($params['strPassword']);

            if (strlen($strPassword) < 6) {
                $errors['Password'] = 'Mật khẩu phải từ 6 ký tự trở lên !';
            }

            if ($strPassword != trim($params['strRePassword'])) {
                $errors['Password'] = 'Hai mật khẩu chưa giống nhau !';
            }

            if (empty($errors)) {
                $arrData = array(
                    'user_password' => md5($strPassword),
                    'user_randomkey' => NULL,
                    'user_updated' => time()
                );
                $intResult = $serviceUser->edit($arrData, $arrDetailUser['user_id']);
                if ($intResult) {
                    if (FRONTEND_TEMPLATE == 'mobile') {
                        $router = $this->toRoute('auth', array('controller' => 'auth', 'action' => 'sigin'));
                        return array(
                            'success' => '<center> Đổi mật khẩu thành công! Mời bạn <a class="cr-red" style="font-size:24px" href="' . $router . '">click vào đây </a> để đăng nhập !<center>',
                        );
                    }
                    return array(
                        'success' => 'Đổi mật khẩu thành công! Mời bạn đăng nhập !',
                    );
                }
                $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại';
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
        );
    }

    public function logoutAction($redirect = true) {
        $this->getAuthService()->clearIdentity();
        if ($redirect) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
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

    public function siginAction() {
        $params = $this->params()->fromRoute();

        if (UID) {
            return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
        }
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//            p($params);die;
            $validator = new Validate();
            $str = strip_tags(trim($params['strUsername']));
            $strPassword = strip_tags(trim($params['strPassWord']));
            $strRemember = $params['remember'];
            $arrReturn = array('params' => $params);

            if (empty($str) || empty($strPassword)) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin !';
            }

            if (substr_count($str, '@') == 1) {
                if (!$validator->emailAddress($str)) {
                    $errors['username'] = 'Email không hợp lệ ... vui lòng điền lại email hoặc tên tài khoản !';
                }
                $arrCondition = array('user_email' => $str, 'not_user_status' => -1);
            } else {
                if ($validator->Regex($str, '/(.*)[^a-zA-Z0-9](.*)/')) {
                    $errors['username'] = 'Tên tài khoản không hợp lệ ... vui lòng điền lại email hoặc tên tài khoản !';
                }
                $arrCondition = array('user_name' => $str, 'not_user_status' => -1);
            }

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUser = $serviceUser->getDetail($arrCondition);

            if (empty($arrUser)) {
                $errors[] = 'Tài khoản hoặc mật khẩu không chính xác! Vui lòng thử lại !';
            }

            if ($arrUser["user_status"] == 0) {
                $errors['user_status'] = 'Tài khoản tạm khóa. Vui lòng liên hệ quản trị ! !';
            }

            if (md5($strPassword) != $arrUser['user_password'] && $strPassword != $arrUser['user_password']) {
                $errors[] = 'Tài khoản hoặc mật khẩu không đúng! Vui lòng thử lại !';
            }

            if (empty($errors)) {

                $login = $serviceUser->edit(array("user_last_login" => time(), "user_login_ip" => $this->getRequest()->getServer('REMOTE_ADDR')), $arrUser["user_id"]);
                if ($login) {
                    if ($strRemember == 'true') {
                        $arrCookieUser = array(
                            'Username' => $str,
                            'Password' => $arrUser['user_password']
                        );
                        setcookie('cookieUser', serialize($arrCookieUser), time() + (604800 * 4), "/");
                    }
                    if ($strRemember == 'false') {
                        setcookie('cookieUser', '', time() - 3600);
                    }
                    $this->getAuthService()->clearIdentity();
                    unset($arrUser['user_password']);
                    $this->getAuthService()->getStorage()->write($arrUser);
                    return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
                }
                $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !';
            }
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Đăng nhập vào hệ thống ! ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Đăng nhập vào hệ thống !'));

        return array(
            'params' => $params,
            'errors' => $errors,
        );
    }

    public function registerAction() {
        $params = $this->params()->fromRoute();
        if (UID) {
            return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
        }
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {
                $validator = new Validate();
                $strPassword = strip_tags(trim($params['strPassWord']));
                $strRePassword = strip_tags(trim($params['strRePassword']));
                $strName = trim($params['strUsername']);
                $strFullName = strip_tags(trim($params['strFullname']));
                $strEmail = strip_tags(trim($params['strEmail']));
                $strPhoneNumber = strip_tags(trim($params['strPhone']));
                //validate Fullname
                if (!$validator->notEmpty($strFullName)) {
                    $errors['fullname'] = 'Họ và tên không được bỏ trống!';
                }

                //validate Username
                if (!$validator->notEmpty($strName)) {
                    $errors['user_name'] = 'Tên tài khoản không được bỏ trống!';
                }

                if (!$validator->Between(strlen($strName), 6, 255)) {
                    $errors['user_name'] = 'Tên tài khoản phải từ 6 ký tự trở lên! Và không được có kí tự đặt biệt !';
                }

                if ($validator->Regex($strName, '/(.*)[^a-zA-Z0-9](.*)/')) {
                    $errors['user_name'] = 'Tên tài khoản phải từ 6 ký tự trở lên! Và không được có kí tự đặt biệt !';
                }

                $nameIsNotExist = $validator->noRecordExists($strName, 'tbl_users', 'user_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));

                if (!$nameIsNotExist) {
                    $errors['user_name'] = 'Tên tài khoản này đã tồn tại ... xin vui lòng đổi tài khoản khác!';
                }

                //validate Email
                if (!$validator->notEmpty($strEmail)) {
                    $errors['user_email'] = 'Địa chỉ email không được bỏ trống';
                }

                if (!$validator->emailAddress($strEmail)) {
                    $errors['user_email'] = 'Địa chỉ email không không đúng định dạng!';
                }

                $emailIsNotExist = $validator->noRecordExists($strEmail, 'tbl_users', 'user_email', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
                if (!$emailIsNotExist) {
                    $errors['user_email'] = 'Email này đã tồn tại ... xin vui lòng đổi email khác!';
                }

                //validate Password
                if (!$validator->notEmpty($strPassword)) {
                    $errors['user_password'] = 'Mật khẩu không được bỏ trống!';
                }

                if (strlen($strPassword) < 6) {
                    $errors['user_password'] = 'Mật khẩu phải từ 6 ký tự trở lên!';
                }

                if ($strPassword != $strRePassword) {
                    $errors['user_repassword'] = 'Hai mật khẩu chưa giống nhau';
                }

                //validate Phone number
                if (!$validator->notEmpty($strPhoneNumber)) {
                    $errors['user_phone'] = 'Số điện thoại không được bỏ trống!';
                }

                if (!$validator->Digits($strPhoneNumber)) {
                    $errors['user_phone'] = 'Số điện thoại phải là số! Từ 8 đến 11 số!';
                }
                if (!$validator->Between(strlen($strPhoneNumber), 8, 11)) {
                    $errors['user_phone'] = 'Số điện thoại phải là số! Từ 8 đến 11 số!';
                }

                $telephoneIsNotExist = $validator->noRecordExists($strPhoneNumber, 'tbl_users', 'user_phone', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));

                if (!$telephoneIsNotExist) {
                    $errors['user_phone'] = 'Số điện thoại này đã tồn tại ... xin vui lòng đổi số điện thoại khác khác!';
                }

                //validate birthday 
                if (empty($params['strMoth']) || empty($params['strDay']) || empty($params['strYear'])) {
                    $errors['user_bithday'] = 'Vui lòng nhập đầy đủ ngày/tháng/năm sinh!';
                }

                $birthDate = mktime(0, 0, 0, $params['strMoth'], $params['strDay'], $params['strYear']);

                //validate Gender
                if (!$validator->notEmpty($params['strGender'])) {
                    $errors['user_gender'] = 'Vui lòng chọn giới tính!';
                }

//                validate Captcha
                if (!$validator->notEmpty($params['strCaptcha'])) {
                    $errors['captcha'] = 'Chưa nhập mã xác nhận !';
                }

                if ($params['strCaptcha'] != $_SESSION['captcha']) {
                    $errors['captcha'] = 'Nhập mã xác nhận chưa chính xác !';
                }

                if (empty($errors)) {

                    //insert user in table users

                    $arrData = array(
                        'user_name' => $strName,
                        'user_fullname' => $strFullName,
                        'user_email' => $strEmail,
                        'user_phone' => $strPhoneNumber,
                        'user_birthdate' => $birthDate,
                        'user_gender' => (int) $params['strGender'],
                        'user_password' => md5($strPassword),
                        'user_status' => 1,
                        'user_created' => time(),
                        'user_last_login' => time(),
                        'user_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
                    );
                    $serviceUser = $this->serviceLocator->get('My\Models\User');
                    $intResult = $serviceUser->add($arrData);

                    if ($intResult > 0) {
                        $arrUser = $serviceUser->getDetail(array('user_email' => $strEmail, 'not_user_status' => -1));
                        $this->getAuthService()->clearIdentity();
                        unset($arrUser['user_password']);
                        $this->getAuthService()->getStorage()->write($arrUser);
                        return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
                    }
                    $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại ! ';
                }
            }
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Tài khoản - Đăng ký tài khoản ') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Tài khoản - Đăng ký tài khoản tại Megavita.vn !'));
//        p(array(
//            'params' => $params,
//            'errors' => $errors
//        ));die;
        return array(
            'params' => $params,
            'errors' => $errors
        );
    }

    public function forgotPasswordAction() {
        $params = $this->params()->fromRoute();
        if (UID) {
            return $this->redirect()->toRoute('profile', array('controller' => 'profile', 'action' => 'index'));
        }
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin!';
            }

            if (empty($params['strEmail'])) {
                $errors['email'] = 'Chưa nhập địa chỉ email!';
            }

            if (empty($params['strCaptcha'])) {
                $errors['captcha'] = 'Chưa nhập mã xác nhận!';
            }

            $strEmail = trim($params['strEmail']);
            $strCaptcha = trim($params['strCaptcha']);

            if ($strCaptcha != $_SESSION['captcha']) {
                $errors['captcha'] = 'Nhập mã xác nhận chưa chính xác!';
            }

            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrDetailUser = $serviceUser->getDetail(array('user_email' => $strEmail));

            if (!$arrDetailUser) {
                $errors['email'] = 'Email này không tồn tại trong hệ thống !';
            }

            if ($arrDetailUser['user_status'] == -1) {
                $errors['email'] = 'Tài khoản của bạn hiện đang bị tạm khóa! Vui lòng liên hệ quản trị viên !';
            }
            if (empty($errors)) {
                $random_key = md5(rand(5, 1000)) . time();
                $intResult = $serviceUser->edit(array('user_randomkey' => $random_key), $arrDetailUser['user_id']);
                if ($intResult) {
                    //Send Mail
                    $General = new General();
                    //tiêu đề Email
                    $strTitle = '[Megavita.Vn] Reset Mật khẩu !';
                    //Nội dung email
                    $strMessage = '<h3>Chúng tôi nhận được yêu cầu lấy lại mật khẩu từ bạn!</h3><br/>'
                            . 'Nếu đúng là yêu cầu của bạn! Xin vui lòng click vào link bên dưới để tiến hành reset mật khẩu:<br/>'
                            . '<a href="http://dev.megavitav2.vn/auth-confirm-reset-password?randomkey=' . $random_key . '">Kích vào đây </a><br/><br/>'
                            . 'Nếu không phải là yêu cầu của bạn! Xin vui lòng bỏ qua!<br/><br/>'
                            . '<h2><b>Megavita - SIÊU THỊ SỐNG KHỎE VÀ ĐẸP </b></h2>';
                    // $result = $General->sendMail($strEmail, $strTitle, $strMessage);
                    $arrMail = array('to' => $strEmail, 'subject' => $strTitle, 'body' => $strMessage);
                    $instanceJobSendMail = new \My\Job\JobSendEmail();
                    $instanceJobSendMail->addJob(SEARCH_PREFIX . 'send_mail', $arrMail);
                    return array(
                        'success' => 'Lấy lại mật khẩu thành công!<br/>'
                        . ' - Mời bạn check email để lấy lại mật khẩu!'
                    );
                }
                $errors[] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !';
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors
        );
    }

}
