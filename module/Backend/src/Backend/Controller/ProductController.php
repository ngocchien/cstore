<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class ProductController extends MyController {
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceProperties \My\Models\Properties */

    public function __construct() {
        $this->defaultJS = [
            'backend:product:add' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:product:edit' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:product:index' => 'jquery.sumoselect.min.js,bootstrap-select.js',
        ];
        $this->defaultCSS = [
            'backend:product:add' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css',
            'backend:product:edit' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css',
            'backend:product:index' => 'sumoselect.css,bootstrap-select.css',
        ];

        $this->externalJS = [
            'backend:product:index' => array(
                STATIC_URL . '/b/js/my/??product.js'
            ),
            'backend:product:add' => array(
                STATIC_URL . '/b/js/my/??product.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
                STATIC_URL . '/b/js/library/??Nileupload-min.js',
            ),
            'backend:product:edit' => array(
                STATIC_URL . '/b/js/my/??product.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
                STATIC_URL . '/b/js/library/??Nileupload-min.js',
            )
        ];
    }

    public function indexAction() {

        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $intPage = $this->params()->fromQuery('page', 1);
        $route = 'backend-product-search';
        $arrCondition = array(
            'prod_actived' => 1,
            'or_brand_id' => $params['brand_id'],
            'cate_id_or_main_cate_id' => $params['cate_id'],
            'prod_name_like' => General::clean(trim($this->params()->fromQuery('s'))),
            'order_sort' => $params['order_sort'],
        );
        //p($arrCondition);die;
        $intLimit = $this->params()->fromQuery('limit', 15);
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $intTotal = $serviceProduct->getTotal($arrCondition);
        $arrProductList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id DESC');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
//        
//        $listCategory = '';
//        $listBrand = '';
//        foreach ($arrProductList as $key => $value) {
//            $listCategory = $value['cate_id'] . ',' .$listCategory;
//            $listBrand = $value['bran_id'].','. $listBrand ;
//        }
//
//        $arrCategoryDetailList = array();
//        if (trim($listCategory) != "") {
//            $result = array_unique(array_filter(explode(',', $listCategory)));
//            $arrCategoryList = $serviceCategory->getDetailCategoryList($result);
//            //print_r($arrCategoryList);die;
//            foreach ($arrCategoryList as $key => $value) {
//                $arrCategoryDetailList[$value['cate_id']] = $value;
//            }
//        }
//        $arrBrandDetailList = array();
//        if (trim($listBrand) != "") {
//            $result = array_unique(array_filter(explode(',', $listBrand)));
//            $arrBrandList = $serviceCategory -> getDetailCategoryList($result);
//            foreach ($arrBrandList as $key => $value) {
//                $arrBrandDetailList[$value['cate_id']] = $value;
//            }
//        }
        //print_r($arrCategoryDetailList);die;

        $arrConditionCate = array(
            'cate_status' => 1,
            'cate_type' => 0
        );
        $arrCategory = $serviceCategory->getList($arrConditionCate);
        $arrCategoryDetailList = array();

        foreach ($arrCategory as $key => $value) {
            $arrCategoryDetailList[$value['cate_id']] = $value;
        }
        $arrConditionBrand = array(
            'cate_status' => 1,
            'cate_type' => 1
        );
        $arrBrandDetailList = array();
        $arrBrand = $serviceCategory->getList($arrConditionBrand);
        foreach ($arrBrand as $key => $value) {
            $arrBrandDetailList[$value['cate_id']] = $value;
        }
//        p($params);die;
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
//        p($paging);die;
        //$params = array_merge($params, $this->params()->fromQuery());
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductList' => $arrProductList,
            'arrCategoryDetailList' => $arrCategoryDetailList,
            'arrBrandDetailList' => $arrBrandDetailList,
        );
    }

    public function addAction() {

        $paramsRoute = $params = $this->params()->fromRoute();

        /*
         * get Category
         */
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrConditionCategory = array(
            'not_cate_status' => -1,
        );
        $arrCategoryList = $serviceCategory->getList($arrConditionCategory);

        /*
         * get Tags
         */
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $arrConditionTag = array(
            'not_tags_status' => -1
        );
        $arrTagList = $serviceTags->getList($arrConditionTag);
        
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['prod_name'])) {
                $errors['prod_name'] = 'Chưa nhập tên cho sản phẩm !';
            }

            if (empty($params['main_cate_id'])) {
                $errors['main_cate_id'] = 'Vui lòng chọn một danh mục';
            }

            if (empty($params['bran_id'])) {
                $errors['bran_id'] = 'Vui lòng chọn một thương hiệu';
            }

            if (empty($params['prod_price']) && $params['prod_price'] < 0) {
                $errors['prod_price'] = 'Vui lòng nhập giá sản phẩm';
            }

            if ((empty($params['prod_promotion_price']) || $params['prod_promotion_price'] < 1) && $params['prod_is_promotion'] == 1) {
                $errors['prod_promotion_price'] = 'Vui lòng nhập giá khuyến mãi';
            }

            if (empty($params['prod_description'])) {
                $errors['prod_description'] = 'Mô tả về sản phẩm không được bỏ trống !';
            }

            if (empty($params['prod_detail'])) {
                $errors['prod_detail'] = 'Nội dung về sản phẩm không được bỏ trống !';
            }


            foreach ($params['tags_name'] as $key => $value) {
                $tags = $value . ',';
            }

            $prop_id = "";
            foreach ($params['prop_id'] as $key => $value) {
                if (trim($value) != "") {
                    if (count($params['prop_id']) == $key + 1) {
                        $prop_id.= $value;
                    } else {
                        $prop_id.= $value . ',';
                    }
                }
            }

            $cate_id = "";
            foreach ($params['cate_id'] as $key => $value) {
                if (trim($value) != "") {
                    if (count($params['cate_id']) == $key + 1) {
                        $cate_id .= $value;
                    } else {
                        $cate_id .= $value . ',';
                    }
                }
            }

            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $prodSlug = ($params['prod_slug'] != "") ? General::getSlug($params['prod_slug']) : General::getSlug($params['prod_name']);
            $isNotExist = $serviceProduct->getTotal(array('prod_slug' => $prodSlug, 'not_prod_status' => -1));

            if ($isNotExist > 0) {
                $errors['prod_name'] = 'Sản phẩm đã tồn tại trong hệ thống !';
            }

            if (empty($params['prod_image'])) {
                $errors['prod_image'] = 'Vui lòng chọn hình chính cho sản phẩm !';
            }

            if (empty($params['prod_image_sub'])) {
                $errors['prod_image_sub'] = 'Vui lòng chọn hình slide cho sản phẩm !';
            }

            if (!empty($params['prod_code'])) {
                $isNotExist = $serviceProduct->getTotal(array('prod_code' => $params['prod_code'], 'not_prod_status' => -1));
                if ($isNotExist > 0) {
                    $errors['prod_code'] = 'Mã sản phẩm đã tồn tại trong hệ thống';
                }
            }


            $tags = "";
            if (!empty($params['tags_name'])) {
                $expTag = explode(",", $params['tags_name']);
                if (count($expTag) > 0) {
                    foreach ($expTag as $key => $value) {
                        if (!empty($value)) {
                            $isExist = $serviceTags->getDetail(array('tags_name' => $value, 'not_tags_status' => -1));
                            if (empty($isExist)) {
                                $arrDataTags = array(
                                    'tags_name' => trim($value),
                                    'tags_slug' => General::getSlug(trim($value)),
                                    'tags_order' => 0,
                                    'tags_created' => time(),
                                    'tags_status' => 1,
                                    'user_created' => UID,
                                    'tags_status' => 1,
                                    'tags_parent' => 0,
                                );
                                $id = $serviceTags->add($arrDataTags);
                                $serviceTags->edit(array('tags_grade' => $id . ':'), $id);
                                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                                $arrLogs = array(
                                    'user_id' => UID,
                                    'logs_controller' => 'Product',
                                    'logs_action' => 'add',
                                    'logs_time' => time(),
                                    'logs_detail' => 'Thêm tags mới id = ' . $id,
                                );
                                $serviceLogs->add($arrLogs);

                                $tags.=$id . ",";
                            } else {
                                $tags.=$isExist["tags_id"] . ",";
                            }
                        }
                    }
                    $tags = substr($tags, 0, -1);
                }
            }

            if (empty($errors)) {
                $arrData = array(
                    'prod_name' => htmlentities($params['prod_name']),
                    'prod_slug' => $prodSlug,
                    'prod_price' => $params['prod_price'],
                    'prod_detail' => \My\Minifier\HtmlMin::minify($params['prod_detail']),
                    'cate_id' => implode(',', $params['Category']),
                    'user_created' => UID,
                    'prop_id' => $prop_id,
                    'tags_id' => $tags,
                    'prod_call_price' => ($params['prod_price'] == 0 || empty($params['prod_price'])) ? 1 : 0, //$params['prod_call_price']
                    'prod_is_promotion' => empty($params['prod_is_promotion']) ? 0 : $params['prod_is_promotion'],
                    'bran_id' => $params['bran_id'],
                    'main_cate_id' => $params['main_cate_id'],
                    'cate_id' => $cate_id,
                    'prod_promotion_price' => $params['prod_promotion_price'],
                    'prod_description' => \My\Minifier\HtmlMin::minify($params['prod_description']),
                    'prod_bestselling' => empty($params['prod_bestselling']) ? 0 : $params['prod_bestselling'],
                    'prod_meta_title' => htmlentities($params['prod_meta_title']),
                    'prod_meta_keyword' => htmlentities($params['prod_meta_keyword']),
                    'prod_meta_description' => ($params['prod_meta_description']),
                    'prod_meta_robot' => ($params['prod_meta_robot']),
                    'prod_image' => $params['prod_image'],
                    'prod_image_sub' => json_encode($params['prod_image_sub']),
                    'prod_status' => $params['prod_status'],
                    'prod_actived' => $params['prod_actived'],
                    'prod_created' => time(),
                    'prod_type' => $params['prod_type']
                );
                $intResult = $serviceProduct->add($arrData);
                $detailProduct = $serviceProduct->getDetail(array('prod_id' => $intResult));
                $detailProduct['prod_name_like'] = $detailProduct['prod_name'];
                $arrDocument[] = new \Elastica\Document($intResult, $detailProduct);
                $instanceProduct = new \My\Search\Products();
                $result = $instanceProduct->add($arrDocument);
                if ($result > 0) {
                    $prodCode = empty($params['prod_code']) ? $intResult : $params['prod_code'];
                    $serviceProduct->edit(['prod_code' => $prodCode], $intResult);

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Product',
                        'logs_action' => 'add',
                        'logs_time' => time(),
                        'logs_detail' => 'Thêm sản phẩm mới id = ' . $intResult,
                    );
                    $serviceLogs->add($arrLogs);
                    $this->flashMessenger()->setNamespace('success-add-product')->addMessage('Thêm sản phẩm thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'add'));
                    }
                }
                $errors[] = 'Không thể thêm dữ liệu. Hoặc sản phẩm đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'arrCategoryList' => $arrCategoryList,
            'arrTagList' => $arrTagList
        );
    }

    public function editAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'index'));
        }
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrProduct = $serviceProduct->getDetail(array('prod_id' => $params['id'], 'prod_actived' => 1));
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCategoryList = $serviceCategory->getList(array('cate_status' => 1, 'or_cate_type' => array(1, 0)));
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $arrTagList = $serviceTags->getList(array('in_tags_id' => !empty($arrProduct["tags_id"]) ? $arrProduct["tags_id"] : "0"));

        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $strProperty = "";
        foreach ($arrCategoryList as $val) {
            if ($val["cate_id"] == $arrProduct["main_cate_id"]) {
                $strProperty = $val["prop_id"];
            }
        }
        $arrProperties = $serviceProperties->getList(array("listAllPropertiesID" => $strProperty, "prop_not_status" => -1));
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();
            $prodCode = $params['prod_code'];
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['prod_name'])) {
                $errors['prod_name'] = 'Chưa nhập tên cho sản phẩm !';
            }


            if (empty($params['main_cate_id'])) {
                $errors['main_cate_id'] = 'Vui lòng chọn một danh mục';
            }

            if (empty($params['bran_id'])) {
                $errors['bran_id'] = 'Vui lòng chọn một thương hiệu';
            }

            if (empty($params['prod_price']) && $params['prod_price'] < 0) {
                $errors['prod_price'] = 'Vui lòng nhập giá sản phẩm';
            }

            if ((empty($params['prod_promotion_price']) || $params['prod_promotion_price'] < 1) && $params['prod_is_promotion'] == 1) {
                $errors['prod_promotion_price'] = 'Vui lòng nhập giá khuyến mãi';
            }

            if (empty($params['prod_description'])) {
                $errors['prod_description'] = 'Mô tả về sản phẩm không được bỏ trống !';
            }

            if (empty($params['prod_detail'])) {
                $errors['prod_detail'] = 'Nội dung về sản phẩm không được bỏ trống !';
            }


            foreach ($params['tags_name'] as $key => $value) {
                $tags = $value . ',';
            }

            $prop_id = "";
            foreach ($params['prop_id'] as $key => $value) {
                if (trim($value) != "") {
                    if (count($params['prop_id']) == $key + 1) {
                        $prop_id.= $value;
                    } else {
                        $prop_id.= $value . ',';
                    }
                }
            }

            $cate_id = "";
            foreach ($params['cate_id'] as $key => $value) {
                if (trim($value) != "") {
                    if (count($params['cate_id']) == $key + 1) {
                        $cate_id .= $value;
                    } else {
                        $cate_id .= $value . ',';
                    }
                }
            }

            $prodSlug = ($params['prod_slug'] != "") ? General::getSlug($params['prod_slug']) : General::getSlug($params['prod_name']);
            $isNotExist = $serviceProduct->getTotal(array('prod_slug' => $prodSlug, 'not_prod_id' => $arrProduct['prod_id'], 'not_prod_status' => -1));
            if ($isNotExist > 0) {
                $errors['prod_name'] = 'Sản phẩm đã tồn tại trong hệ thống!';
            }

            if (empty($params['prod_image'])) {
                $errors['prod_image'] = 'Vui lòng chọn hình chính cho sản phẩm !';
            }

            if (empty($params['prod_image_sub'])) {
                $errors['prod_image_sub'] = 'Vui lòng chọn hình slide cho sản phẩm !';
            }

            if (empty($params['prod_code'])) {
                $errors['prod_code'] = 'Vui lòng nhập mã sản phẩm';
            }

            $isNotExist = $serviceProduct->getTotal(array('prod_code' => $params['prod_code'], "not_prod_id" => $arrProduct['prod_id'], 'not_prod_status' => -1));
            if ($isNotExist > 0) {
                $errors['prod_code'] = 'Mã sản phẩm đã tồn tại trong hệ thống';
            }

            $tags = "";
            if (!empty($params['tags_name'])) {
                $expTag = explode(",", $params['tags_name']);
                if (count($expTag) > 0) {
                    foreach ($expTag as $key => $value) {
                        if (!empty($value)) {
                            $isExist = $serviceTags->getDetail(array('tags_name' => $value, 'not_tags_status' => -1));
                            if (empty($isExist)) {
                                $id = $serviceTags->add(array('tags_name' => trim($value), 'tags_slug' => General::getSlug(trim($value)), 'tags_status' => 1, 'tags_created' => time(), 'user_created' => UID, 'tags_created' => time()));
                                $serviceTags->edit(array('tags_grade' => $id . ':'), $id);
                                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                                $arrLogs = array(
                                    'user_id' => UID,
                                    'logs_controller' => 'Product',
                                    'logs_action' => 'edit',
                                    'logs_time' => time(),
                                    'logs_detail' => 'Thêm tags mới có id = ' . $id,
                                );
                                $serviceLogs->add($arrLogs);

                                $tags.=$id . ",";
                            } else {
                                $tags.=$isExist["tags_id"] . ",";
                            }
                        }
                    }
                    $tags = substr($tags, 0, -1);
                }
            }

            //  echo $tags;
            //    die();
            //update product database
            if (empty($errors)) {
                $arrData = array(
                    'prod_name' => htmlentities($params['prod_name']),
                    'prod_slug' => $prodSlug,
                    'prod_code' => $prodCode,
                    'prod_price' => $params['prod_price'],
                    'prod_detail' => \My\Minifier\HtmlMin::minify($params['prod_detail']),
                    'cate_id' => implode(',', $params['Category']),
                    'user_updated' => UID,
                    'prop_id' => $prop_id,
                    'tags_id' => $tags,
                    'prod_call_price' => ($params['prod_price'] == 0 || empty($params['prod_price'])) ? 1 : 0, //$params['prod_call_price']
                    'prod_is_promotion' => empty($params['prod_is_promotion']) ? 0 : $params['prod_is_promotion'],
                    'bran_id' => $params['bran_id'],
                    'main_cate_id' => $params['main_cate_id'],
                    'cate_id' => $cate_id,
                    'prod_promotion_price' => $params['prod_promotion_price'],
                    'prod_description' => \My\Minifier\HtmlMin::minify($params['prod_description']),
                    'prod_bestselling' => empty($params['prod_bestselling']) ? 0 : $params['prod_bestselling'],
                    'prod_meta_title' => htmlentities($params['prod_meta_title']),
                    'prod_meta_keyword' => htmlentities($params['prod_meta_keyword']),
                    'prod_meta_description' => ($params['prod_meta_description']),
                    'prod_meta_robot' => ($params['prod_meta_robot']),
                    'prod_image' => $params['prod_image'],
                    'prod_image_sub' => json_encode($params['prod_image_sub']),
                    'prod_actived' => $params['prod_actived'],
                    'prod_status' => $params['prod_status'],
                    'prod_updated' => time(),
                    'prod_type' => $params['prod_type']
                );
                $intResult = $serviceProduct->edit($arrData, $arrProduct['prod_id']);
                $detailProduct = $serviceProduct->getDetail(array('prod_id' => $arrProduct['prod_id']));
                $detailProduct['prod_name_like'] = $detailProduct['prod_name'];
                $arrDocument[] = new \Elastica\Document($arrProduct['prod_id'], $detailProduct);
                $instanceProduct = new \My\Search\Products();
                $result = $instanceProduct->add($arrDocument);
                if ($intResult) {

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Product',
                        'logs_action' => 'edit',
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa sản phẩm có id = ' . $arrProduct['prod_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-product')->addMessage('Chỉnh sửa sản phẩm thành công');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'edit', 'id' => $arrProduct['prod_id']));
                    }
                    //return $this->redirect()->toRoute('backend', array('controller' => 'product', 'action' => 'index'));
                }
                $errors[] = 'Không thể cập nhật dữ liệu. Hoặc tên sản phẩm đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }

        //   print_r($arrTagList);
        return array(
            'arrProduct' => $arrProduct,
            'params' => $params,
            'errors' => $errors,
            'arrCategoryList' => $arrCategoryList,
            'arrProperties' => $arrProperties,
            'arrTagList' => $arrTagList
        );
    }

    public function deleteAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if (empty($params['productID'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $arrData = array('prod_actived' => -1, 'user_updated' => UID, 'prod_updated' => time());
            if (is_array($params['productID'])) {
                if (count($params['productID']) > 0) {
                    foreach ($params['productID'] as $value) {
                        $intResult = $serviceProduct->edit($arrData, $value);
                    }
                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Product',
                        'logs_action' => 'delete',
                        'logs_time' => time(),
                        'logs_detail' => 'Xóa ' . count($params['productID']) . ' sản phẩm cùng lúc có id là ' . json_encode($params['productID']),
                    );
                    $serviceLogs->add($arrLogs);
                }
            } else {
                $intResult = $serviceProduct->edit($arrData, $params['productID']);
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Product',
                    'logs_action' => 'delete',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa sản phẩm có id = ' . json_encode($params['productID']),
                );
                $serviceLogs->add($arrLogs);
            }
            $instanceProduct = new \My\Search\Products();
            $result = $instanceProduct->delete($params['productID']);

            if ($intResult) {
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Sản phẩm hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function getPropertyAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $serviceCategory = $this->serviceLocator->get('My\Models\Category');
            $result = $serviceCategory->getDetail(array("cate_id" => $params["cate_id"]));
            $strData = "";
            $arrData = array();
            $strProperty = $result["prop_id"];
            $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
            $arrListProperty = $serviceProperties->getList(array("listAllPropertiesID" => $strProperty, "prop_not_status" => -1));
            foreach ($arrListProperty as $key => $value) {
                if ($value["prop_parent"] == 0) {
                    $arrData[$value["prop_id"]] = $value;
                } else {
                    $arrData[$value["prop_parent"]]["parent"][] = $value;
                }
            }

            foreach ($arrData as $key => $value) {
                $strData.= '<div class="col-lg-4"><div class="row"><label class="col-lg-12 control-label text-center">' . $value["prop_name"] . '</label><div class="col-lg-12"> <select name="prop_id[]" class="sumo-select-box" id="select-box" data-placeholder="Chọn một danh mục" >';
                if (!empty($value["parent"])) {
                    $strData.= '<option   value=""> Chọn ' . $value["prop_name"] . '</option>';
                    foreach ($value["parent"] as $k => $val) {
                        $strData.= '<option   value="' . $val["prop_id"] . '">' . $val["prop_name"] . '</option>';
                    }
                }
                $strData.= '</select></div></div></div>';
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'msg' => 'Lấy dữ liệu thành công', 'data' => $strData)));
        }
    }

    public function editAjaxAction() {
        $params = $this->params()->fromPost();
        if (empty($params)) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Vui lòng nhập đầy đủ thông tin ! ')));
        }

        if (!isset($params['newPrice'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Vui lòng Nhập giá cho sản phẩm ! ')));
        }
        if (empty($params['productID'])) {
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !! ')));
        }
        $call_price = ($params['newPrice'] == 0) ? 1 : 0;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrData = array(
            'prod_price' => (int) $params['newPrice'],
            'prod_call_price' => $call_price,
            'prod_updated' => time(),
            'user_updated' => UID,
        );
        $inResult = $serviceProduct->edit($arrData, $params['productID']);
        $detailProduct = $serviceProduct->getDetail(array('prod_id' => $params['productID']));
        $arrDocument[] = new \Elastica\Document($params['productID'], $detailProduct);
        $instanceProduct = new \My\Search\Products();
        $result = $instanceProduct->add($arrDocument);
        if ($inResult) {
            $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
            $arrLogs = array(
                'user_id' => UID,
                'logs_controller' => 'Product',
                'logs_action' => 'editAjax',
                'logs_time' => time(),
                'logs_detail' => 'Cập nhật giá cho sản phẩm có id = ' . $params['productID'],
            );
            $serviceLogs->add($arrLogs);
            if ($params['newPrice'] == 0) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Cập nhật giá sản phẩm thành công !', 'data' => 'Giá liên hệ')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Cập nhật giá sản phẩm thành công !', 'data' => number_format($params['newPrice'], 2, ",", "."))));
        }
        return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý ! Vui lòng thử lại !! ')));
    }

    public function getDetailAction() {

        // $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
//             p($params['prodID']);die();
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            if (!isset($params['prodCODE'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại!')));
            }
            $arrCondition = array();
            if (isset($params['prodCODE'])) {
                $arrCondition += array('prod_code' => $params['prodCODE']);
            }
            $arrDetailProduct = $serviceProduct->getDetail($arrCondition);
            if (!empty($arrDetailProduct)) {
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'arrDetailProduct' => $arrDetailProduct)));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => 0)));
        }
    }

}
