<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class ContentController extends MyController {
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceComment \My\Models\Comment */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceTagsContent \My\Models\TagsContent */
    /* @var $serviceKeyword \My\Models\Keyword */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:content:view' => 'jquery.lazyload.js,jquery.range.js,insilde.js',
                'frontend:content:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js',
            ];
            $this->defaultCSS = [
                'frontend:content:index' => 'jquery.range.css,checkbox.css',
                'frontend:content:view' => 'jquery.range.css,checkbox.css,jquery.jqzoom.css,jRating.jquery.css',
            ];
            $this->externalJS = [
                'frontend:content:view' => array(
                    STATIC_URL . '/f/v1/js/library/??jquery.range.js,jRating.jquery.min.js,jquery.jqzoom-core.js,insilde.js',
                    STATIC_URL . '/f/v1/js/my/??insilde.js,content.js',
                ),
                'frontend:content:index' => array(
                    STATIC_URL . '/f/v1/js/library/??jquery.range.js,jRating.jquery.min.js,jquery.jqzoom-core.js,insilde.js',
                    STATIC_URL . '/f/v1/js/my/??insilde.js,content.js',
                ),
            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intCategoryID = (int) $params['categoryID'];
        if (empty($intCategoryID)) {
            return $this->redirect()->toRoute('404', array());
        }

        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 16;

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        // Lấy chi tiết category để tiến hành kiểm tra slug có đúng với slug hiện hành hay ko 
        $getDetailCategory = $serviceCategory->getDetail(array('cate_id' => $intCategoryID, 'cate_type' => 0, 'not_cate_status' => -1));

        // Trả về lỗi 404 khi category không tồn tại
        if (!$getDetailCategory) {
            return $this->redirect()->toRoute('404', array());
        }

        // Kiểm tra slug category nếu sai redirect về category đúng
        if ($getDetailCategory['cate_slug'] != $params['categorySlug']) {
            // Thêm header 301
            return $this->redirect()->toRoute('content', array('controller' => 'index', 'action' => 'index', 'categorySlug' => $getDetailCategory['cate_slug'], 'categoryID' => $getDetailCategory['cate_id']))->setStatusCode('301');
        }

        $cate_grade = explode(':', rtrim($getDetailCategory['cate_grade'], ':'));
        $listCateGrade = empty($cate_grade) ? 0 : implode(",", $cate_grade);
        $arrCateGradeList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1, 'listCategoryID' => $listCateGrade));
        $arrCategoryList = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0, 'categrade' => $cate_grade[0]));
        $params['cate_name'] = $getDetailCategory['cate_name'];

        if ($params['s']) {
            $s = trim(General::clean($params['s']));
            $arrCondition['search'] = $s;
        }

        //$cateId = $params['categoryID'];
        $arrCategoryListChild = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0, 'categrade' => $getDetailCategory['cate_grade']));
        $listCateChild = '';
        foreach ($arrCategoryListChild as $val) {
            $listCateChild .= (int) $val['cate_id'] . ",";
        }
        $listCateChild = rtrim($listCateChild, ',');
        $arrConditionContent = array(
            'cate_id_or_main_cate_id' => $listCateChild,
            'not_cont_status' => -1,
        );

        // $serviceContent = $this->serviceLocator->get('My\Models\Content');

        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchContent->setParams($arrConditionContent);
        $intTotal = $instanceSearchContent->getTotalData();

        // $instanceSearchContent = new \My\Search\Content();
        $arrConditionContent = array(
            'cate_id_or_main_cate_id' => $listCateChild,
            'not_cont_status' => -1,
            'page' => $intPage
        );
        $instanceSearchContent->setParams($arrConditionContent)->setLimit($intLimit);
        $arrContentList = $instanceSearchContent->getListLimit();
        //get tags

        $arrTagsContent = array();
        if (!empty($arrContentList)) {
            $strTagsContentListID = '';
            foreach ($arrContentList as $value) {
                $strTagsContentListID = $strTagsContentListID . ',' . $value['tags_cont_id'];
            }
            $arrTagsContentListID = explode(',', $strTagsContentListID);
            $arrTagsContentListID = array_unique($arrTagsContentListID);

            foreach ($arrTagsContentListID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrTagsContentListID[$offset]);
                }
            }
            $strTagsContentListID = implode(',', $arrTagsContentListID);
            if (!empty($strTagsContentListID)) {
                $serviceTagsContent = $this->serviceLocator->get('My\Models\TagsContent');
                $arrTagsContent = $serviceTagsContent->getList(array('in_tags_cont_id' => $strTagsContentListID, 'not_tags_cont_status'));
            }
        }


        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $countPage = ceil($intTotal / $intLimit);
        $arrParams = array('controller' => 'content', 'action' => 'index', 'categoryID' => $intCategoryID, 'categorySlug' => $params['categorySlug']);
        if (isset($params['s'])) {
            $arrParams['s'] = str_replace(' ', '+', $s);
        }
        if (isset($params['categorySlug']))
            $arrParamsRoute['categorySlug'] = $params['categorySlug'];
        if (isset($intCategoryID))
            $arrParamsRoute['categoryID'] = $intCategoryID;
        if (isset($params['page']))
            $arrParams['page'] = $intPage;

        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'content', $arrParams);
        $serviceContentCategory = $this->serviceLocator->get('My\Models\ContentCategory');

        $getDetailContentCategory = $serviceContentCategory->getDetail(array('cate_id' => $intCategoryID, 'cate_status' => 1));
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');

        $strMetaTitle = !empty($getDetailCategory['cate_meta_title']) ? $getDetailCategory['cate_meta_title'] : $getDetailCategory['cate_name'];
        $strMetaKeyword = !empty($getDetailCategory['cate_meta_keyword']) ? $getDetailCategory['cate_meta_keyword'] : "";
        $strMetaDescription = !empty($getDetailCategory['cate_meta_description']) ? $getDetailCategory['cate_meta_description'] : "";
        $strMetaSocial = !empty($getDetailCategory['cate_meta_social']) ? $getDetailCategory['cate_meta_social'] : "";


        if (!empty($getDetailContentCategory)) {
            !empty($getDetailContentCategory['cate_meta_title']) ? $strMetaTitle = $getDetailContentCategory['cate_meta_title'] : '';
            !empty($getDetailContentCategory['cate_meta_keyword']) ? $strMetaKeyword = $getDetailContentCategory['cate_meta_keyword'] : '';
            !empty($getDetailContentCategory['cate_meta_description']) ? $strMetaDescription = $getDetailContentCategory['cate_meta_description'] : '';
            !empty($getDetailContentCategory['cate_meta_social']) ? $strMetaSocial = $getDetailContentCategory['cate_meta_social'] : '';
        }

        $getDetailCategory["cate_name"] = "Tin tức về " . $getDetailCategory["cate_name"];
        !empty($strMetaTitle) ? $this->renderer->headTitle(html_entity_decode("Tin tức về " . $strMetaTitle) . General::TITLE_META) : null;
        !empty($strMetaKeyword) ? $this->renderer->headMeta()->appendName('keywords', html_entity_decode($strMetaKeyword)) : null;
        !empty($strMetaDescription) ? $this->renderer->headMeta()->appendName('description', html_entity_decode($strMetaDescription)) : null;
        !empty($strMetaSocial) ? $this->renderer->headMeta()->appendName('social', $strMetaSocial) : null;

        foreach ($getDetailContentCategory as $key => $value) {
            if (empty($value) && $key != 'cate_description') {
                unset($getDetailContentCategory[$key]);
            }
        }
        $getDetailCategory = array_merge($getDetailCategory, $getDetailContentCategory);
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');

        $arrProductList = $serviceProduct->getListLimit(['cate_id_or_main_cate_id' => $intCategoryID], 1, 10, "prod_id DESC");
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('cate_id_or_main_cate_id' =>  $intCategoryID, 'page' => 1))->setLimit(10);
//        $arrProductList = $instanceSearchProduct->getListLimit();

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrDetailCategory' => $getDetailCategory,
            'arrCateGradeList' => $arrCateGradeList,
            'arrCategoryList' => $arrCategoryList,
            'arrContentList' => $arrContentList,
            'arrCategoryListChild' => $arrCategoryListChild,
            'countPage' => $countPage,
            'intPage' => $intPage,
            'arrProductList' => $arrProductList,
            'arrTagsContent' => $arrTagsContent
        );
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['contId'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCondition = array(
            'cont_id' => $params['contId'],
            'not_cont_status' => -1
        );

        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        $instanceSearchKeyword = new \My\Search\Content();
        $instanceSearchKeyword->setParams($arrCondition);
        $DetaiContent = $instanceSearchKeyword->getDetail();
		
		if ($DetaiContent["cont_status"] == 0 && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "bot") === false) {
            // Kiểm tra robot
            return $this->redirect()->toRoute('404', array());
        }

        if (empty($DetaiContent)) {
            return $this->redirect()->toRoute('404', array());
        }
        if ($DetaiContent['cont_slug'] != $params['contslug']) {
            // Thêm header 301
            return $this->redirect()->toRoute('content_detail', array('controller' => 'index', 'action' => 'index', 'contslug' => $DetaiContent['cont_slug'], 'contId' => $DetaiContent['cont_id']))->setStatusCode('301');
        }

        //get detail user created content 
        $serviceUser = $this->serviceLocator->get('My\Models\User');
        $arrDetailUser = $serviceUser->getDetail(array('user_id' => $DetaiContent['user_id']));
        $params['categoryID'] = $DetaiContent['main_cate_id'];
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCateList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        $getDetailCategory = $serviceCategory->getDetail(array('cate_id' => $params['categoryID'], 'cate_type' => 0, 'cate_status' => 1));
        if (!$getDetailCategory) {
            return $this->redirect()->toRoute('404', array());
        }

        $cate_grade = explode(':', rtrim($getDetailCategory['cate_grade'], ':'));
        $listCateGrade = '';
        foreach ($cate_grade as $val) {
            $listCateGrade .= (int) $val . ',';
        }
        $listCateGrade = rtrim($listCateGrade, ',');
        $CateDetailList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1, 'listCategoryID' => $listCateGrade));
        $arrCategoryList = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0, 'categrade' => $cate_grade[0] . ':'));
        $arrCateId = array();
        foreach ($arrCategoryList as $category) {
            $arrGrade = explode(':', $category['cate_grade']);
            foreach ($arrGrade as $grade) {
                if ($grade)
                    $arrCateId[] = array_push($arrCateId, $grade);
            }
        }
        $arrCateId = array_unique($arrCateId);
        $strCateId = implode(',', $arrCateId);

        $params['cate_name'] = $getDetailCategory['cate_name'];

        //get tags
