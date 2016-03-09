<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;
use Zend\View\Model\ViewModel;

class BrandController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:brand:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js',
            ];
            $this->defaultCSS = [
                'frontend:brand:index' => 'jquery.range.css,checkbox.css',
            ];
            $this->externalJS = [
                'frontend:brand:index' => STATIC_URL . '/f/v1/js/my/??brand.js,category.js',
            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
//        p($params);die();
        if (empty($params['brandSlug'])) {
            return $this->redirect()->toRoute('404', array());
        }

        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $intLimit = 16;
        $arrCondition = array('prod_actived' => 1);

        $params['brandSlug'] = explode('--', $params['brandSlug']);
        $listBrandSlug = '';
        foreach ($params['brandSlug'] as $val) {
            $listBrandSlug .= "'" . $val . "',";   // => ex : 'a','b',
        }
        $listBrandSlug = rtrim($listBrandSlug, ',');
        $BrandDetailList = $serviceCategory->getList(array('cate_type' => 1, 'cate_status' => 1, 'listCategorySlug' => $listBrandSlug));
        if (!$BrandDetailList)
            return $this->redirect()->toRoute('404', array());

        foreach ($BrandDetailList as $val) {
            $brand_id[] = $val['cate_id'] ? $val['cate_id'] : NULL;
            $params['brand_name'][] = $val['cate_name'];
        }
        $arrCondition['listBrandID'] = $brand_id;

        $arrBrandList = $serviceCategory->getListSort(array('cate_type' => 1, 'cate_status' => 1));
        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
        }

        $arrCategoryList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        if ($params['categoryID']) {
            $getDetailCategory = $serviceCategory->getDetail(array('cate_id' => $params['categoryID'], 'cate_type' => 0, 'cate_status' => 1));
            // p($getDetailCategory);die;
            $arrCondition['cate_id_or_main_cate_id'] = $params['categoryID'];

            $cate_grade = explode(':', rtrim($getDetailCategory['cate_grade'], ':'));
            foreach ($arrCategoryList as $val) {
                if (strstr($val['cate_grade'], $cate_grade[0]))
                    $arrCateGradeList[] = $val;
            }

            $params['cate_name'] = $getDetailCategory['cate_name'];
        }else {
            $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
            foreach ($ARR_CATEGORY_LIST as $category) {
                if ($category['cate_parent'] == 0) {
                    $arrCategoryParentList[] = $category;
                }
            }
        }

        if ($params['price']) {
            $arrPrice = explode('--', $params['price']);
            $price_start = abs((int) $arrPrice[0]);
            $price_end = abs((int) $arrPrice[1]);

            if ($price_end < $price_start && $price_end > 0)
                return $this->redirect()->toRoute('404', array());

            if ($price_start)
                $arrCondition['price_start'] = $price_start;

            if ($price_end)
                $arrCondition['price_end'] = $price_end;
        }

        if (isset($params['sort']) && $params['sort'] && ($params['sort'] == 'id_desc' || $params['sort'] == 'id_asc' || $params['sort'] == 'price_desc' || $params['sort'] == 'price_asc')) {
            $sort = trim($params['sort']);
            $sort = 'prod_' . str_replace('_', ' ', $sort);
        } else if (isset($params['sort'])) {
            $sort = 'prod_id desc';
        } else {
            $sort = 'prod_id desc';
        };
        $intTotal = $serviceProduct->getTotal($arrCondition); // 2
        $arrProductList = $serviceProduct->getListLimitSortingPrice($arrCondition, $intPage, $intLimit, $sort);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $intTotal = $instanceSearchProduct->getTotal();
