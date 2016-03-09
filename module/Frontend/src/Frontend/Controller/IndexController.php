<?php

namespace Frontend\Controller;

use My\Controller\MyController;

class IndexController extends MyController {
    /* @var $serviceTemplate \My\Models\Template */
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
//        if (strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "bot") === false) {
//            // Kiểm tra robot
//            echo "<center><h2>Trang Web đang được bảo trì, quý khách vui lòng quay lại sau</h2></center>";
//            die();
//        }

        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:index:index' => 'jquery.lazyload.js',
            ];
        }
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        if (FRONTEND_TEMPLATE == 'v1') {
            $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
            $arrTemplateList = $serviceTemplate->getList(array('status' => 1, 'is_mobile' => 0));
            $arrListProductID = array();
            $strListCategoryID = '';
            foreach ($arrTemplateList as $key => $value) {
                if ($value['template'] == 4) {
                    $strListCategoryID = $strListCategoryID . ',' . json_decode($value['category_id'], true)['item'];
                    foreach (json_decode($value['json_product'], true) as $arrProductList) {
                        foreach ($arrProductList['item'] as $arrProduct) {
                            $arrListProductID[] = $arrProduct;
                        }
                    }
                }
            }
            //get Content in category
            $arrListCategoryID = explode(',', $strListCategoryID);
            $arrListCategoryID = array_unique($arrListCategoryID);
            array_shift($arrListCategoryID);
            if (count($arrListCategoryID) != 0) {
                $strListIdCategory = implode(',', $arrListCategoryID);
//                $serviceContent = $this->serviceLocator->get('My\Models\Content');
//                $arrListContent = $serviceContent->getList(array('listCategoryID' => $strListIdCategory, 'not_cont_status' => -1));
                $instanceSearchContent = new \My\Search\Content();
                $arrCondition = array(
                    'listCategoryID' => $arrListCategoryID,
                    'not_cont_status' => -1
                );
                $instanceSearchContent->setParams($arrCondition);
                $arrListContent = $instanceSearchContent->getList();
            }

            if (count($arrListCategoryID) == 0) {
                $arrListContent = array();
            }


            $listCategory = $serviceCategory->getList(array('listCategoryID' => $strListIdCategory, 'not_cate_status' => -1));
            $arrListCategory = array();
            foreach ($listCategory as $val) {
                $arrListCategory[$val['cate_id']] = $val;
            }

            //get Product
            $arrListProductID = array_unique($arrListProductID);
            foreach ($arrListProductID as $k => $v) {
                $listIdProduct[] = $v;
            }
//       p($arrListProductID);die;
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $strListIdProduct = implode(',', $listIdProduct);
            $arrCondition = array('listProductID' => $strListIdProduct, 'not_prod_status' => -1);
            $listProduct = $serviceProduct->getList($arrCondition);
//            $instanceSearchProduct = new \My\Search\Products();
//            $instanceSearchProduct->setParams(array('listProductID' => $listIdProduct, 'not_prod_status' => -1));
//            $listProduct = $instanceSearchProduct->getList();

            $arrListProduct = array();
            foreach ($listProduct as $val) {
                $arrListProduct[$val['prod_id']] = $val;
            }
//        p($arrListProduct);die;
            $arrCookieProduct = unserialize($_COOKIE['cookieProduct']);
            if ($arrCookieProduct) {
                $listProduct = implode(',', $arrCookieProduct);
                $arrProductCookieList = $serviceProduct->getListLimit(array('listProductID' => $listProduct), 1, 20, 'prod_id ASC');
//                $instanceSearchProduct = new \My\Search\Products();
//                $instanceSearchProduct->setParams(array('listProductID' => $arrCookieProduct, 'page' => 1 ,'sort' => array('prod_id'=> 'asc')))->setLimit(20);
//                $arrProductCookieList = $instanceSearchProduct->getListLimit();
            }

            return array(
                'arrTemplateList' => $arrTemplateList,
                'arrListProduct' => $arrListProduct,
                'arrListContent' => $arrListContent,
                'arrProductCookieList' => $arrProductCookieList
            );
        } else {
            $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
            $arrTemplateList = $serviceTemplate->getList(array('status' => 1, 'is_mobile' => 1));
            $ARR_CATEGORY_LIST = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));
            foreach ($ARR_CATEGORY_LIST as $key => $value) {
                if ($value['cate_parent'] == 0) {
                    $arrListCategoryParent[$value['cate_id']] = $value;
                }
            }
            return array(
                'arrTemplateList' => $arrTemplateList,
                'arrListCategoryParent' => $arrListCategoryParent,
            );
        }
    }

}
