<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate,
    Zend\View\Model\ViewModel;

class ProductController extends MyController {
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceComment \My\Models\Comment */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProperties \My\Models\Properties */
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceContent  \My\Models\Content */
    /* @var $serviceOrder  \My\Models\Order */
    /* @var $serviceProductOrder  \My\Models\ProductOrder */
    /* @var $serviceStandProduct  \My\Models\StandProduct */
    /* @var $serviceStand  \My\Models\Stand */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:product:detail' => 'jquery.lazyload.js',
            ];
            $this->defaultCSS = [
                'frontend:product:detail' => 'jquery.range.css,checkbox.css,jquery.jqzoom.css,jRating.jquery.css',
            ];

            $this->externalJS = [
                'frontend:product:detail' => array(
                    STATIC_URL . '/f/v1/js/library/??jquery.range.js,jRating.jquery.min.js,jquery.jqzoom-core.js,insilde.js',
                    STATIC_URL . '/f/v1/js/my/??insilde.js,product.js',
                ),
            ];
        }
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $arrCondition = array('prod_actived' => 1);
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProdutList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id DESC');

//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_actived' => 1));
//        $intTotal = $instanceSearchProduct->getTotal();
//        
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_actived' => 1,'page'=>$intPage,'sort'=>array('prod_id' => 'desc')))->setLimit($intLimit);
//        $arrProdutList = $instanceSearchProduct->getListLimit();
//        
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProdutList' => $arrProdutList
        );
    }

    public function detailAction() {
        $params = $this->params()->fromRoute();
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (empty($params['productslug'])) {
            return $this->redirect()->toRoute('404', array());
        }

        if (empty($params['productId'])) {
            return $this->redirect()->toRoute('404', array());
        }

        $validator = new Validate();
        if (!$validator->Digits($params['productId'])) {
            return $this->redirect()->toRoute('404', array());
        }

        //get detailProduct
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetaiProduct = $serviceProduct->getDetail(array('prod_id' => $params['productId'], 'not_prod_actived' => -1));
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_id' => $params['productId'], 'not_prod_actived' => -1));
//        $arrDetaiProduct = $instanceSearchProduct->getDetail();

        if ($arrDetaiProduct["prod_actived"] == 0 && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "bot") === false) {
            // Kiểm tra robot
            return $this->redirect()->toRoute('404', array());
        }

        if (empty($arrDetaiProduct)) {
            return $this->redirect()->toRoute('404', array());
        }

        if ($arrDetaiProduct['prod_slug'] != $params['productslug']) {
            // Thêm header 301
            return $this->redirect()->toRoute('product', array('controller' => 'index', 'action' => 'index', 'productslug' => $arrDetaiProduct['prod_slug'], 'productId' => $arrDetaiProduct['prod_id']))->setStatusCode('301');
        }

        //get Meta for SEO
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headMeta()->appendName('robots', !empty($arrDetaiProduct['prod_meta_robot']) ? $arrDetaiProduct['prod_meta_robot'] : "index");
        $arrDetaiProduct['prod_meta_title'] ? $this->renderer->headTitle(html_entity_decode($arrDetaiProduct['prod_meta_title']) . General::TITLE_META) : $this->renderer->headTitle(html_entity_decode($arrDetaiProduct['prod_name']) . General::TITLE_META);
        $arrDetaiProduct['prod_meta_keyword'] ? $this->renderer->headMeta()->appendName('keywords', html_entity_decode($arrDetaiProduct['prod_meta_keyword'])) : NULL;
        $arrDetaiProduct['prod_meta_description'] ? $this->renderer->headMeta()->appendName('description', html_entity_decode($arrDetaiProduct['prod_meta_description'])) : NULL;
        $arrDetaiProduct['prod_social_meta'] ? $this->renderer->headMeta()->appendName('social', $arrDetaiProduct['prod_social_meta']) : NULL;
        $url = $this->serviceLocator->get('viewhelpermanager')->get('URL');
        $this->renderer->headLink(array('rel' => 'canonical', 'href' => BASE_URL . $url('product', array('controller' => 'product', 'action' => 'index', 'productslug' => $arrDetaiProduct["prod_slug"], 'productId' => $arrDetaiProduct["prod_id"]))));

        //get Brand of product
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrDetailBrand = $serviceCategory->getDetail(array('cate_id' => $arrDetaiProduct['bran_id'], 'cate_status' => 1));

        //getCategory 
        //LIST CATEGORY
        $arrCategoryList = array();
        $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        foreach ($ARR_CATEGORY_LIST as $key => $value) {
            $arrCategoryList[$value['cate_id']] = $value;
        }

        //get main Category $arrDetaiProduct['main_cate_id']
        $mainCategory = $arrCategoryList[$arrDetaiProduct['main_cate_id']];
        $arrGradeID = explode(':', $mainCategory['cate_grade']);
        foreach ($arrGradeID as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrGradeID[$offset]);
            }
        }
        $arrGradeList = array();
        foreach ($arrGradeID as $key => $value) {
            $arrGradeList[] = $arrCategoryList[$value];
        }
        //get Properties in product
        $arrPropertiesList = array();
        if ($arrDetaiProduct['prop_id'] != '') {
            $arrPropertiesID = explode(',', $arrDetaiProduct['prop_id']);
            $arrPropertiesID = array_unique($arrPropertiesID);
            foreach ($arrPropertiesID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrPropertiesID[$offset]);
                }
            }
            $arrDetaiProduct['prop_id'] = implode(',', $arrPropertiesID);
            if ($arrDetaiProduct['prop_id']) {
                $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
                $arrPropertiesList = $serviceProperties->getList(array('listPropertiesID' => $arrDetaiProduct['prop_id'], 'prop_status' => 1));

                if (count($arrPropertiesList) > 0) {
                    $arrPropertiesParentList = $serviceProperties->getList(array('prop_parent' => 0));
                }

                if (count($arrPropertiesList) == 0) {
                    $arrPropertiesParentList = array();
                }
            }
        }

        //GET KEYWORD
        // Lấy từ elasticsearch
        $instanceSearchKeyword = new \My\Search\Keywords();
        $instanceSearchKeyword->setParams(['page' => 1, 'word_key' => General::clean($arrDetaiProduct['prod_name'])])->setLimit(50);
        $listKeyword = $instanceSearchKeyword->getSearchData();
