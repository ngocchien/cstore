<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General;
use Zend\View\Model\ViewModel;

class SearchController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceProperties \My\Models\Properties */
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceKeyword \My\Models\Keyword */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:search:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js,category.js',
            ];
            $this->defaultCSS = [
                'frontend:search:index' => 'jquery.range.css,checkbox.css',
            ];
            $this->externalJS = [
                'frontend:search:index' => STATIC_URL . '/f/v1/js/my/??search.js',
            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['s'] = General::clean($params['s']);
        if (empty($params['s']) && empty($params['categoryID']) && empty($params['keySlug']))
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 8;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrCondition = array('prod_actived' => 1);
        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
            $intLimit = 15;
        }

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCategoryListFormat = array();
        $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        foreach ($ARR_CATEGORY_LIST as $value) {
            $arrCategoryListFormat[$value['cate_id']] = $value;
        }

        if ($params['keySlug']) {
            $instanceSearchKeyword = new \My\Search\Keywords();
            $instanceSearchKeyword->setParams(array('page' => 1, 'word_slug' => $params['keySlug']))->setLimit(1);
            $Keyword = current($instanceSearchKeyword->getDetailData());
            $params['s'] = $Keyword['word_key'];
        }
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
        $arrCategoryList = array();
        $arrCateParentList = array();
        $arrListProduct = $serviceProduct->getListProductID($arrCondition);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $arrListProduct = $instanceSearchProduct->getList();

        $strListBrandID = '';
        $strListCateID = '';
        foreach ($arrListProduct as $value) {
            $strListBrandID = $strListBrandID . ',' . $value['bran_id'];
            $strListCateID = $strListCateID . ',' . $value['cate_id'] . ',' . $value['main_cate_id'];
            $arrListPrice[] = $value['prod_price'];
        }
        // max and min price 
        $max_price = max($arrListPrice);
        $min_price = min($arrListPrice);
        if ($arrCondition['price_end']) {
            $max_price = $arrCondition['price_end'];
        }
        if ($arrCondition['price_start']) {
            $min_price = $arrCondition['price_start'];
        }
        //cover string => array()
        $arrListBrandID = explode(',', $strListBrandID);
        $arrListCateID = explode(',', $strListCateID);
