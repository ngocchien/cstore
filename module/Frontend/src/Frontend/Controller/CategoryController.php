<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate;

class CategoryController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceContent \My\Models\Content */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:category:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js,category.js',
            ];
            $this->defaultCSS = [
                'frontend:category:index' => 'jquery.range.css,checkbox.css',
            ];
            $this->externalJS = [
                'frontend:category:index' => STATIC_URL . '/f/v1/js/my/??cate.js',
            ];
        }
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
//        p($params);die;
        if (empty($params['categoryID']))
            return $this->redirect()->toRoute('404', array());

        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCategoryList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));

        foreach ($arrCategoryList as $value) {
            $arrCategoryListFormat[$value['cate_id']] = $value;
        }
        if (empty($arrCategoryListFormat[$params['categoryID']])) {
            return $this->redirect()->toRoute('404', array());
        }

        $getDetailCategory = $arrCategoryListFormat[$params['categoryID']];
        if (empty($params["categorySlug"]) || ($params["categorySlug"] != $getDetailCategory["cate_slug"])) {
            return $this->redirect()->toRoute('category', array('controller' => 'index', 'action' => 'index', 'categorySlug' => $getDetailCategory["cate_slug"], 'categoryID' => $getDetailCategory["cate_id"]))->setStatusCode('301');
        }

        //get Parent Lớn nhất
        $strMainCategoryID = explode(':', $getDetailCategory['cate_grade'])[0];
        $arrMainCategory = $arrCategoryListFormat[$strMainCategoryID];

        //get list con của main Parent và chidldren of curent category
        $arrCategoryChildrenList = array();
        foreach ($arrCategoryList as $value) {
            if ($value['cate_parent'] == $getDetailCategory['cate_id']) {
                $arrCategoryChildrenList[] = $value;
            }
            $arrCateGrade = explode(':', $value['cate_grade']);
            if (in_array($strMainCategoryID, $arrCateGrade)) {
                $arrCategoryParentList[] = $value;
            }
        }
//        p($arrCategoryChildrenList);die;
        //get ListID con cua category hiện tại
        $arrListCategoryID = array();
        if ($getDetailCategory['cate_parent'] == 0) {
            foreach ($arrCategoryParentList as $value) {
                $arrListCategoryID[] = $value['cate_id'];
            }
        } else {
            foreach ($arrCategoryParentList as $value) {
                $arrTemp = explode(':', $value['cate_grade']);
                if (in_array($getDetailCategory['cate_id'], $arrTemp)) {
                    $arrListCategoryID[] = $value['cate_id'];
                }
            }
        }
        $result = implode(',', $arrListCategoryID);
        $arrCondition = array('cate_id_or_main_cate_id' => $result, 'prod_actived' => 1);

        //  end - cate
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 16;

        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
        }
        //get list Brand
        //get List All
        $arrAllProduct = $serviceProduct->getListProductID(array('cate_id_or_main_cate_id' => $arrCondition['cate_id_or_main_cate_id'], 'prod_actived' => 1));     //lấy list sp có $s,cate_id,main_cate_id
        $arrCondition['cate_id_or_main_cate_id'] = $arrCondition['cate_id_or_main_cate_id'];
        $arrCondition['prod_actived'] = 1;
