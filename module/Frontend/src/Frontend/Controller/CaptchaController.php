<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class CaptchaController extends MyController {

    public function __construct() {
        
    }

    public function indexAction() {
//         p(CAPTCHA_URL);die;   
        $general = new General();
        $maxWordLength = 6;
        $width = 100;
        $height = 30;
        $captcha = $general->generateCaptcha($maxWordLength, $width, $height);
        $_SESSION['captcha'] = $captcha['word'];
        die(json_encode($captcha['url']));
    }
    
//    public function checkAction(){
//        $params = $this->params()->fromPost();
//        if(empty($params['CaptchaWord'])){
//            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Chưa nhập mã xác nhận !')));
//        }
//        if($_SESSION['captcha']!= $params['CaptchaWord']){
//            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Nhập sai mã xác nhận !')));
//        }
//        if($_SESSION['captcha']== $params['CaptchaWord']){
//            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1)));
//        }
//    }
}
