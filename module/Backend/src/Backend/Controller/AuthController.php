<?php

namespace Backend\Controller;

use Zend\Mvc\MvcEvent,
    Zend\Crypt\Password\Bcrypt,
    Zend\Mvc\Controller\AbstractActionController;
use My\General;

class AuthController extends AbstractActionController {
    /* @var $serviceUser \My\Models\User */

    protected $storage;
    protected $authservice;

    public function onDispatch(MvcEvent $event) {
        $event->getViewModel()->setTemplate('backend/auth');
        return parent::onDispatch($event);
    }

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->serviceLocator->get('AuthService');
        }
        return $this->authservice;
    }

    public function indexAction() {
        $this->getAuthService()->clearIdentity();
        return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
    }

    public function loginAction() {
        $params = $this->params()->fromRoute();
        if ($this->getAuthService()->hasIdentity()) {
            $arrUserData = $this->getAuthService()->getIdentity();
            if ($arrUserData['grou_id'] == General::MEMBER) {
                return $this->redirect()->toRoute('home');
            }
            return $this->redirect()->toRoute('backend');
        }
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $strEmail = trim($params['email']);
            $strPassword = trim($params['password']);
            $params['remember'] ? $intRemember = (int) $params['remember'] : $intRemember = 0;

            $arrReturn = array('params' => $params);
            if (empty($strEmail) || empty($strPassword)) {
                $arrReturn['error']['empty-username-password'] = 'Vui lòng nhập Email và mật khẩu.';
                return $arrReturn;
            }
            $serviceUser = $this->serviceLocator->get('My\Models\User');
            $arrUser = $serviceUser->getDetail(array('user_email_or_user_name' => $strEmail, 'not_user_status' => -1));
            if (empty($arrUser)) {
                $arrReturn['error']['empty-username-password'] = 'Email không tồn tại trong hệ thống, xin vui lòng kiểm tra lại.';
                return $arrReturn;
            }

            if ($arrUser["user_status"] == 0) {
                $arrReturn['error']['empty-username-password'] = 'Tài khoản tạm khóa.<br/>Vui lòng liên hệ quản trị';
                return $arrReturn;
            }

            if (md5($strPassword) != $arrUser['user_password']) {
                $arrReturn['error']['empty-username-password'] = 'Mật khẩu không chính xác, xin vui lòng kiểm tra lại.';
                return $arrReturn;
            }

            $serviceUser->edit(
                    array(
                "user_last_login" => time(),
                "user_login_ip" => $this->getRequest()->getServer('REMOTE_ADDR')
                    ), $arrUser["user_id"]
            );

            $servicePermission = $this->serviceLocator->get('My\Models\Permission');
            $arrUser["permission"] = $servicePermission->getListjoinRole(array("grou_id" => $arrUser["grou_id"], "perm_status" => 1));
            $serviceGroup = $this->serviceLocator->get('My\Models\Group');
            $arrGroup = $serviceGroup->getDetail(array('grou_id' => $arrUser["grou_id"]));
            $this->getAuthService()->clearIdentity();
            $arrUser['is_acp'] = $arrGroup['is_acp'];
            $this->getAuthService()->getStorage()->write($arrUser);

            return $this->redirect()->toRoute('backend');
        }



        return array(
            'params' => $params,
        );
    }

    public function logoutAction($redirect = true) {
        $this->getAuthService()->clearIdentity();
        if ($redirect) {
            return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
        }
    }

}