//        if (empty($listKeyword)) {
//            // Lấy từ db
//            $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
//            $listKeyword = $serviceKeyword->getLimit(array('search' => General::clean($arrDetaiProduct['prod_name'])), 1, 50, 'score DESC');
//        }
        //get Product bestSelling
        $arrProductBestSelling = $serviceProduct->getListLimit(array('prod_actived' => 1, 'main_cate_id' => $arrDetaiProduct['main_cate_id']), 1, 15, 'prod_viewer DESC');

//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_actived' => 1, 'main_cate_id' => $arrDetaiProduct['main_cate_id'],'page'=>1,'sort' => array('prod_viewer' => 'desc')))->setLimit(15);
//        $arrProductBestSelling = $instanceSearchProduct->getListLimit();
        //get list Parent Comment in Product
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ipaddress = $client;
        }

        if (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ipaddress = $forward;
        }

        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            $ipaddress = $remote;
        }

        $arrDataComment = array(
            'prod_id' => $arrDetaiProduct['prod_id'],
            'ipaddress' => $ipaddress,
        );

        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 10;
        $arrCondition = array('prod_id' => $arrDetaiProduct['prod_id'], 'comm_status' => 1, 'comm_parent' => 0, 'comm_ip' => $ipaddress);
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intTotalComment = $serviceComment->getTotalInProduct($arrCondition);
        $arrParentCommentList = $serviceComment->getListLimitInProduct($arrCondition, $intPage, $intLimit, 'comm_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $pagingComment = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotalComment, $intPage, $intLimit, 'product', array('controller' => 'product', 'action' => 'detail', 'page' => $intPage));

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
        //get tags
        $arrTags = array();
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');

        if (!empty($arrDetaiProduct['tags_id']))
            $arrTags = $serviceTags->getList(array('in_tags_id' => $arrDetaiProduct['tags_id']));


        //get product in tags

        if ($arrDetaiProduct['tags_id'] != '') {
            $arrProductTagsList = $serviceProduct->getList(array('listTagsID' => $arrDetaiProduct['tags_id'], 'prod_actived' => 1));
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('listTagsID' => explode(',',$arrDetaiProduct['tags_id']), 'prod_actived' => 1));
//            $arrProductTagsList = $instanceSearchProduct->getList();
        }
        if ($arrDetaiProduct['tags_id'] == '') {
            $arrProductTagsList = array();
        }

        //get product in mainCategory
        $arrProductTagsList = array();
        if ($arrDetaiProduct['main_cate_id'] != '') {
            $arrProductTagsList = $serviceProduct->getListLimit(array('main_cate_id' => $arrDetaiProduct['main_cate_id'], 'not_prod_id' => $arrDetaiProduct['prod_id'], 'prod_actived' => 1), $intPage, 30, 'prod_id DESC');
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('main_cate_id' => $arrDetaiProduct['main_cate_id'], 'not_prod_id' => $arrDetaiProduct['prod_id'], 'prod_actived' => 1,'page' => $intPage,'sort' => array('prod_id' =>'desc')))->setLimit(30);
//            $arrProductTagsList = $instanceSearchProduct->getListLimit();
        }
        $arrCookieProduct = unserialize($_COOKIE['cookieProduct']);
        if ($arrCookieProduct) {
            $listProduct = implode(',', $arrCookieProduct);

            $arrProductCookieList = array_reverse($serviceProduct->getList(array('listProductID' => $listProduct), 1, 20, 'prod_id ASC'));
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('listProductID' => $arrCookieProduct,'page'=>1,'sort' => array('prod_id' => 'asc')))->setLimit(20);
//            $arrProductCookieList = array_reverse($instanceSearchProduct->getListLimit());
        }

        if (!in_array($arrDetaiProduct['prod_id'], $arrCookieProduct)) {
            $arrCookieProduct[] = $arrDetaiProduct['prod_id'];
            setcookie('cookieProduct', serialize(array_reverse($arrCookieProduct)), time() + (604800 * 4), "/");
        }

        //get List contenet :)
        $listCategoryContent = $arrDetaiProduct['main_cate_id'];
        if (!empty($arrDetaiProduct['cate_id'])) {
            $listCategoryContent = $listCategoryContent . ',' . $arrDetaiProduct['cate_id'];
        }
        $arrlistCategoryContent = explode(',', $listCategoryContent);
        $arrlistCategoryContent = array_unique($arrlistCategoryContent);
        $listCategoryContent = implode(',', $arrlistCategoryContent);
        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        $arrContentList = $serviceContent->getListLimit(array('listCategoryID' => $listCategoryContent, 'cont_status' => 1), 1, 7, 'cont_id DESC');