//        p($arrListBrandID);die;
        //format array;
        $arrListBrandID = array_unique($arrListBrandID);
        $arrListCateID = array_unique($arrListCateID);
        foreach ($arrListBrandID as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrListBrandID[$offset]);
            }
        }
        foreach ($arrListCateID as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrListCateID[$offset]);
            }
        }

        foreach ($arrListCateID as $val) {
            if (!empty($arrCategoryListFormat[$val])) {
                $arrCategoryList[$val] = $arrCategoryListFormat[$val];
            }
        }
        foreach ($arrCategoryList as $val) {
            if ($val['cate_parent'] == 0) {
                $arrCateParentList[$val['cate_id']] = $val;
            }
        }


        //List
        $arrCateParentList = $this->array_sort($arrCateParentList, 'cate_sort', SORT_ASC);

        //get ListBrand in ListProduct
        $strListBrandID = implode(',', $arrListBrandID);
        $arrBrandList = array();
        if (!empty($strListBrandID)) {
            $arrBrandList = $serviceCategory->getListSortBrand(array('cate_type' => 1, 'cate_status' => 1, 'listCategoryID' => $strListBrandID));
        }
        $arrBrandDetailList = array();
        $arrBrandListSlug = array();
        if ($params['brand']) {
            $arrBrandListSlug = explode('--', $params['brand']);
            foreach ($arrBrandList as $value) {
                if (in_array($value['cate_slug'], $arrBrandListSlug)) {
                    $arrBrandDetailList[$value['cate_id']] = $value;
                    $arrTempID[] = $value['cate_id'];
                }
            }
            $arrCondition['listBrandID'] = $arrTempID;
        }

        if (isset($params['sort']) && $params['sort'] && ($params['sort'] == 'id_desc' || $params['sort'] == 'id_asc' || $params['sort'] == 'price_desc' || $params['sort'] == 'price_asc')) {
            $sort = trim($params['sort']);
            $sort = 'prod_' . str_replace('_', ' ', $sort);
            $sort = explode(' ', $sort); // [0] => prod_id , [1]=> desc
        } else if (isset($params['sort'])) {
            $sort = 'score desc';
        } else {
            $sort = 'score desc';
        }

        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProductList = $serviceProduct->getListLimitSortingPrice($arrCondition, $intPage, $intLimit, $sort);
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $intTotal = $instanceSearchProduct->getTotal();
//        
      
        $instanceSearchProduct = new \My\Search\Products();
        $arrCondition['page'] = $intPage;
        if ($sort != 'score desc') {
            $arrCondition['sort'] = array($sort[0] => $sort[1]);
        }
        $instanceSearchProduct->setParams($arrCondition)->setLimit($intLimit);
        $arrProductList = $instanceSearchProduct->getListLimit();
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

        //keyword
        $keyword = empty($params['s']) ? General::getSlug($params['keySlug']) : General::getSlug($params['s']);
        // Lấy dữ liệu từ trong elasticsearch

        $instanceSearchKeyword = new \My\Search\Keywords();
        $instanceSearchKeyword->setParams(array('page' => 1, 'word_slug' => $keyword))->setLimit(50);

        $dataKey = $arrTempKW = current($instanceSearchKeyword->getDetailData());

        // Nếu không có sẽ kiểm tra từ DB
       // $serviceKeyword = $this->serviceLocator->get('My\Models\Keyword');
     //   if (empty($arrTempKW)) {//            
            //   $arrTempKW = $dataKey = $serviceKeyword->getDetail(array('word_slug' => $keyword, 'word_status' => 1));
     //   }


        $arrDataKeyword = array();

        if (!empty($dataKey)) {

            empty(json_decode($dataKey['word_data'])) ? $arrWordData = array() : $arrWordData = json_decode($dataKey['word_data']);

            if (!empty(array_search($keyword, $arrWordData))) {
                unset($arrWordData[array_search($keyword, $arrWordData)]);
            }

            empty(json_decode($dataKey['word_samelevel'])) ? $arrWordSamelevel = array() : $arrWordSamelevel = json_decode($dataKey['word_samelevel']);

            $arrDataKeyword = (array) $arrWordSamelevel + (array) $arrWordData;
            //	print_r($arrDataKeyword);
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $arrParamsRoute = array('controller' => 'search', 'action' => 'index', 'page' => $intPage);
        if (isset($params['s']))
            $arrParamsRoute['s'] = str_replace(' ', '+', $s);
        if (isset($price_start) && isset($price_end))
            $arrParamsRoute['price'] = $params['price'];
        if (isset($params['sort']))
            $arrParamsRoute['sort'] = $params['sort'];
        if (isset($params['brand'])) {
            $brand = implode('--', $params['brand']);
            $arrParamsRoute['brand'] = $brand;
        }
        if (isset($params['keySlug'])) {
            $arrParamsRoute['keySlug'] = $params['keySlug'];
        }


//        p($arrParamsRoute);die;
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'frontend-search', $arrParamsRoute);
        $countPage = ceil($intTotal / $intLimit);
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle($params['s'] . ' - Tìm kiếm' . General::TITLE_META);

        //get content