//       $arrContentSameMainCate = $serviceContent->getList(array('main_cate_id' => $DetaiContent['main_cate_id'], 'cont_status' => 1,));

        $instanceSearchKeyword = new \My\Search\Content();
        $instanceSearchKeyword->setParams(array('main_cate_id' => $DetaiContent['main_cate_id'], 'cont_status' => 1,));
        $arrContentSameMainCate = $instanceSearchKeyword->getList();

        $listTags = '';

        foreach ($arrContentSameMainCate as $value) {
            $listTags .= $value['tags_cont_id'] . ',';
        }

        $arrListTags = explode(',', $listTags);
        $arrListTags = array_unique($arrListTags);

        foreach ($arrListTags as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrListTags[$offset]);
            }
        }
        $listTags = implode(',', $arrListTags);
//        p($listTags);die;
        $serviceTags = $this->serviceLocator->get('My\Models\TagsContent');
        if ($listTags != '')
            $arrTags = $serviceTags->getListLimit(array('in_tags_cont_id' => $listTags), 1, 50, 'tags_cont_id DESC');

        //bai viet lien quan có ID nhỏ hơn bài viết hiện tại
        $arrCondition = array(
            'main_cate_id' => $DetaiContent['main_cate_id'],
            'cont_status' => 1,
            'cont_id_smaller' => $DetaiContent['cont_id'],
            'page' => 1,
            'sort' => array('cont_id' => 'desc')
        );