//        p($arrDetaiProduct);die();
//        $instanceSearchContent = new \My\Search\Content();
//        $arrCondition = array(
//            'listCategoryID' => $arrlistCategoryContent,
//            'cont_status' => 1,
//            'page' => 1,
//            'sort' => array('cont_id' => 'desc')
//        );
//        $instanceSearchContent->setParams($arrCondition)->setLimit(7);
//        $arrContentList = $instanceSearchContent->getListLimit();

        $serviceProduct->editViewer((int) $arrDetaiProduct['prod_id']);

        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        // lấy chi tiết sản phẩm này trong gian hàng
        $arrStandProduct = $serviceStandProduct->getDetail(["prod_id" => $arrDetaiProduct['prod_id']]);

        $arrStand = [];
        if (!empty($arrStandProduct)) {
            // lấy thông tin gian hàng
            $arrStand = $serviceStand->getDetail(["stan_id" => $arrStandProduct["stan_id"]]);
        }

        return array(
            'params' => $params,
            'arrDetaiProduct' => $arrDetaiProduct,
            'arrProductTagsList' => $arrProductTagsList,
            'arrTags' => $arrTags,
            'pagingComment' => $pagingComment,
            'arrParentCommentList' => $arrParentCommentList,
            'arrDetailBrand' => $arrDetailBrand,
            'mainCategory' => $mainCategory,
            'arrPropertiesList' => $arrPropertiesList,
            'arrPropertiesParentList' => $arrPropertiesParentList,
            'arrProductBestSelling' => $arrProductBestSelling,
            'arrListUserComment' => $arrListUserComment,
            'arrProductCookieList' => $arrProductCookieList,
            'arrListCommentChildren' => $arrListCommentChildren,
            'totalComment' => $totalComment,
            'arrContentList' => $arrContentList,
            'arrGradeList' => $arrGradeList,
            'listKeyword' => $listKeyword,
            'arrStand' => $arrStand
        );
    }

    public function rateAction() {
        $params = $this->params()->fromPost();

        if (empty($params['idBox'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }

        if (empty($params['rate'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//        $detailProduct = $serviceProduct->getDetail(array("prod_id" => $params['idBox']));
        $instanceSearchProduct = new \My\Search\Products();
        $instanceSearchProduct->setParams(array("prod_id" => $params['idBox']));
        $detailProduct = $instanceSearchProduct->getDetail();

        echo $rate = $detailProduct['prod_rate'] + $params['rate'];
        $countRate = $detailProduct['prod_count_rate'];
        $intResult = $serviceProduct->edit(array('prod_rate' => $rate, 'prod_count_rate' => $countRate + 1), $detailProduct['prod_id']);

        $arrDataElastic = $detailProduct;
        $arrDataElastic['prod_rate'] = $rate;
        $arrDataElastic['prod_count_rate'] = $countRate + 1;
        $arrDocument[] = new \Elastica\Document($arrDataElastic['prod_id'], $arrDataElastic);
        $instanceSearchProduct->add($arrDocument);

        die();
        //   print_r($intResult);die();
//        if ($intResult) {
//            return true; // $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Cảm ơn bạn đã đánh giá sản phẩm của chúng tôi !')));
//        }
        // return false; // $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

    public function printProductAction() {
        $this->layout('layout/empty');
        if (IS_ACP != 1) {
            return $this->redirect()->toRoute('404', array());
        }
        $params = $this->params()->fromRoute();
        if (empty($params['orderID'])) {
            return $this->redirect()->toRoute('404', array());
        }
        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $arrOrder = $serviceOrder->getDetail(array('orde_id' => (int) $params['orderID'], 'not_orde_status' => -1));
        if (empty($arrOrder)) {
            return $this->redirect()->toRoute('404', array());
        }
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrOrderDetail = $serviceProductOrder->getList(array('orde_id' => $params['orderID']));
        foreach ($arrOrderDetail as $value) {
//            $arrListProducts[$value['prod_id']] = $serviceProduct->getDetail(array('prod_id'=>$value['prod_id']));
            $instanceSearchProduct = new \My\Search\Products();
            $instanceSearchProduct->setParams(array('prod_id' => $value['prod_id']));
            $arrListProducts[$value['prod_id']] = $instanceSearchProduct->getDetail();
        }
        //p($arrListProducts);die();
        if (empty($arrOrderDetail)) {
            return $this->redirect()->toRoute('404', array());
        }

        return array(
            'arrOrder' => $arrOrder,
            'arrOrderDetail' => $arrOrderDetail,
            'arrListProducts' => $arrListProducts,
        );
    }

    public function infoOrderAction() {
        if (IS_ACP != 1) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1)));
        }

        $view = new ViewModel();
        $view->setTemplate('info-order');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
        die();
    }

    public function insertOrderAction() {
        if (IS_ACP != 1) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1)));
        }
        $params = $this->params()->fromPost();
        if (empty($params['ProductID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại </center>')));
        }
        $strProductID = $params['ProductID'];

        if (empty($params['quantityPro'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Chưa nhập số lượng sản phẩm ! </center>')));
        }
        $strQuantity = $params['quantityPro'];
        $validator = new Validate();

        if (!$validator->Digits($strProductID)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại </center>')));
        }

        if (!$validator->Digits($strQuantity)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Số lượng sản phẩm phải là 1 số nguyên, từ 1 đến 1000 </center>')));
        }

        if ($strQuantity < 0 || $strQuantity > 1000) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Số lượng sản phẩm phải là 1 số nguyên, từ 1 đến 1000 </center>')));
        }

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//        $arrProductDetail = $serviceProduct->getDetail(array('prod_id' => $strProductID, 'not_prod_status' => -1));
        $instanceSearchProduct = new \My\Search\Products();
        $instanceSearchProduct->setParams(array('prod_id' => $strProductID, 'not_prod_status' => -1));
        $arrProductDetail = $instanceSearchProduct->getDetail();

        if (empty($arrProductDetail)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Sản phẩm không tồn tại trong hệ thống ! Vui lòng xem lại! </center>')));
        }

        if (empty($params['userName'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập họ và tên của khách hàng !')));
        }
        $strFullName = trim($params['userName']);
        if (empty($params['userPhone'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Chưa nhập số điện thoại của khách hàng! </center>')));
        }

        $intPhone = trim($params['userPhone']);
        if (!$validator->Digits($intPhone)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Số điện thoại phải là 1 số nguyên, từ 8 đến 11 số </center>')));
        }
        if (!$validator->Between(strlen($intPhone), 8, 11)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center> Số điện thoại phải là 1 số nguyên, từ 8 đến 11 số </center>')));
        }

        if (empty($params['userAddress'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Chưa nhập địa chỉ của khách hàng !')));
        }
        $strAddress = trim($params['userAddress']);

        $arrProductDetail['prod_is_promotion'] == 1 ? $totalPrice = $arrProductDetail['prod_promotion_price'] * $strQuantity : $totalPrice = $arrProductDetail['prod_price'] * $strQuantity;

        $serviceOrder = $this->serviceLocator->get('My\Models\Order');
        $serviceProductOrder = $this->serviceLocator->get('My\Models\ProductOrder');
//        p($params);die;
        if (empty($params['userEmail'])) {
            $orderDetail = array(
                'fullname' => $strFullName,
                'address' => $strAddress,
                'phone' => $intPhone,
                    //            'email' => $arrUser['user_email']
            );
//            p
            $arrOrder = array(
                'orde_detail' => json_encode($orderDetail),
                'orde_phone' => $intPhone,
                'orde_created' => time(),
                'user_id' => UID,
                'orde_total_price' => $totalPrice,
                //                    'orde_payment' => $_SESSION['payment'],
                'user_fullname' => $strFullName,
                'user_phone' => $intPhone,
                //            'user_email' => $arrUser['user_email'],
                'user_updated' => UID,
                'user_change_status' => FULLNAME
            );
//            p($orderDetail);die;
            $inResult = $serviceOrder->add($arrOrder);
            if ($inResult) {
                $arrProductOrder = array(
                    'orde_id' => $inResult,
                    'prod_id' => $arrProductDetail['prod_id'],
                    'user_id' => UID ? (int) UID : NULL,
                    'prod_name' => $arrProductDetail['prod_name'],
                    'prod_quantity' => $strQuantity,
                    'prod_price' => ($arrProductDetail['prod_is_promotion'] == 1) ? $arrProductDetail['prod_promotion_price'] : $arrProductDetail['prod_price'],
                    'prod_call_price' => $arrProductDetail['prod_call_price'],
                    'total_price' => $totalPrice,
                    'prod_slug' => $arrProductDetail['prod_slug'],
                    'prod_image' => $arrProductDetail['prod_image'],
                );
                $serviceProductOrder->add($arrProductOrder);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'url' => $this->url()->fromRoute('product_print', array('orderID' => $inResult)))));
//                    return $this->redirect()->toRoute('product_print', array('orderID'=>$inResult));
            } else {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!</center>')));
            }
        }

        $serviceUser = $this->serviceLocator->get('My\Models\User');
        if ($params['userEmail']) {
            $strEmail = trim($params['userEmail']);
            if (!$validator->emailAddress($strEmail)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Địa chỉ email không không đúng định dạng!</center>')));
            }
            $arrUser = $serviceUser->getDetail(array('user_email' => $strEmail, 'user_status' => 1));
//            p($arrUser);die;
            if ($arrUser) {
                $orderDetail = array(
                    'fullname' => $arrUser['user_fullname'],
                    'address' => $arrUser['user_address'],
                    'phone' => $arrUser['user_phone'],
                    'email' => $arrUser['user_email']
                );
                $arrOrder = array(
                    'orde_detail' => json_encode($orderDetail),
                    'orde_phone' => $arrUser['user_phone'],
                    'orde_created' => time(),
                    'user_id' => $arrUser['user_id'],
                    'orde_total_price' => $totalPrice,
//                    'orde_payment' => $_SESSION['payment'],
                    'user_fullname' => $arrUser['user_fullname'],
                    'user_phone' => $arrUser['user_phone'],
                    'user_email' => $arrUser['user_email'],
                    'user_updated' => UID,
                    'user_change_status' => FULLNAME
                );
//                p($arrOrder);die;
                $inResult = $serviceOrder->add($arrOrder);
//                echo $this->url('route-name', $urlParams, $urlOptions);
//                die;

                if ($inResult) {
                    $arrProductOrder = array(
                        'orde_id' => $inResult,
                        'prod_id' => $arrProductDetail['prod_id'],
                        'user_id' => UID ? (int) UID : NULL,
                        'prod_name' => $arrProductDetail['prod_name'],
                        'prod_quantity' => $strQuantity,
                        'prod_price' => ($arrProductDetail['prod_is_promotion'] == 1) ? $arrProductDetail['prod_promotion_price'] : $arrProductDetail['prod_price'],
                        'prod_call_price' => $arrProductDetail['prod_call_price'],
                        'total_price' => $totalPrice,
                        'prod_slug' => $arrProductDetail['prod_slug'],
                        'prod_image' => $arrProductDetail['prod_image'],
                    );
                    $serviceProductOrder->add($arrProductOrder);
                    return $this->getResponse()->setContent(json_encode(array('st' => 1, 'url' => $this->url()->fromRoute('product_print', array('orderID' => $inResult)))));
                } else {
                    return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!</center>')));
                }
            }

            $orderDetail = array(
                'fullname' => $strFullName,
                'address' => $strAddress,
                'phone' => $intPhone,
                'email' => $strEmail
            );
            $arrOrder = array(
                'orde_detail' => json_encode($orderDetail),
                'orde_phone' => $intPhone,
                'orde_created' => time(),
                'user_id' => UID,
                'orde_total_price' => $totalPrice,
                //                    'orde_payment' => $_SESSION['payment'],
                'user_fullname' => $strFullName,
                'user_phone' => $intPhone,
                'user_email' => $strEmail,
                'user_updated' => UID,
                'user_change_status' => FULLNAME
            );
            $inResult = $serviceOrder->add($arrOrder);
            if ($inResult) {
                $arrProductOrder = array(
                    'orde_id' => $inResult,
                    'prod_id' => $arrProductDetail['prod_id'],
                    'user_id' => UID ? (int) UID : NULL,
                    'prod_name' => $arrProductDetail['prod_name'],
                    'prod_quantity' => $strQuantity,
                    'prod_price' => ($arrProductDetail['prod_is_promotion'] == 1) ? $arrProductDetail['prod_promotion_price'] : $arrProductDetail['prod_price'],
                    'prod_call_price' => $arrProductDetail['prod_call_price'],
                    'total_price' => $totalPrice,
                    'prod_slug' => $arrProductDetail['prod_slug'],
                    'prod_image' => $arrProductDetail['prod_image'],
                );
                $serviceProductOrder->add($arrProductOrder);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'url' => $this->url()->fromRoute('product_print', array('orderID' => $inResult)))));
            } else {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => '<center>Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!</center>')));
            }
        }
        die();
    }

}