//        
//        $sort = explode(' ', $sort);
//        $arrCondition['page'] = $intPage;
//        $arrCondition['sort'] = array($sort[0] => $sort[1]);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition)->setLimit($intLimit);
//        $arrProductList = $instanceSearchProduct->getListLimit();
        //Tags products
        $arrTagsList = array();
        if (!empty($arrProductList)) {
            $listTags = '';
            foreach ($arrProductList as $pro) {
                if (empty($pro['tags_id']))
                    continue;
                $listTags .= $pro['tags_id'] . ',';
            }
            $arrTagsList = explode(',', $listTags);
            $arrTagsList = array_unique($arrTagsList);
            foreach ($arrTagsList as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrTagsList[$offset]);
                }
            }
            $listTags = implode(',', $arrTagsList);
            if (!empty($listTags)) {
                $arrConditionTag = array(
                    'in_tags_id' => $listTags,
                    'tags_status' => 1
                );
                $serviceTags = $this->serviceLocator->get('My\Models\Tags');
                $arrTagsList = $serviceTags->getList($arrConditionTag);
            }
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $arrParamsRoute = array('controller' => 'brand', 'action' => 'index', 'page' => $intPage, 'brandSlug' => implode('--', $params['brandSlug']));

        if (isset($params['s']))
            $arrParamsRoute['s'] = str_replace(' ', '+', $s);
        if (isset($params['categorySlug']))
            $arrParamsRoute['categorySlug'] = $params['categorySlug'];
        if (isset($params['categoryID']))
            $arrParamsRoute['categoryID'] = $params['categoryID'];
        if (isset($price_start) && isset($price_end))
            $arrParamsRoute['price'] = $params['price'];
        if (isset($params['sort']))
            $arrParamsRoute['sort'] = $params['sort'];

        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'brand', $arrParamsRoute);
        $countPage = ceil($intTotal / $intLimit);
        $strbrand = '';
        foreach ($params['brandSlug'] as $key => $value) {
            $strbrand = $value . ',' . $strbrand;
        }

//        $LinkCateBrand = '';
//        if (isset($params['categoryID']) && isset($params['brandSlug'])) {
//            $serviceLinkCateBrand = $this->serviceLocator->get('My\Models\LinkCategoryBrand');
//            $arrConditionLinkCateBrand = array(
//                'link_cate_brand_brand' => $brand_id[0],
//                'link_cate_brand_category' => $params['categoryID']
//            );
//            $LinkCateBrand = $serviceLinkCateBrand->getDetail($arrConditionLinkCateBrand);
//            if (!empty($LinkCateBrand)) {
//
//                !empty($LinkCateBrand['link_meta_title']) ? $metaTitle = $LinkCateBrand['link_meta_title'] : null;
//                !empty($LinkCateBrand['link_meta_keyword']) ? $metaKeyword = $LinkCateBrand['link_meta_keyword'] : null;
//                !empty($LinkCateBrand['link_meta_description']) ? $metaDescription = $LinkCateBrand['link_meta_description'] : null;
//                !empty($LinkCateBrand['link_meta_social']) ? $metaSocial = $LinkCateBrand['link_meta_social'] : null;
//            }
//        }
        !empty($BrandDetailList[0]['cate_meta_title']) ? $metaTitle = $BrandDetailList[0]['cate_meta_title'] : $metaTitle = $BrandDetailList[0]['cate_name'];
        !empty($BrandDetailList[0]['cate_meta_keyword']) ? $metaKeyword = $BrandDetailList[0]['cate_meta_keyword'] : NULL;
        !empty($BrandDetailList[0]['cate_meta_description']) ? $metaDescription = $BrandDetailList[0]['cate_meta_description'] : NULL;
        !empty($BrandDetailList[0]['cate_meta_social']) ? $metaSocial = $BrandDetailList[0]['cate_meta_social'] : NULL;

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode($metaTitle) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode($metaKeyword));
        $this->renderer->headMeta()->appendName('description', html_entity_decode($metaDescription));
        $this->renderer->headMeta()->appendName('social', $metaSocial);

        $url = $this->serviceLocator->get('viewhelpermanager')->get('URL');
        $this->renderer->headLink(array('rel' => 'canonical', 'href' => BASE_URL . $url('brand', array('controller' => 'brand', 'action' => 'index', 'brandSlug' => $params['brandSlug'][0]))));

        $strBrandSlugList = implode('--', $params['brandSlug']);