//        $serviceContent = $this->serviceLocator->get('My\Models\Content');
//        $arrContentList = $serviceContent->getLimit(array('search' => $params['s'], 'cont_status' => 1), $intPage, 15, 'score DESC');
        $instanceSearchContent = new \My\Search\Content();
        $arrCondition = array(
            'search' => $params['s'],
            'cont_status' => 1,
            'page' => $intPage,
        );
        $instanceSearchContent->setParams($arrCondition)->setLimit(15);
        $arrContentList = $instanceSearchContent->getListLimit();
        //get 50 keyword
        $instanceSearchKeyword = new \My\Search\Keywords();
        //echo $arrTempKW['word_id'];die();
        $wordId = empty($arrTempKW['word_id']) ? 50 : $arrTempKW['word_id'];

        $instanceSearchKeyword->setParams(['page' => 0, 'word_id_greater' => $wordId]);
        $instanceSearchKeyword->setLimit(50);
        $arrAllKeyword = $instanceSearchKeyword->getListLimit();

        $instanceSearchKeyword = new \My\Search\Keywords();

        $instanceSearchKeyword->setParams(['page' => 0, 'word_id_less' => $wordId]);
        $instanceSearchKeyword->setLimit(50);
        foreach ($instanceSearchKeyword->getListLimit() as $value) {
            $arrAllKeyword[] = $value;
        }


        return array(
            'paging' => $paging,
            'countPage' => $countPage,
            'intPage' => $intPage,
            'params' => $params,
            'arrProductList' => $arrProductList,
            'arrContentList' => $arrContentList,
            'arrTagsList' => $arrTagsList,
            'arrDataKeyword' => $arrDataKeyword,
            'arrBrandDetailList' => $arrBrandDetailList,
            'max_price' => $max_price,
            'min_price' => $min_price,
            'arrBrandList' => $arrBrandList,
            'arrCateParentList' => $arrCateParentList,
            'arrBrandListSlug' => $arrBrandListSlug,
            'arrAllKeyword' => $arrAllKeyword
        );
    }

    static function unparse_url($parsed_url) {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    static function array_sort($array, $on, $order = SORT_ASC) {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    public function getAjaxAction() {
        $this->layout('layout/empty');  //disable layout
        $params = $this->params()->fromPost();
        if (empty($params['s']) && empty($params['categoryID'])) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrCondition = array('prod_actived' => 1);
        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
        }
        $arrCategoryListFormat = array();
        $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        foreach ($ARR_CATEGORY_LIST as $value) {
            $arrCategoryListFormat[$value['cate_id']] = $value;
        }

        if (!empty($params['price'])) {
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

        $arrCategoryList = array();
        if (!empty($params['categoryID'])) {
            $getDetailCategory = $arrCategoryListFormat[$params['categoryID']];
            $parent = explode(':', rtrim($getDetailCategory['cate_grade'], ':'));
            foreach ($parent as $value) {
                if (!empty($value)) {
                    $arrCateGradeList[$value] = $arrCategoryListFormat[$value];
                }
            }
            $arrCondition['cate_id_or_main_cate_id'] = $params['categoryID'];
            foreach ($arrCategoryListFormat as $val) {
                if (strstr($val['cate_grade'], $parent[0])) {
                    $arrCateGradeList[$val['cate_id']] = $val;
                }
            }
            $params['cate_name'] = $getDetailCategory['cate_name'];
            $arrListProduct = $serviceProduct->getListProductID($arrCondition);
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams($arrCondition);
//            $arrListProduct = $instanceSearchProduct->getList();

            $strListBrandID = '';
            $strListCateID = $strListCateID . ',' . $value['cate_id'];
            foreach ($arrListProduct as $value) {
                $strListBrandID = $strListBrandID . ',' . $value['bran_id'];
                $strListCateID = $strListCateID . ',' . $value['cate_id'];
                $arrListPrice[] = $value['prod_price'];
            }
            // max and min price
            $max_price = max($arrListPrice);
            $min_price = min($arrListPrice);
            if ($arrCondition['price_end']) {
                $max_price = $arrCondition['price_end'];
            }
            if ($arrCondition['price_start']) {
                $min_price = $arrCondition['price_start'];
            }
            //cover string => array()
            $arrListBrandID = explode(',', $strListBrandID);
            $arrListCateID = explode(',', $strListCateID);

            //format array;
            $arrListBrandID = array_unique($arrListBrandID);
            $arrListCateID = array_unique($arrListCateID);

            foreach ($arrListBrandID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrListBrandID[$offset]);
                }
            }

            foreach ($arrListCateID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrListCateID[$offset]);
                }
            }
        } else {
            $arrListProduct = $serviceProduct->getListProductID($arrCondition);
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams($arrCondition);
//            $arrListProduct = $instanceSearchProduct->getList();

            $strListBrandID = '';
            $strListCateID = '';
            foreach ($arrListProduct as $value) {
                $strListBrandID = $strListBrandID . ',' . $value['bran_id'];
                $strListCateID = $strListCateID . ',' . $value['cate_id'] . ',' . $value['main_cate_id'];
                $arrListPrice[] = $value['prod_price'];
            }
            // max and min price 
            $max_price = max($arrListPrice);
            $min_price = min($arrListPrice);

            if ($arrCondition['price_end']) {
                $max_price = $arrCondition['price_end'];
            }
            if ($arrCondition['price_start']) {
                $min_price = $arrCondition['price_start'];
            }

            //cover string => array()
            $arrListBrandID = explode(',', $strListBrandID);
            $arrListCateID = explode(',', $strListCateID);

            //format array;
            $arrListBrandID = array_unique($arrListBrandID);
            $arrListCateID = array_unique($arrListCateID);
            foreach ($arrListBrandID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrListBrandID[$offset]);
                }
            }

            foreach ($arrListCateID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrListCateID[$offset]);
                }
            }

            foreach ($arrListCateID as $val) {
                if (!empty($arrCategoryListFormat[$val])) {
                    $arrCategoryList[$val] = $arrCategoryListFormat[$val];
                }
            }
            foreach ($arrCategoryList as $val) {
                if ($val['cate_parent'] == 0) {
                    $arrCateParentList[$val['cate_id']] = $val;
                }
            }
            $arrCateParentList = $this->array_sort($arrCateParentList, 'cate_sort', SORT_ASC);
        }
        //get detail List Brand
        $arrListBrandDetail = $serviceCategory->getListSort(array('cate_type' => 1, 'cate_status' => 1));
        //format $arrBrandList
        foreach ($arrListBrandDetail as $val) {
            if (in_array($val['cate_id'], $arrListBrandID)) {
                $arrBrandList[$val['cate_id']] = $val;
            }
        }
        if ($params['brand']) {
            $arrBrandSlug = explode(',', $params['brand']);
            foreach ($arrBrandList as $value) {
                if (in_array($value['cate_slug'], $arrBrandSlug)) {
                    $BrandDetailList[$value['cate_id']] = $value;
                }
            }
            foreach ($BrandDetailList as $val) {
                $brand_id[] = $val['cate_id'] ? $val['cate_id'] : NULL;
                $params['brand_name'][] = $val['cate_slug'];
            }
            $arrCondition['or_brand_id'] = implode(',', $brand_id);
        }
        $arrParamsRoute = array('controller' => 'search', 'action' => 'index', 'page' => $intPage);
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
        if (isset($params['brand'])) {
            $brand = implode('--', $params['brand']);
            $arrParamsRoute['brand'] = $brand;
        }

        foreach ($params as $offset => $row) {
            if ('' == trim($row)) {
                unset($params[$offset]);
            }
        }
        $arrData = array(
            'max_price' => $max_price,
            'min_price' => $min_price,
            'BrandDetailList' => $BrandDetailList,
            'arrBrandList' => $arrBrandList,
            'arrCateGradeList' => $arrCateGradeList,
            'arrListCateID' => $arrListCateID,
            'categoryID' => $params['categoryID'],
            'arrCateParentList' => $arrCateParentList,
            'arrListBrandDetail' => $arrListBrandDetail,
            'params' => $params
        );
        $view = new ViewModel($arrData);
        $view->setTemplate('frontend/search_ajax');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
    }

}
