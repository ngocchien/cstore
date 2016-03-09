<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class CommentController extends MyController {
    /* @var $serviceComment \My\Models\Comment */

    public function __construct() {
        $this->defaultJS = [
            'backend:comment:index' => 'jquery.validate.min.js',
        ];

        $this->externalJS = [
            'backend:comment:index' => STATIC_URL . '/b/js/my/??comment.js',
            'backend:comment:edit' => array(
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
                STATIC_URL . '/b/js/my/??comment.js',
            ),
            'backend:comment:editComment' => array(
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
                STATIC_URL . '/b/js/my/??comment.js',
            ),
        
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array(
            'not_comm_status' => -1,
            'content_or_fullname_or_email' => General::clean(trim($this->params()->fromQuery('s'))),
            'comm_parent' => 0
        );
        $intLimit = $this->params()->fromRoute('limit', 15);
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intTotal = $serviceComment->getTotal($arrCondition);
        $arrCommentList = $serviceComment->getListParentLimit($arrCondition, $intPage, $intLimit);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'comment', 'action' => 'index', 'page' => $intPage));
        $params = array_merge($params, $this->params()->fromQuery());

        return array(
            'errors' => $errors,
            'params' => $params,
            'paging' => $paging,
            'arrCommentList' => $arrCommentList,
        );
    }

    public function editCommentAction() {
        $params = $this->params()->fromPost();
        if (empty($params['comm_id'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $arrData = array('comm_id' => $params['comm_id'], 'not_comm_status' => -1);
        $array_out = array();
        if (isset($params['comm_content']) || isset($params['comm_status']) || isset($params['user_fullname']) || isset($params['user_email'])) {
            $arrData = array(
                "comm_content" => trim($params['comm_content']),
                "comm_status" => $params['comm_status'],
                "user_fullname" => $params['user_fullname'],
                "user_email" => $params['user_email'],
                "comm_updated" => time()
            );
            $intResult = $serviceComment->edit($arrData, $params['comm_id']);
            $array_out = array('error' => 0, 'success' => 1, 'message' => ' Sửa comment hoàn tất !!!');
        } else {
            $intResult = $serviceComment->getDetail($arrData);
            $array_out = array('error' => 0, 'success' => 1, 'message' => 'Load success!!!', 'data' => $intResult);
        }

        if ($intResult) {
            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                        'user_id'=>UID,
                        'logs_controller'=>'Comment',
                        'logs_action'=>'edit',
                        'logs_time'=>time(),
                        'logs_detail'=>'Chỉnh sửa Bình luận có id = '.$params['comm_id'],
                    );
            $serviceLogs -> add($arrLogs);
            return $this->getResponse()->setContent(json_encode($array_out));
        }


        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }
    public function editAction(){
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'content', 'action' => 'index'));
        }
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $commentList = $serviceComment->getTotalById(array('comm_id_or_parent_id' => $params['id'],'not_comm_status'=> -1));
        $commentParent = $serviceComment->getDetail(array('comm_id' => $params['id']));
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if(empty($params['comm_content'])){
                $errors['comm_content'] = 'Nhập nội dung comment!';
            }
            if(empty($errors)){
                if($params['user_type'] == 1){
                    $uid = UID;//admin
                    $fullname = FULLNAME;
                    $email = EMAIL;
                }
                else{
                    $uid = $commentParent['user_id'];
                    $fullname = $commentParent['user_fullname'];
                    $email = $commentParent['user_email'];
                }
                $arrData = array(
                    'comm_content' => $params['comm_content'],
                    'comm_created' => time(),
                    'comm_ip' => General::getRealIpAddr(),
                    'user_id' => $uid,
                    'user_fullname' => $fullname,
                    'user_email' => $email,
                    'prod_id' => $commentParent['prod_id'],
                    'cont_id' => $commentParent['cont_id'],
                    'comm_parent' => $commentParent['comm_id'],
                    'comm_type' => 1,
                    'comm_status' => 1,
                );
                $result = $serviceComment->add($arrData);
                if($result){
                    $this->redirect()->toRoute('backend', array('controller' => 'comment', 'action' => 'edit', 'id' => $commentParent['comm_id']));
                }
            }
        }
        return array(
            'ListComment' => $commentList,
            'params' => $params
        );
    }

    public function censorAction() {
        $params = $this->params()->fromPost();
        if (empty($params['comm_id'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $arrData = array('comm_status' => 1);
        $intResult = $serviceComment->edit($arrData, $params['comm_id']);
        if ($intResult) {
            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Hoàn tất !!!')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

    public function deleteAction() {
        $params = $this->params()->fromPost();
        $error = array();
        if (empty($params['comm_id'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $arrData = array('comm_status' => -1);
        $intResult = $serviceComment->edit($arrData, $params['comm_id']);
        if ($intResult) {
            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                        'user_id'=>UID,
                        'logs_controller'=>'Comment',
                        'logs_action'=>'delete',
                        'logs_time'=>time(),
                        'logs_detail'=>'Xóa Bình luận có id = '.$params['comm_id'],
                    );
            $serviceLogs -> add($arrLogs);
            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Bình luận hoàn tất')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

}