//        $arrListContent = $serviceContent->getLimit($arrCondition, 1, 10, "cont_id DESC");
        $instanceSearchKeyword = new \My\Search\Content();
        $instanceSearchKeyword->setParams($arrCondition)->setLimit(10);
        $arrListContent = $instanceSearchKeyword->getListLimit();
        
        // Không tồn tại bài viết nhỏ hơn bài viết hiện tại sẽ tiến hành lấy bài viết lớn hơn
        if (empty($arrListContent)) {
            $arrCondition = array(
                'main_cate_id' => $DetaiContent['main_cate_id'],
                'cont_status' => 1,
                'sort' => array('cont_id' => 'desc')
            );
//           $arrListContent = $serviceContent->getLimit($arrCondition, 1, 10, "cont_id DESC");
            $instanceSearchKeyword = new \My\Search\Content();
            $instanceSearchKeyword->setParams($arrCondition)->setLimit(10);
            $arrListContent = $instanceSearchKeyword->getListLimit();
        }

        $instanceSearchKeyword = new \My\Search\Keywords();
        $instanceSearchKeyword->setParams(['page' => 1, 'word_key' => General::clean($DetaiContent['cont_name'])])->setLimit(50);
        $listKeyword = $instanceSearchKeyword->getSearchData();
//        if (empty($listKeyword)) {
//            // Lấy từ db
//            // $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
//            // $listKeyword = $serviceKeyword->getLimit(array('search' => General::clean($arrDetaiProduct['prod_name'])), 1, 50, 'score DESC');
//        }
        //Lấy danh sách sản phẩm gợi ý
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrProduct = $serviceProduct->getListLimit(array('main_cate_id' => $DetaiContent['main_cate_id'], 'prod_status' => 1), 1, 5, "prod_id DESC");
        //    $arrListNewsContent = $serviceContent->getListLimit(array('cont_status' => 1, 'listCategoryID' => $strCateId), 1, 13, "cont_id DESC");
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('main_cate_id' => $DetaiContent['main_cate_id'], 'prod_status' => 1,'prod_actived' =>1));
//        $arrProduct = $instanceSearchProduct->getList();
        // Lấy sanh sách tin tức mới từ elasticsearch
        $arrCateID = explode(',', $strCateId);
        $arrCondition = array(
            'cont_status' => 1,
            'listCategoryID' => $arrCateID,
            'page' => 1,
            'sort' => array('cont_id' => 'desc')
        );
        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchContent->setParams($arrCondition)->setLimit(13);
        $arrListNewsContent = $instanceSearchContent->getListLimit();

        // Cập nhật lượt xem cho bài viết vào DB
        $viewer = $DetaiContent['cont_viewer'] + 1;
        $serviceContent->edit(array('cont_viewer' => $viewer), $DetaiContent['cont_id']);
        // Cập nhật lượt xem vào elastic
        $arrDataElastic = $DetaiContent;
        $arrDataElastic['cont_viewer'] = $DetaiContent['cont_viewer'] + 1;
        $arrDocument[] = new \Elastica\Document($arrDataElastic['cont_id'], $arrDataElastic);
        $instanceSearchContent->add($arrDocument);

        /*  INSERT HEADER TITLE TẠI ĐÂY */
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headMeta()->appendName('robots', !empty($DetaiContent['cont_meta_robot']) ? $DetaiContent['cont_meta_robot'] : "index");
        $this->renderer->headTitle(html_entity_decode($DetaiContent['cont_title']));
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode($DetaiContent['cont_meta_keyword']));
        $this->renderer->headMeta()->appendName('description', html_entity_decode($DetaiContent['cont_meta_description']));
        /* ------------------------------------ */



        $arrDataComment = array(
            'prod_id' => $arrDetaiProduct['prod_id'],
            'ipaddress' => General::getRealIpAddr(),
        );
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 10;
        $arrConditionComment = array('cont_id' => $DetaiContent['cont_id'], 'comm_status' => 1, 'comm_parent' => 0, 'comm_ip' => General::getRealIpAddr());
        $serviceComment = $this->serviceLocator->get('My\Models\Comment');
        $intTotalComment = $serviceComment->getTotalCommentInContent($arrConditionComment);
