<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;

class TagsController extends MyController {

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:tags:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js,category.js',
            ];
            $this->defaultCSS = [
                'frontend:tags:index' => 'jquery.range.css,checkbox.css',
            ];
            $this->externalJS = [
                'frontend:tags:index' => STATIC_URL . '/f/v1/js/my/??tags.js',
            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        //p($params);die;
        if (empty($params['tagsSlug'])) {
            return $this->redirect()->toRoute('404', array());
        }

        if (empty($params['tagsID'])) {
            return $this->redirect()->toRoute('404', array());
        }

        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
//        p($intPage);die;
        $intLimit = 16;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');

        $tagsID = $params['tagsID'];
        $arrBrandList = $serviceCategory->getListSort(array('cate_type' => 1, 'cate_status' => 1));
        $arrCategoryList = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0));
        $tagDetail = $serviceTags->getDetail(array('tags_id' => $tagsID));
        if ($tagDetail['tags_slug'] != $params['tagsSlug']) {
            // Thêm header 301
            return $this->redirect()->toRoute('tags', array('controller' => 'index', 'action' => 'index', 'tagsSlug' => $tagDetail['tags_slug'], 'tagsID' => $tagDetail['tags_id']))->setStatusCode('301');
        }

        if (isset($params['sort']) && $params['sort'] && ($params['sort'] == 'id_desc' || $params['sort'] == 'id_asc' || $params['sort'] == 'price_desc' || $params['sort'] == 'price_asc')) {
            $sort = trim($params['sort']);
            $sort = 'prod_' . str_replace('_', ' ', $sort);
        } else if (isset($params['sort'])) {
            $sort = 'prod_id desc';
        } else {
            $sort = 'prod_id desc';
        }

        $arrCondition = array(
            'tags_id' => $tagsID,
            'prod_actived' => 1,
        );
        //search
        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
        }
        // brand
        if ($params['brand']) {
            $params['brand'] = explode('--', $params['brand']);
            $listCategorySlug = '';
            foreach ($params['brand'] as $val) {
                $listCategorySlug .= "'" . $val . "',";   // => ex : 'a','b',
            }
            $listCategorySlug = rtrim($listCategorySlug, ',');
            $BrandDetailList = $serviceCategory->getList(array('cate_type' => 1, 'cate_status' => 1, 'listCategorySlug' => $listCategorySlug));
            foreach ($BrandDetailList as $val) {
                $brand_id[] = $val['cate_id'] ? $val['cate_id'] : NULL;
                $params['brand_name'][] = $val['cate_name'];
            }
            $arrCondition['or_brand_id'] = implode(',', $brand_id);
        }
        //price
        if ($params['price']) {
            $arrPrice = explode('--', $params['price']);
            $price_start = abs((int) $arrPrice[0]);
            $price_end = abs((int) $arrPrice[1]);

            if ($price_end < $price_start && $price_end > 0)
                return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));

            if ($price_start)
                $arrCondition['price_start'] = $price_start;
            if ($price_end)
                $arrCondition['price_end'] = $price_end;
        }
        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProductList = $serviceProduct->getListLimitSortingPriceJoinSortTags($arrCondition,array('sort_tag'=>$params['tagsID']) ,$intPage, $intLimit,'sort.sort_ordering DESC');
        $arrTags = array();
        $arrContentList =array();
        if(!empty($arrProductList)){
            foreach ($arrProductList as $value) {
                $arrListTags[] = $value['tags_id'];
                $arrMainCategoryList[] = $value['main_cate_id'];
            }
            
            if(!empty($arrMainCategoryList)){
                $arrMainCategoryList = array_unique( $arrMainCategoryList );
                foreach ( $arrMainCategoryList as $offset => $row ) {
                        if ('' == trim($row)) {
                                unset($arrMainCategoryList[$offset]);
                        }
                }
              //  p($arrMainCategoryList);die();
                $strMainCategoryList = implode(',', $arrMainCategoryList);
//                $serviceContent = $this->serviceLocator->get('My\Models\Content');
//                $arrContentList = $serviceContent->getLimit(array('listCategoryID'=>$strMainCategoryList,'cate_id_or_main_cate_id'=>$strMainCategoryList),1,10,'cont_id DESC');
                $instanceSearchContent = new \My\Search\Content();
                $arrCondition = array(
                    'listCategoryID' => $arrMainCategoryList,
                    'cate_id_or_main_cate_id' => $strMainCategoryList,
                    'sort' => array('cont_id' => 'desc'),
                    'page' => 1
                );
                $instanceSearchContent->setParams($arrCondition)->setLimit(10);
                $arrContentList = $instanceSearchContent->getListLimit();
            }
            
            if(!empty($arrListTags)){
                $arrListTags = array_unique( $arrListTags );
                foreach ( $arrListTags as $offset => $row ) {
                        if ('' == trim($row)) {
                                unset($arrListTags[$offset]);
                        }
                }
                $strlistTags = implode(',', $arrListTags);
                $arrTags = $serviceTags->getList(array('in_tags_id' => $strlistTags));
            }
            
            
            
            
        }
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $arrParams = array('controller' => 'tags', 'action' => 'index', 'tagsID' => $params['tagsID'], 'tagsSlug' => $params['tagsSlug']);

        if (isset($params['s'])) {
            $arrParams['s'] = str_replace(' ', '+', $s);
        }if (isset($price_start) && isset($price_end))
            $arrParams['price'] = $params['price'];
        if (isset($params['tagsSlug']))
            $arrParamsRoute['tagsSlug'] = $params['tagsSlug'];
        if (isset($params['tagsID']))
            $arrParamsRoute['tagsID'] = $params['tagsID'];
        if (isset($params['sort']))
            $arrParams['sort'] = $params['sort'];
        if (isset($params['page']))
            $arrParams['page'] = $intPage;
        if (isset($params['brand'])) {
            $brand = implode('--', $params['brand']);
            $arrParams['brand'] = $brand;
        }
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'tags', $arrParams);
        $countPage = ceil($intTotal / $intLimit);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode((!empty($tagDetail['tags_meta_title']) ? $tagDetail['tags_meta_title'] : $tagDetail['tags_name'])) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode($tagDetail['tags_meta_keyword']));
        $this->renderer->headMeta()->appendName('description', html_entity_decode($tagDetail['tags_meta_description']));
        $this->renderer->headMeta()->appendName('social', $tagDetail['tags_meta_social']);
        
//        p($params);die;
        return array(
            'params' => $params,
            'paging' => $paging,
            //'arrDetailCategory' => $getDetailCategory,
            'arrProductList' => $arrProductList,
            //'arrCateGradeList' => $CateDetailList,
            'arrCategoryList' => $arrCategoryList,
            'arrBrandList' => $arrBrandList,
            'countPage' => $countPage,
            //'LinkCateBrand' => $LinkCateBrand,
            'BrandDetailList' => $BrandDetailList,
            'tagsDetail' => $tagDetail,
            'arrTags' => $arrTags,
            'intPage'=>$intPage,
            'arrListNewsContent '=>$arrContentList
//            'arrContentList'=>$arrContentList
        );
    }

}
