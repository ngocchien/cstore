<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class TagsContentController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceTagsContent \My\Models\TagsContent */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:tagscontent:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js',
            ];
            $this->defaultCSS = [
                'frontend:tagscontent:index' => 'jquery.range.css,checkbox.css',
            ];
//            $this->externalJS = [
//                    'frontend:TagsContent:index' => STATIC_URL . '/f/v1/js/my/??tags.js',
//            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        if (empty($params['tagsSlug'])) {
            return $this->redirect()->toRoute('404', array());
        }

        if (empty($params['tagsID'])) {
            return $this->redirect()->toRoute('404', array());
        }

        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;

        $intLimit = 16;
//        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $serviceTagsContent = $this->serviceLocator->get('My\Models\TagsContent');
        $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        foreach ($ARR_CATEGORY_LIST as $value) {
            if ($value['cate_parent'] == 0) {
                $arrCategorList[$value['cate_id']] = $value;
            }
        }

        $tagsID = $params['tagsID'];
        $tagDetail = $serviceTagsContent->getDetail(array('tags_cont_id' => $tagsID, 'tags_cont_status' => 1));
        if ($tagDetail['tags_cont_slug'] != $params['tagsSlug']) {
            // Thêm header 301
            return $this->redirect()->toRoute('tags-content', array('controller' => 'index', 'action' => 'index', 'tagsSlug' => $tagDetail['tags_cont_slug'], 'tagsID' => $tagDetail['tags_cont_id']))->setStatusCode('301');
        }

        $arrCondition = array(
            'tags_cont_id' => $tagsID,
            'cont_status' => 1,
            'page' => $intPage,
            'sort' => array('cont_id' => 'desc')
        );
//        $arrContentList = $serviceContent->getLimit($arrCondition, $intPage, $intLimit, 'cont_id DESC');
        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchContent->setParams($arrCondition)->setLimit($intLimit);
        $arrContentList = $instanceSearchContent->getListLimit();

        if (!empty($arrContentList)) {
            $listTags = '';
            foreach ($arrContentList as $value) {
//                $arrListTagsID[] = $value['tags_cont_id'];
                $listTags .= $value['tags_cont_id'] . ',';
            }

            $arrListTagsID = explode(',', $listTags);
            $arrListTagsID = array_unique($arrListTagsID);
            foreach ($arrListTagsID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrListTagsID[$offset]);
                }
            }
            $listTags = implode(',', $arrListTagsID);

            if ($listTags != '') {
                $arrTags = $serviceTagsContent->getList(array('in_tags_cont_id' => $listTags));
            }
        }
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');

        $arrParams = array('controller' => 'TagsContent', 'action' => 'index', 'tagsID' => $params['tagsID'], 'tagsSlug' => $params['tagsSlug']);

        if (isset($params['page']))
            $arrParams['page'] = $intPage;

        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'tags_content', $arrParams);
        $countPage = ceil($intTotal / $intLimit);
        $tagsMetaTitle = $tagDetail['tags_cont_name'];
        if (!empty($tagDetail['tags_cont_meta_title']))
            $tagsMetaTitle = $tagDetail['tags_cont_meta_title'];
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle($tagsMetaTitle . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', $tagDetail['tags_cont_meta_keywords']);
        $this->renderer->headMeta()->appendName('description', $tagDetail['tags_cont_meta_description']);
        $this->renderer->headMeta()->appendName('social', $tagDetail['tags_cont_meta_social']);

        //get product topview 
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrProductList = $serviceProduct->getListLimit(array('prod_actived' => 1), 1, 15, 'prod_viewer DESC');
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams(array('prod_actived'=>1,'page'=>1,'sort' => array('prod_viewer'=> 'desc')))->setLimit(15);
//        $arrProductList = $instanceSearchProduct->getListLimit();
//        p(count($arrProductList));die;
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrContentList' => $arrContentList,
            'tagsDetail' => $tagDetail,
            'arrCategorList' => $arrCategorList,
            'countPage' => $countPage,
            'intPage' => $intPage,
            'arrTags' => $arrTags,
            'arrProductList' => $arrProductList
        );
    }

}