//        p($arrConditionComment);die;
        $arrParentCommentList = $serviceComment->getListLimitCommentInContent($arrConditionComment, $intPage, $intLimit, 'comm_id DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $pagingComment = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotalComment, $intPage, $intLimit, 'content', array('controller' => 'content', 'action' => 'view', 'page' => $intPage));

        //get list userComment
        $totalComment = 0;
        $arrListCommentChildren = array();
        if (count($arrParentCommentList) > 0) {
            $totalComment = $totalComment + count($arrParentCommentList);
            foreach ($arrParentCommentList as $key => $value) {
                $listIdParent[] = $value['comm_id'];
                $listIdUser[] = $value['user_id'];
            }
            if (count($listIdParent) > 0) {
                $strlistIdParent = implode(',', $listIdParent);
                $listCommentChildren = $serviceComment->getListChildren(array('listIdParen' => $strlistIdParent, 'comm_status' => 1, 'comm_ip' => General::getRealIpAddr()));
                if (count($listCommentChildren) > 0) {
                    foreach ($listCommentChildren as $value) {
                        $totalComment = $totalComment + 1;
                        $arrListCommentChildren[$value['comm_parent']][] = $value;
                        $listIdUser[] = $value['user_id'];
                    }
                }
            }

            //Lấy ID user trong comment
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
//        p($arrListContent);die;

        return array(
            'params' => $params,
            'DetaiContent' => $DetaiContent,
            'arrListContent' => $arrListContent,
            'arrListNewsContent' => $arrListNewsContent,
            'arrDetailCategory' => $getDetailCategory,
            'arrCateGradeList' => $CateDetailList,
            'arrCategoryList' => $arrCategoryList,
            'arrTags' => $arrTags,
            'arrProduct' => $arrProduct,
            'pagingComment' => $pagingComment,
            'arrParentCommentList' => $arrParentCommentList,
            'arrListUserComment' => $arrListUserComment,
            'arrListCommentChildren' => $arrListCommentChildren,
            'totalComment' => $totalComment,
            'arrDetailUser' => $arrDetailUser,
            'listKeyword' => $listKeyword,
        );
    }

    public function rateAction() {
        $params = $this->params()->fromPost();
        if (empty($params['ContentID'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        if (empty($params['Rate'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        $detailContent = $serviceContent->getDetail($params['ContentID']);
        $rate = $detailContent['cont_rate'] + ($params['Rate']);
        $intResult = $serviceContent->edit(array('cont_rate' => $rate), $params['categoryID']);
        if ($intResult) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Cảm ơn bạn đã đánh giá bài viết !')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

}