//        p($params);die;

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductList' => $arrProductList,
            'countPage' => $countPage,
            'getDetailCategory' => $getDetailCategory,
            'arrCategoryParentList' => $arrCategoryParentList,
            'arrCateGradeList' => $arrCateGradeList,
            'arrBrandList' => $arrBrandList,
            'LinkCateBrand' => $LinkCateBrand,
            'BrandDetailList' => $BrandDetailList,
            'arrTags' => $arrTagsList,
            'strBrandSlugList' => $strBrandSlugList,
            'ARR_CATEGORY_LIST' =>$ARR_CATEGORY_LIST
        );
    }

    public function getAjaxAction() {
        $params = $this->params()->fromPost();
        if (empty($params['strbrandSlug'])) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $strBrandSlugList = str_replace('--', ',', $params['strbrandSlug']);
        $arrBrandSlugList = explode(',', $strBrandSlugList);
        //get All Brand

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrAllBrand = $serviceCategory->getList(array('cate_type' => 1, 'cate_status' => 1));
        $arrBrandDetailList = array();
        $arrBrandListID = array();
        foreach ($arrAllBrand as $value) {
            if (in_array($value['cate_slug'], $arrBrandSlugList)) {
                $arrBrandDetailList[$value['cate_id']] = $value;
                $arrBrandListID[] = $value['cate_id'];
            }
        }


        $arrCondition['listBrandID'] = $arrBrandListID;
        //GET category
        $arrCategoryList =  $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));

        if ($params['cateID']) {
            $getDetailCategory = $serviceCategory->getDetail(array('cate_id' => $params['cateID'], 'cate_type' => 0, 'cate_status' => 1));
            $arrCondition['cate_id_or_main_cate_id'] = $params['cateID'];
            $cate_grade = explode(':', rtrim($getDetailCategory['cate_grade'], ':'));
            foreach ($arrCategoryList as $val) {
                if (strstr($val['cate_grade'], $cate_grade[0]))
                    $arrCateGradeList[] = $val;
            }
            $params['cate_name'] = $getDetailCategory['cate_name'];
        }else {
            foreach ($arrCategoryList as $category) {
                if ($category['cate_parent'] == 0) {
                    $arrCategoryParentList[] = $category;
                }
            }
        }
        if (!empty($params['price'])) {
            $arrPrice = explode('--', $params['price']);
            $price_start = abs((int) $arrPrice[0]);
            $price_end = abs((int) $arrPrice[1]);

            if ($price_end < $price_start && $price_end > 0)
                return $this->redirect()->toRoute('404', array());

            if ($price_start)
                $arrCondition['price_start'] = $price_start;

            if ($price_end)
                $arrCondition['price_end'] = $price_end;
        }

//        p($arrCondition);die;
        //get Product = 
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrProductList = $serviceProduct->getList($arrCondition);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $arrProductList = $instanceSearchProduct->getList();
        
        if (!empty($arrProductList)) {
            foreach ($arrProductList as $value) {
                $arrListPrice[] = $value['prod_price'];
            }
        }
        $min_price = empty($arrCondition['price_start']) ? min($arrListPrice) : $arrCondition['price_start'];
        $max_price = empty($arrCondition['price_end']) ? max($arrListPrice) : $arrCondition['price_end'];
//        $a = imp
//        p($strBrandSlugList);die;
        $arrData = array(
            'arrCategoryList' => $arrCategoryList,
            'arrAllBrand' => $arrAllBrand,
            'arrBrandDetailList' => $arrBrandDetailList,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'arrCateGradeList' => $arrCateGradeList,
            'strBrandSlugList' => $params['strbrandSlug'],
            'getDetailCategory' => $getDetailCategory,
            'arrCategoryParentList' => $arrCategoryParentList,
            'arrBrandSlugList' => $arrBrandSlugList
        );
        $view = new ViewModel($arrData);
        $view->setTemplate('frontend/brand_ajax');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
    }

//
//    public function viewAction() {
//        $params = $this->params()->fromRoute();
//
//        if (empty($params['id'])) {
//            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
//        }
//
//        $validator = new Validate();
//        if (!$validator->Digits($params['id'])) {
//            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
//        }
//
//        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
//        $detailCategory = $serviceCategory->getDetail(array('cate_id' => $params['id'], 'not_cate_status' => -1, 'cate_type' => 0));
//
//        if (count($detailCategory) == 0) {
//            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
//        }
//
//        $intPage = $this->params()->fromRoute('page', 1);
//        $arrCondition = array('cate_id' => $params['id'], 'not_prod_actived' => -1);
//        $intLimit = 15;
//        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//        $intTotal = $serviceProduct->getTotal($arrCondition);
//
//        $arrProductList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id DESC');
//        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
//        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
//
//        return array(
//            'params' => $params,
//            'paging' => $paging,
//            'arrProductList' => $arrProductList,
//            'detailCategory' => $detailCategory,
//        );
//    }
//
//    protected function valid($params = array()) {
//        if ($params) {
//            foreach ($params as $val) {
//                if (substr_count("\ " . $val . "\ ", trim("\ ")) > 2)       //kiểm tra 1 số kí tư đb ảnh hưởng đến truy vấn
//                    return true;
//                if (substr_count($val, '"') > 0)
//                    return true;
//                if (substr_count($val, "'") > 0)
//                    return true;
//            }
//            return false;
//        }
//    }
}