//        $instanceSearchProduct = new \My\Search\Products();
//        $instanceSearchProduct->setParams($arrCondition);
//        $arrAllProduct = $instanceSearchProduct->getList();

        if (!empty($arrAllProduct)) {
            foreach ($arrAllProduct as $val) {   //lấy brand_id
                $arrPrice[] = $val['prod_price'];
                $arrBrandListID[] = $val['bran_id'];
            }
            //get min_price and max_price
            $max_price = max($arrPrice);
            $min_price = min($arrPrice);
            if ($arrCondition['price_end']) {
                $max_price = $arrCondition['price_end'];
            }
            if ($arrCondition['price_start']) {
                $min_price = $arrCondition['price_start'];
            }
            $arrBrandListID = array_unique($arrBrandListID);
            foreach ($arrBrandListID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrBrandListID[$offset]);
                }
            }
            $strBrandListID = implode(',', $arrBrandListID);
            // lấy list brand tương ứng
            $arrAllBrandList = $serviceCategory->getListSortBrand(array('cate_status' => 1, 'cate_type' => 1, 'listCategoryID' => $strBrandListID));
        }
        $mainBrand = array();
        $arrBrandSlug = array();
        $strMainslug = '';
        $arrBrandDetailList = array();
        if ($params['brand']) {
            $arrTempBrand = explode('--', $params['brand']);
            $strMainslug = $arrTempBrand[0];
            foreach ($arrAllBrandList as $value) {
                if (in_array($value['cate_slug'], $arrTempBrand)) {
                    $arrBrandDetailList[] = $value;
                    $arrBrandSlug[] = $value['cate_slug'];
                    $arrTempID[] = $value['cate_id'];
                }
                if ($value['cate_slug'] == $strMainslug) {
                    $mainBrand = $value;
                }
            }
            $arrCondition['listBrandID'] = implode(',', $arrTempID);
        }
        if ($params['price']) {
            $arrPrice = explode('--', $params['price']);
            $price_start = abs((int) $arrPrice[0]);
            $price_end = abs((int) $arrPrice[1]);

            if ($price_end < $price_start && $price_end > 0)
                return $this->redirect()->toRoute('404', array());

            if ($price_start) {
                $arrCondition['price_start'] = $price_start;
            }
            if ($price_end) {
                $arrCondition['price_end'] = $price_end;
            }
        }

        if (isset($params['sort']) && $params['sort'] && ($params['sort'] == 'id_desc' || $params['sort'] == 'id_asc' || $params['sort'] == 'price_desc' || $params['sort'] == 'price_asc')) {
            $sort = trim($params['sort']);
            $sort = 'prod.prod_' . str_replace('_', ' ', $sort);
        } else if (isset($params['sort'])) {
            $sort = 'sort.sort_ordering DESC';
        } else {
            $sort = 'sort.sort_ordering DESC';
        };

        $arrayConditionSort = array(
            'sort_cate' => $params['categoryID']
        );

        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProductList = $serviceProduct->getListLimitSortingPriceJoinSort($arrCondition, $arrayConditionSort, $intPage, $intLimit, $sort);


        if (!empty($arrProductList)) {
            $listTags = '';
            foreach ($arrProductList as $pro) {
                if (!empty($pro['tags_id']))
                    $listTags .= $pro['tags_id'] . ',';
            }
            $arrTagsList = explode(',', rtrim($listTags, ','));
            $arrTagsList = array_unique($arrTagsList);
            $listTags = implode(',', $arrTagsList);
            $arrTagsList = array();
            if (!empty($listTags)) {
                $arrConditionTag = array(
                    'in_tags_id' => $listTags,
                    'tags_status' => 1
                );
                $serviceTags = $this->serviceLocator->get('My\Models\Tags');
                $arrTagsList = $serviceTags->getList($arrConditionTag);
            }
        }

        $countPage = ceil($intTotal / $intLimit);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $arrParams = array('controller' => 'category', 'action' => 'index', 'categoryID' => $params['categoryID'], 'categorySlug' => $params['categorySlug']);
        if (isset($params['s'])) {
            $arrParams['s'] = str_replace(' ', '+', $s);
        }if (isset($price_start) && isset($price_end))
            $arrParams['price'] = $params['price'];
        if (isset($params['categorySlug']))
            $arrParamsRoute['categorySlug'] = $params['categorySlug'];
        if (isset($params['categoryID']))
            $arrParamsRoute['categoryID'] = $params['categoryID'];
        if (isset($params['sort']))
            $arrParams['sort'] = $params['sort'];
        if (isset($params['page']))
            $arrParams['page'] = $intPage;
        if (isset($params['brand'])) {
            if (is_array($params['brand'])) {
                $brand = implode('--', $params['brand']);
            } else {
                $brand = $params['brand'];
            }

            $arrParams['brand'] = $brand;
        }

        //  print_r($arrParams);die();

        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'category', $arrParams);
        $countPage = ceil($intTotal / $intLimit);

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $getDetailCategory['cate_meta_title'] ? $metaTitle = $getDetailCategory['cate_meta_title'] : $metaTitle = $getDetailCategory['cate_name'];
        $getDetailCategory['cate_meta_title'] ? $nameTitle = $getDetailCategory['cate_meta_title'] : $nameTitle = $getDetailCategory['cate_name'];
        $getDetailCategory['cate_meta_keyword'] ? $metaKeyword = $getDetailCategory['cate_meta_keyword'] : NULL;
        $getDetailCategory['cate_meta_description'] ? $metaDescription = $getDetailCategory['cate_meta_description'] : NULL;
        $getDetailCategory['cate_meta_social'] ? $metaSocial = $getDetailCategory['cate_meta_social'] : NULL;
        $LinkCateBrand = '';
        $listKeyword = array();
        if (isset($params['categoryID']) && isset($params['brand'])) {
            $serviceLinkCateBrand = $this->serviceLocator->get('My\Models\LinkCategoryBrand');
            $arrConditionLinkCateBrand = array(
                'link_cate_brand_brand' => $mainBrand['cate_id'],
                'link_cate_brand_category' => $params['categoryID'],
                'link_status' => 1
            );

            $LinkCateBrand = $serviceLinkCateBrand->getDetail($arrConditionLinkCateBrand);
            $metaDescription = 'Mua sản phẩm ' . $getDetailCategory['cate_name'] . ' chính hãng chất lượng từ thương hiệu ' . $mainBrand['cate_name'] . '  tại Megavita Việt Nam! Click mua ngay đừng bỏ qua giá tốt';

            empty($LinkCateBrand['link_meta_title']) ? $metaTitle = $getDetailCategory['cate_name'] . ' ' . $mainBrand['cate_name'] . ' chính hãng, giá tốt nhất' : $metaTitle = $LinkCateBrand['link_meta_title'];
            empty($LinkCateBrand['link_meta_title']) ? $nameTitle = $mainBrand['cate_name'] . ' ' . $getDetailCategory['cate_name'] : $nameTitle = $LinkCateBrand['link_meta_title'];
            empty($LinkCateBrand['link_meta_keyword']) ? $metaKeyword = str_replace(' ', ',', $metaDescription) : $metaKeyword = $LinkCateBrand['link_meta_keyword'];
            empty($LinkCateBrand['link_meta_description']) ? $metaDescription = $metaDescription : $metaDescription = $LinkCateBrand['link_meta_description'];
            empty($LinkCateBrand['link_meta_social']) ? $metaSocial = $metaSocial . '  ' . $mainBrand['cate_name'] : $metaSocial = $LinkCateBrand['link_meta_social'];

            $instanceSearchKeyword = new \My\Search\Keywords();
            $instanceSearchKeyword->setParams(['page' => 1, 'word_key' => General::clean($nameTitle)])->setLimit(50);
            $listKeyword = $instanceSearchKeyword->getSearchData();
        }

        $serviceBanner = $this->serviceLocator->get('My\Models\Banners');
        $listBanner = $serviceBanner->getList(array('category_id' => $params['categoryID'], 'is_delete' => 0, 'ban_status' => \My\General::ENABLED));
        $arrBanner = array();
        foreach ($listBanner as $banner) {
            $arrLocaltion = explode(',', $banner['ban_location']);
            foreach ($arrLocaltion as $location) {
                $arrBanner[$location] = $banner;
            }
        }
        $this->renderer->headTitle(html_entity_decode($metaTitle) . General::TITLE_META);
        $this->renderer->headMeta()->appendName('keywords', html_entity_decode($metaKeyword));
        $this->renderer->headMeta()->appendName('description', html_entity_decode($metaDescription));
        $this->renderer->headMeta()->appendName('social', $metaSocial);

        $url = $this->serviceLocator->get('viewhelpermanager')->get('URL');
        if (empty($params['brand']) || strlen(explode('--', $params['brand'])[0]) < 2) {
            $this->renderer->headLink(array('rel' => 'canonical', 'href' => BASE_URL . $url('category', array('controller' => 'category', 'action' => 'index', 'categorySlug' => $getDetailCategory["cate_slug"], 'categoryID' => $getDetailCategory["cate_id"]))));
        } else {
            $this->renderer->headLink(array('rel' => 'canonical', 'href' => BASE_URL . $url('category', array('controller' => 'category', 'action' => 'index', 'categorySlug' => $getDetailCategory["cate_slug"], 'categoryID' => $getDetailCategory["cate_id"], 'brand' => explode('--', $params['brand'])[0]))));
        }

        $arrTopview = array();
        if (FRONTEND_TEMPLATE == 'mobile') {
            $arrTopview = $serviceProduct->getListLimit(array('prod_actived' => 1), 1, 10, 'prod_viewer DESC');
//            $instanceSearchProduct = new \My\Search\Products();
//            $arrCondition = array();
//            $arrCondition['prod_actived'] = 1;
//            $arrCondition['page'] = 1;
//            $arrCondition['sort'] = array('prod_viewer' => 'desc');
//            $instanceSearchProduct->setParams($arrCondition)->setLimit(10);
//            $arrTopview = $instanceSearchProduct->getListLimit();
        }

        $instanceSearchContent = new \My\Search\Content();
        $arrCondition = array(
            'cate_id_or_main_cate_id' => $getDetailCategory['cate_id'],
            'cont_status' => 1
        );
        $instanceSearchContent->setParams($arrCondition)->setLimit(10);
        $arrListNewsContent = $instanceSearchContent->getListLimit();

        $arrGradeList = explode(':', $getDetailCategory['cate_grade']);
        foreach ($arrGradeList as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrGradeList[$offset]);
            }
        }
        foreach ($arrCategoryParentList as $value) {
            if (in_array($value['cate_id'], $arrGradeList)) {
                $arrGrade[$value[cate_id]] = $value;
            }
        }
