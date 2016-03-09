<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\Validator\Validate,
    Zend\View\Model\ViewModel;

class CommentController extends MyController {
    /* @var $serviceComment \My\Models\Comment */
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceGroup \My\Models\Group */

    public function __construct() {
        
    }

    public function indexAction() {
        
    }

    public function addAction() {
        $params = $this->params()->fromPost();
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (empty($params['ProductID']) && empty($params['ContentID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại !')));
        }

        if (empty($params['Fullname'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập họ và tên !')));
        }

        if (empty($params['Email'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xin vui lòng nhập Email !')));
        }

        $validator = new Validate();
        if (!$validator->emailAddress($params['Email'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Email không đúng định dạng !')));
        }

        if (empty($params['Content'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xin vui lòng nhập Nội dung bình luận !')));
        }

        $strcontent = strip_tags(trim($params['Content']));
        if (strlen($strcontent) < 5 || strlen($strcontent) > 1000) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Nội dung bình luận phải từ 5 đến 1000 ký tự !')));
        }

        $parent = (empty($params['Parent'])) ? 0 : $params['Parent'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ipaddress = $client;
        }
        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ipaddress = $forward;
        }
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            $ipaddress = $remote;
        }
        
        $groupName = '';
        if (UID) {
            $serviceGroup = $this->serviceLocator->get('My\Models\Group');
            $arrGroup = $serviceGroup->getDetail(array('grou_id' => GROU_ID));
            $groupName = $arrGroup['grou_name'];
        }
        $arrData = array(
            'prod_id' => $params['ProductID'],
            'cont_id' => $params['ContentID'],
            'user_id' => UID ? (int) UID : 0,
            'user_fullname' => FULLNAME ? FULLNAME : strip_tags(trim($params['Fullname'])),
            'user_email' => EMAIL ? EMAIL : strip_tags(trim($params['Email'])),
            'comm_content' => $strcontent,
            'comm_parent' => $parent,
            'comm_ip' => $ipaddress,
            'comm_status' => 0,
            'user_name_group' => $groupName,
            'comm_type'=> (!empty($params['ProductID'])) ? 1 : 2 ,
            'comm_created' => time()
        );

        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intResult = $serviceComment->add($arrData);
        if ($intResult > 0) {
            if ($arrData['user_id'] >= 1) {
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $arrUser = $serviceUser->getDetail(array('user_id' => $arrData['user_id']));
                $arrData['avatar'] = json_decode($arrUser['user_avatar'], true)[0]['thumbImage']['50x50'];
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $arrData, 'commentID' => $intResult)));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong qua trình xử lý, vui lòng thử lại !')));
        die();
    }

    public function likeAction() {

        $params = $this->params()->fromPost();
//        p($params);die;
        if (empty($params['CommentID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý !')));
        }
//        p($params);die;
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intResult = $serviceComment->editlike($params['CommentID']);
        if ($intResult > 0) {
            return $this->getResponse()->setContent(json_encode(array('st' => 1)));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong qua trình xử lý, vui lòng thử lại !')));
        die();
    }

    public function getCommentAction() {
        $paramsRouter = $this->params()->fromRoute();
//            p($paramsRouter);die;
        $params = $this->params()->fromPost();
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ipaddress = $client;
        }
        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ipaddress = $forward;
        }
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            $ipaddress = $remote;
        }
        
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetaiProduct = $serviceProduct->getDetail(array('prod_id' => $params['ProductID'], 'prod_status' => 1));
//        $instanceSearchProduct = new \My\Search\Products();
//        $arrCondition = array();
//        $arrCondition['prod_id'] = $params['ProductID'];
//        $arrCondition['prod_status'] = 1;
//        $instanceSearchProduct->setParams($arrCondition);
//        $arrDetaiProduct = $instanceSearchProduct->getDetail();
        
        $intPage = $params['page'];
        $intLimit = 10;
        $arrCondition = array('prod_id' => $params['ProductID'], 'comm_status' => 1, 'comm_parent' => 0, 'comm_ip' => $ipaddress);
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intTotalComment = $serviceComment->getTotalInProduct($arrCondition);
        $arrParentCommentList = $serviceComment->getListLimitInProduct($arrCondition, $intPage, $intLimit, 'comm_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $pagingComment = $helper($paramsRouter['module'], $paramsRouter['__CONTROLLER__'], $paramsRouter['action'], $intTotalComment, $intPage, $intLimit, 'product', array('controller' => 'product', 'action' => 'getCommentInProduct', 'page' => $intPage));
        
        //get List Children Comment
        //get list userComment
        $totalComment = 0;
        $arrListCommentChildren = array();
        if (count($arrParentCommentList) > 0) {
            $totalComment = $totalComment + count($arrParentCommentList);
            foreach ($arrParentCommentList as $key => $value) {
                $listIdParent[] = $value['comm_id'];
                $listIdUser[] = $value['user_id'];
            }
            $arrListCommentChildren = array();
            if (count($listIdParent) > 0) {
                $strlistIdParent = implode(',', $listIdParent);
                $listCommentChildren = $serviceComment->getListChildren(array('listIdParen' => $strlistIdParent, 'comm_status' => 1, 'comm_ip' => $ipaddress));
                if (count($listCommentChildren) > 0) {
                    foreach ($listCommentChildren as $value) {
                        $totalComment = $totalComment + 1;
                        $arrListCommentChildren[$value['comm_parent']][] = $value;
                        $listIdUser[] = $value['user_id'];
                    }
                }
            }
            //get info user Comment
            $listIdUser = array_unique($listIdUser);
            $arrListUserComment = array();
            if (count($listIdUser) > 0) {
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $strListId = implode(',', $listIdUser);
                $listUserComment = $serviceUser->getList(array('listUserID' => $strListId));
                if (count($listUserComment) > 0) {
                    foreach ($listUserComment as $valueUser) {
                        $arrListUserComment[$valueUser['user_id']] = $valueUser;
                    }
                }
            }
        }
        $arrData = array(
            'pagingComment' => $pagingComment,
            'arrParentCommentList' => $arrParentCommentList,
            'arrListUserComment' => $arrListUserComment,
            'arrListCommentChildren' => $arrListCommentChildren,
            'totalComment' => $totalComment,
            'arrDetaiProduct'=>$arrDetaiProduct
        );
        
        $view = new ViewModel($arrData);
        $view->setTemplate('frontend/layout/comment');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
        die;
    }
    
    public function getCommentContentAction() {
        $paramsRouter = $this->params()->fromRoute();
//            p($paramsRouter);die;
        $params = $this->params()->fromPost();
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ipaddress = $client;
        }
        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ipaddress = $forward;
        }
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            $ipaddress = $remote;
        }
        
        $intPage = $params['page'];
        $intLimit = 10;
        $arrCondition = array('cont_id' => $params['ContentID'], 'comm_status' => 1, 'comm_parent' => 0, 'comm_ip' => $ipaddress);
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intTotalComment = $serviceComment->getTotalCommentInContent($arrCondition);
        $arrParentCommentList = $serviceComment->getListLimitCommentInContent($arrCondition, $intPage, $intLimit, 'comm_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $pagingComment = $helper($paramsRouter['module'], $paramsRouter['__CONTROLLER__'], $paramsRouter['action'], $intTotalComment, $intPage, $intLimit, 'content', array('controller' => 'content', 'action' => 'getCommentContent', 'page' => $intPage));
        
        //get List Children Comment
        //get list userComment
        $totalComment = 0;
        $arrListCommentChildren = array();
        if (count($arrParentCommentList) > 0) {
            $totalComment = $totalComment + count($arrParentCommentList);
            foreach ($arrParentCommentList as $key => $value) {
                $listIdParent[] = $value['comm_id'];
                $listIdUser[] = $value['user_id'];
            }
            $arrListCommentChildren = array();
            if (count($listIdParent) > 0) {
                $strlistIdParent = implode(',', $listIdParent);
                $listCommentChildren = $serviceComment->getListChildren(array('listIdParen' => $strlistIdParent, 'comm_status' => 1, 'comm_ip' => $ipaddress));
                if (count($listCommentChildren) > 0) {
                    foreach ($listCommentChildren as $value) {
                        $totalComment = $totalComment + 1;
                        $arrListCommentChildren[$value['comm_parent']][] = $value;
                        $listIdUser[] = $value['user_id'];
                    }
                }
            }
            //get info user Comment
            $listIdUser = array_unique($listIdUser);
            $arrListUserComment = array();
            if (count($listIdUser) > 0) {
                $serviceUser = $this->serviceLocator->get('My\Models\User');
                $strListId = implode(',', $listIdUser);
                $listUserComment = $serviceUser->getList(array('listUserID' => $strListId));
                if (count($listUserComment) > 0) {
                    foreach ($listUserComment as $valueUser) {
                        $arrListUserComment[$valueUser['user_id']] = $valueUser;
                    }
                }
            }
        }
        $arrData = array(
            'pagingComment' => $pagingComment,
            'arrParentCommentList' => $arrParentCommentList,
            'arrListUserComment' => $arrListUserComment,
            'arrListCommentChildren' => $arrListCommentChildren,
            'totalComment' => $totalComment,
        );
        
        $view = new ViewModel($arrData);
        $view->setTemplate('frontend/layout/comment-content');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
        die;
    }

}
