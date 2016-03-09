<?php

namespace Frontend\Controller;

use My\Controller\MyController;

class SitemapController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceTagsContent \My\Models\TagsContent */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceKeyword \My\Models\Keyword */

    public function __construct() {
        
    }

    public function indexAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
    }

    public function categoryAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCondition = array(
            'not_cate_status' => -1,
            'cate_type' => 0
        );
        $arrCategoryList = $serviceCategory->getListLimit($arrCondition, $intPage, $intLimit, 'cate_sort ASC');
        $intTotal = $serviceCategory->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrCategoryList' => $arrCategoryList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Danh mục'
        );
    }

    public function tagsAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $arrCondition = array(
            'not_tags_status' => -1,
        );
        $arrTagsList = $serviceTags->getListLimit($arrCondition, $intPage, $intLimit, 'tags_sort ASC');
        $intTotal = $serviceTags->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrTagsList' => $arrTagsList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Tags sản phẩm'
        );
    }

    public function tagsContentAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
        $serviceTagsContent = $this->serviceLocator->get('My\Models\TagsContent');
        $arrCondition = array(
            'not_tags_cont_status' => -1,
        );
        $arrTagsContentList = $serviceTagsContent->getListLimit($arrCondition, $intPage, $intLimit, 'tags_cont_sort ASC');
        $intTotal = $serviceTagsContent->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrTagsContentList' => $arrTagsContentList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Tags bài viết'
        );
    }

    public function brandAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCondition = array(
            'not_cate_status' => -1,
            'cate_type' => 1
        );
        $arrCategoryList = $serviceCategory->getListLimit($arrCondition, $intPage, $intLimit, 'cate_sort ASC');
        $intTotal = $serviceCategory->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrCategoryList' => $arrCategoryList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Thương hiệu'
        );
    }

    public function productAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrCondition = array(
            'not_prod_status' => -1,
        );
        $arrProductList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id ASC');
        $intTotal = $serviceProduct->getTotal($arrCondition);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $intTotal = $instanceSearchProduct->getTotal();
//        
//        $instanceSearchProduct = new \My\Search\Products();
//        $arrCondition['page'] = $intPage;
//        $arrCondition['sort'] = array('prod_id' => 'asc');
//        $instanceSearchProduct->setParams($arrCondition)->setLimit($intLimit);
//        $arrProductList = $instanceSearchProduct->getListLimit();


        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrProductList' => $arrProductList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Sản phẩm'
        );
    }

    public function contentAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 200;
//        $serviceContent = $this->serviceLocator->get('My\Models\Content');
//        $arrContentList = $serviceContent->getListLimit($arrCondition, $intPage, $intLimit, 'cont_id ASC');
//        $intTotal = $serviceContent->getTotal($arrCondition);
        $arrCondition = array(
            'not_cont_status' => -1,
        );
        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchContent->setParams($arrCondition);
        $intTotal = $instanceSearchContent->getTotalData();

        $instanceSearchContent = new \My\Search\Content();

        $arrCondition = array(
            'not_cont_status' => -1,
            'page' => $intPage,
            'sort' => array('cont_id' => 'asc')
        );
        $instanceSearchContent->setParams($arrCondition)->setLimit($intLimit);
        $arrContentList = $instanceSearchContent->getListLimit();


        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrContentList' => $arrContentList,
            'paging' => $paging,
            'intPage' => $intPage,
            'title' => 'Bài viết'
        );
    }

    public function KeywordAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromRoute();
        $intPage = (!empty($this->params()->fromQuery('page')) && $this->params()->fromQuery('page') > 0) ? $this->params()->fromQuery('page') : 1;

        $intLimit = 200;
        $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
        $arrCondition = array(
            'word_id_less' => round((time() - 1437706100) / (60 * 60 / 100)),
        );
        $arrKeywordList = $serviceKeyword->getListLimit($arrCondition, $intPage, $intLimit, 'word_id DESC', $arrCol = array('word_slug', 'word_key', 'word_id'));
        $intTotal = $serviceKeyword->getTotal($arrCondition);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'sitemap', $params);
        return array(
            'params' => $params,
            'arrKeywordList' => $arrKeywordList,
            'paging' => $paging,
            'intPage' => $intPage,
            'intLimit' => $intLimit,
            'intTotal' => $intTotal,
            'title' => 'Keyword'
        );
    }

    public function otherAction() {
        $this->layout('layout/empty');
        $arrData = array(
            array('url' => 'http://megavita.vn/', 'name' => "Trang chủ")
        );
        return array(
            'arrData' => $arrData
        );
    }

}