//        p($arrBrandSlug);die;
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrDetailCategory' => $getDetailCategory,
            'arrProductList' => $arrProductList,
            'arrCategoryParentList' => $arrCategoryParentList,
            'arrAllBrandList' => $arrAllBrandList,
            'countPage' => $countPage,
            'LinkCateBrand' => $LinkCateBrand,
            'arrBanner' => $arrBanner,
            'nameTitle' => $nameTitle,
            'intPage' => $intPage,
            'arrTopview' => $arrTopview,
            'arrTags' => $arrTagsList,
            'arrMainCategory' => $arrMainCategory,
            'max_price' => $max_price,
            'min_price' => $min_price,
            'mainBrand' => $mainBrand,
            'arrBrandDetailList' => $arrBrandDetailList,
            'arrBrandSlug' => $arrBrandSlug,
            'arrListNewsContent' => $arrListNewsContent,
            'arrGrade' => $arrGrade,
            'arrCategoryChildrenList' => $arrCategoryChildrenList,
            'listKeyword' => $listKeyword
        );
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();

        if (empty($params['id'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $validator = new Validate();
        if (!$validator->Digits($params['id'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $detailCategory = $serviceCategory->getDetail(array('cate_id' => $params['id'], 'not_cate_status' => -1, 'cate_type' => 0));

        if (count($detailCategory) == 0) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        p('1');
        die();
        $intPage = $this->params()->fromRoute('page', 1);
//        $arrCondition = array('cate_id' => $params['id'], 'not_prod_actived' => -1);
        $intLimit = 15;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProductList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id DESC');
//        $instanceSearchProduct = new \My\Search\Products();
//        $arrCondition = array();
//        $arrCondition['cate_id'] = $params['id'];
//        $arrCondition['not_prod_actived'] = -1;
//        $instanceSearchProduct->setParams($arrCondition);
//        $intTotal = $instanceSearchProduct->getTotal();
//        
//        $instanceSearchProduct = new \My\Search\Products();
//        $arrCondition = array();
//        $arrCondition['cate_id'] = $params['id'];
//        $arrCondition['not_prod_actived'] = -1;
//        $arrCondition['page'] = $intPage;
//        $arrCondition['sort'] = array('prod_id'=> 'desc');
//        $instanceSearchProduct->setParams($arrCondition)->setLimit($intLimit);
//        $arrProductList = $instanceSearchProduct->getListLimit();

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductList' => $arrProductList,
            'detailCategory' => $detailCategory,
        );
    }

    public function menuajaxAction() {
        $this->layout('layout/empty');
        $params = $this->params()->fromPost();
//        p($params);die;
        if (empty($params['categoryID'])) {
            return $this->redirect()->toRoute('404', array());
        }
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');

        $arrCategoryList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
        foreach ($arrCategoryList as $value) {
            $arrCategoryListFormat[$value['cate_id']] = $value;
        }

        $getDetailCategory = $arrCategoryListFormat[$params['categoryID']];
        if (empty($params["categorySlug"]) || ($params["categorySlug"] != $getDetailCategory["cate_slug"])) {
            return $this->redirect()->toRoute('category', array('controller' => 'index', 'action' => 'index', 'categorySlug' => $getDetailCategory["cate_slug"], 'categoryID' => $getDetailCategory["cate_id"]))->setStatusCode('301');
        }

        //get Parent Lớn nhất
        $strMainCategoryID = explode(':', $getDetailCategory['cate_grade'])[0];
        $arrMainCategory = $arrCategoryListFormat[$strMainCategoryID];

        //get list con của main Parent
        foreach ($arrCategoryList as $value) {
            $arrCateGrade = explode(':', $value['cate_grade']);
            if (in_array($strMainCategoryID, $arrCateGrade)) {
                $arrCategoryParentList[] = $value;
            }
        }

        //get ListID con cua category hiện tại
        $arrListCategoryID = array();
        if ($getDetailCategory['cate_parent'] == 0) {
            foreach ($arrCategoryParentList as $value) {
                $arrListCategoryID[] = $value['cate_id'];
            }
        } else {
            foreach ($arrCategoryParentList as $value) {
                $arrTemp = explode(':', $value['cate_grade']);
                if (in_array($getDetailCategory['cate_id'], $arrTemp)) {
                    $arrListCategoryID[] = $value['cate_id'];
                }
            }
        }
        $result = implode(',', $arrListCategoryID);
        $arrCondition = array('cate_id_or_main_cate_id' => $result, 'prod_actived' => 1);

        //  end - cate
        $intPage = is_numeric($this->params()->fromQuery('page', 1)) ? $this->params()->fromQuery('page', 1) : 1;
        $intLimit = 16;

        if ($params['s']) {
            $s = trim($params['s']);
            $arrCondition['search'] = $s;
        }

        //get list Brand
        //get List All
        $arrAllProduct = $serviceProduct->getListProductID(array('cate_id_or_main_cate_id' => $arrCondition['cate_id_or_main_cate_id'], 'prod_actived' => 1));     //lấy list sp có $s,cate_id,main_cate_id
//        $instanceSearchProduct = new \My\Search\Products();
//        $arrCondition['cate_id_or_main_cate_id'] = $arrCondition['cate_id_or_main_cate_id'];
//        $arrCondition['prod_actived'] = 1;
//        $instanceSearchProduct->setParams($arrCondition);
//        $arrAllProduct = $instanceSearchProduct->getList();

        if (!empty($arrAllProduct)) {
            foreach ($arrAllProduct as $val) {   //lấy brand_id
                $arrPrice[] = $val['prod_price'];
                $arrBrandListID[] = $val['bran_id'];
            }
            //get min_price and max_price
            $max_price = max($arrPrice);
            $min_price = min($arrPrice);
            if ($arrCondition['price_end']) {
                $max_price = $arrCondition['price_end'];
            }
            if ($arrCondition['price_start']) {
                $min_price = $arrCondition['price_start'];
            }
//        p($min_price);die;
            $arrBrandListID = array_unique($arrBrandListID);
            foreach ($arrBrandListID as $offset => $row) {
                if ('' == trim($row)) {
                    unset($arrBrandListID[$offset]);
                }
            }
            $strBrandListID = implode(',', $arrBrandListID);
            // lấy list brand tương ứng
            $arrAllBrandList = $serviceCategory->getListSortBrand(array('cate_status' => 1, 'cate_type' => 1, 'listCategoryID' => $strBrandListID));
        }
        $mainBrand = array();
        $arrBrandSlug = array();
        $strMainslug = '';
        $arrBrandDetailList = array();
        if ($params['brand']) {
            $arrTempBrand = explode('--', $params['brand']);
            $strMainslug = $arrTempBrand[0];
            foreach ($arrAllBrandList as $value) {
                if (in_array($value['cate_slug'], $arrTempBrand)) {
                    $arrBrandDetailList[] = $value;
                    $arrBrandSlug[] = $value['cate_slug'];
                    $arrTempID[] = $value['cate_id'];
                }
//                p()
                if ($value['cate_slug'] == $strMainslug) {
                    $mainBrand = $value;
                }
            }
            $arrCondition['listBrandID'] = implode(',', $arrTempID);
        }
//        p($arrBrandDetailList);die;
        if ($params['price']) {
            $arrPrice = explode('--', $params['price']);
            $price_start = abs((int) $arrPrice[0]);
            $price_end = abs((int) $arrPrice[1]);

            if ($price_end < $price_start && $price_end > 0)
                return $this->redirect()->toRoute('404', array());

            if ($price_start) {
                $arrCondition['price_start'] = $price_start;
            }
            if ($price_end) {
                $arrCondition['price_end'] = $price_end;
            }
        }

        if (isset($params['sort']) && $params['sort'] && ($params['sort'] == 'id_desc' || $params['sort'] == 'id_asc' || $params['sort'] == 'price_desc' || $params['sort'] == 'price_asc')) {
            $sort = trim($params['sort']);
            $sort = 'prod.prod_' . str_replace('_', ' ', $sort);
        } else if (isset($params['sort'])) {
            $sort = 'sort.sort_ordering DESC';
        } else {
            $sort = 'sort.sort_ordering DESC';
        };

        //get list Tags

        if (!empty($arrProductList)) {
            $listTags = '';
            foreach ($arrProductList as $pro) {
                if (!empty($pro['tags_id']))
                    $listTags .= $pro['tags_id'] . ',';
            }
            $arrTagsList = explode(',', rtrim($listTags, ','));
            $arrTagsList = array_unique($arrTagsList);
            $listTags = implode(',', $arrTagsList);
            $arrTagsList = array();
            if (!empty($listTags)) {
                $arrConditionTag = array(
                    'in_tags_id' => $listTags,
                    'tags_status' => 1
                );
                $serviceTags = $this->serviceLocator->get('My\Models\Tags');
                $arrTagsList = $serviceTags->getList($arrConditionTag);
            }
        }

        $arrParams = array('controller' => 'category', 'action' => 'index', 'categoryID' => $params['categoryID'], 'categorySlug' => $params['categorySlug']);
        if (isset($params['s'])) {
            $arrParams['s'] = str_replace(' ', '+', $s);
        }if (isset($price_start) && isset($price_end))
            $arrParams['price'] = $params['price'];
        if (isset($params['categorySlug']))
            $arrParamsRoute['categorySlug'] = $params['categorySlug'];
        if (isset($params['categoryID']))
            $arrParamsRoute['categoryID'] = $params['categoryID'];
        if (isset($params['sort']))
            $arrParams['sort'] = $params['sort'];
        if (isset($params['page']))
            $arrParams['page'] = $intPage;
        if (isset($params['brand'])) {
            $brand = implode('--', $params['brand']);
            $arrParams['brand'] = $brand;
        }

        //get list content
        $instanceSearchContent = new \My\Search\Content();
        $arrCondition = array(
            'cate_id_or_main_cate_id' => $getDetailCategory['cate_id'],
            'cont_status' => 1
        );
        $instanceSearchContent->setParams($arrCondition)->setLimit(10);
        $arrListNewsContent = $instanceSearchContent->getListLimit();

        $arrGradeList = explode(':', $getDetailCategory['cate_grade']);
        foreach ($arrGradeList as $offset => $row) {
            if ('' == trim($row)) {
                unset($arrGradeList[$offset]);
            }
        }
        foreach ($arrCategoryParentList as $value) {
            if (in_array($value['cate_id'], $arrGradeList)) {
                $arrGrade[$value[cate_id]] = $value;
            }
        }
        return array(
            'params' => $params,
            'arrDetailCategory' => $getDetailCategory,
            'arrCategoryParentList' => $arrCategoryParentList,
            'arrAllBrandList' => $arrAllBrandList,
            'arrTags' => $arrTagsList,
            'arrMainCategory' => $arrMainCategory,
            'max_price' => $max_price,
            'min_price' => $min_price,
            'mainBrand' => $mainBrand,
            'arrBrandDetailList' => $arrBrandDetailList,
            'arrBrandSlug' => $arrBrandSlug,
            'arrListNewsContent' => $arrListNewsContent,
            'arrGrade' => $arrGrade
        );
    }

}
