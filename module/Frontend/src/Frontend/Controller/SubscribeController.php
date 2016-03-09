<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\Validator\Validate;

class SubscribeController extends MyController {
    
    /* @var $serviceSubscribe \My\Models\Subscribe */

    public function __construct() {
        $this->externalJS = [
        ];
    }

    public function addAction() {
        $params = $this->params()->fromPost();
        if (empty($params['EmailSubs'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Vui lòng nhập Email trước khi nhấn đăng ký !')));
        }
        $strEmail = trim($params['EmailSubs']);
        $validator = new Validate();
        if (!$validator->emailAddress($strEmail)) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Email không đúng định dạng !')));
        }
        $serviceSubscribe = $this->serviceLocator->get('My\Models\Subscribe');
        $detailEmailSubs = $serviceSubscribe->getDetail(array('subs_email' => $strEmail));
        if (count($detailEmailSubs) == 0) {
            $arrData = array(
                'subs_email' => $strEmail,
                'subs_created' => time(),
            );
            $intResult = $serviceSubscribe->add($arrData);
            if ($intResult > 0) {
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Đăng ký thành công !')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !')));
        }

        if ((count($detailEmailSubs) != 0) && ($detailEmailSubs['subs_status'] == 1)) {
            $arrData = array(
                'subs_email' => $strEmail,
                'subs_created' => time(),
                'subs_status' => -1
            );
            $intResult = $serviceSubscribe->edit($arrData, $detailEmailSubs['subs_id']);
            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Hủy Đăng ký thành công !')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !')));
        }

        if ((count($detailEmailSubs) != 0) && ($detailEmailSubs['subs_status'] == -1)) {
            $arrData = array(
                'subs_email' => $strEmail,
                'subs_created' => time(),
                'subs_status' => 1
            );
            $intResult = $serviceSubscribe->edit($arrData, $detailEmailSubs['subs_id']);
            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Đăng ký thành công !')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !')));
        }
    }

}
