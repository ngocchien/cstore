<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class BrandController extends MyController {
    /* @var $serviceBrand \My\Models\Brand */

    public function __construct() {

        $this->defaultJS = [
            'backend:brand:add' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:brand:edit' => 'jquery-ui-1.9.2.custom.min.js,jquery.sumoselect.min.js,bootstrap-fileupload.js,jquery.tagsinput.js,bootstrap-select.js',
            'backend:brand:product' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js',
        ];
        $this->defaultCSS = [
            'backend:brand:add' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css,style.css',
            'backend:brand:edit' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css,style.css',
        ];

        $this->externalJS = [
            'backend:brand:index' => array(
                STATIC_URL . '/b/js/my/??brand.js'
            ),
            'backend:brand:add' => array(
                STATIC_URL . '/b/js/my/??brand.js',
            ),
            'backend:brand:edit' => array(
                STATIC_URL . '/b/js/my/??brand.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
            )
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);

        $arrCondition = array(
            'not_bran_status' => -1,
            'bran_name_like' => General::clean(trim($this->params()->fromQuery('s')))
        );

        $intLimit = $this->params()->fromRoute('limit', 30);
        $serviceBrand = $this->serviceLocator->get('My\Models\Brand');
        $intTotal = $serviceBrand->getTotal($arrCondition);
        $arrBrandList = $serviceBrand->getListLimit($arrCondition, $intPage, $intLimit, 'bran_sort ASC');

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());

        return array(
            'params' => $params,
            'arrBrandList' => $arrBrandList,
            'paging' => $paging
        );
    }

    public function addAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $errors = array();

            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['brandName'])) {
                $errors['brandName'] = 'Tên Danh mục không được để trống !';
            }

            $strBrandName = trim($params['brandName']);

            if (empty($errors)) {

                $brandSlug = (empty($params['brandSlug'])) ? General::getSlug($strBrandName) : General::getSlug(trim($params['brandSlug']));

                /*
                 * Kiểm tra đã tồn tại hay chưa
                 */

                $serviceBrand = $this->serviceLocator->get('My\Models\Brand');
                $arrCondition = array(
                    'bran_slug' => $brandSlug,
                    'not_bran_status' => -1
                );
                $arrBrandExist = $serviceBrand->getDetail($arrCondition);

                if (!empty($arrBrandExist)) {
                    $errors['brandName'] = 'Danh mục này đã tồn tại !';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'bran_name' => $strBrandName,
                        'bran_slug' => $brandSlug,
                        'bran_sort' => (int) $params['brandSort'],
                        'bran_description' => trim($params['brandDescription']),
                        'bran_meta_title' => trim(strip_tags($params['brandMetaTitle'])),
                        'bran_meta_keyword' => trim(strip_tags($params['brandMetaKeyword'])),
                        'bran_meta_description' => trim(strip_tags($params['brandMetaDescription'])),
                        'bran_meta_social' => trim(strip_tags($params['brandMetaSocial'])),
                        'bran_status' => (int) $params['brandStatus'],
                        'user_created' => UID,
                        'bran_created' => time(),
                    );
                    $intResult = $serviceBrand->add($arrData);
                    if ($intResult > 0) {

                        /*
                         * Write to Logs
                         */

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Thêm Thương hiệu có id = ' . $intResult,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-add-category')->addMessage('Thêm danh mục thành công !');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'add'));
                        }
                    }
                    $errors[] = 'Không thể thêm dữ liệu. Hoặc danh mục này đã tồn tại. Xin vui lòng kiểm tra lại';
                }
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
        );
    }

    public function editAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'index'));
        }
        $intBrandId = (int) $params['id'];
        $serviceBrand = $this->serviceLocator->get('My\Models\Brand');
        $arrCondition = array(
            'not_bran_status' => -1,
            'bran_id' => $intBrandId
        );

        $arrBrand = $serviceBrand->getDetail($arrCondition);

        if (empty($arrBrand)) {
            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'index'));
        }

        $errors = [];

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['brandName'])) {
                $errors['brandName'] = 'Tên thương hiệu không được để trống !';
            }

            $strBrandName = trim($params['brandName']);

            if (empty($errors)) {
                $strBrandSlug = (empty($params['brandSlug'])) ? General::getSlug($strBrandName) : General::getSlug(trim($params['brandSlug']));

                /*
                 * Kiểm tra trùng lặp trước khi lưu
                 */

                $arrConditionExist = array(
                    'not_bran_staus' => -1,
                    'not_bran_id' => $intBrandId,
                    'bran_slug' => $strBrandSlug
                );

                $arrBrandExist = $serviceBrand->getDetail($arrConditionExist);

                if (!empty($arrBrandExist)) {
                    $errors['brandName'] = 'Tên thương hiệu này đã tồn tại trong hệ thống !';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'bran_name' => $strBrandName,
                        'bran_slug' => $strBrandSlug,
                        'bran_sort' => (int) $params['brandSort'],
                        'bran_description' => trim($params['brandDescription']),
                        'bran_meta_title' => trim(strip_tags($params['brandMetaTitle'])),
                        'bran_meta_keyword' => trim(strip_tags($params['brandMetaKeyword'])),
                        'bran_meta_description' => trim(strip_tags($params['brandMetaDescription'])),
                        'bran_meta_social' => trim(strip_tags($params['bradMetaSocial'])),
                        'user_updated' => UID,
                        'bran_status' => (int) $params['brandStatus'],
                        'bran_updated' => time(),
                    );

                    $intResult = $serviceBrand->edit($arrData, $intBrandId);

                    if ($intResult) {

                        /*
                         * Write to Logs
                         */
                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => $paramsRoute['__CONTROLLER__'],
                            'logs_action' => $paramsRoute['action'],
                            'logs_time' => time(),
                            'logs_detail' => 'Chỉnh sửa Thương hiệu có id = ' . $intBrandId,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-edit-brand')->addMessage('Chỉnh sửa Danh mục thành công !');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'brand', 'action' => 'edit', 'id' => $intBrandId));
                        }
                    }
                    $errors[] = 'Không thể chỉnh sửa dữ liệu. Hoặc tên thương hiệu đã tồn tại. Xin vui lòng kiểm tra lại';
                }
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
            'arrBrand' => $arrBrand
        );
    }

    public function deleteAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            $intBrandId = (int) $params['brandID'];

            if (!$intBrandId) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $arrCondition = array(
                'not_bran_status' => -1,
                'bran_id' => $intBrandId
            );
            $serviceBrand = $this->serviceLocator->get('My\Models\Brand');
            $arrBrand = $serviceBrand->getDetail($arrCondition);

            if (empty($arrBrand)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không tìm thấy thông tin thương hiệu trong hệ thống !')));
            }

            $arrData = array(
                'bran_status' => -1,
                'user_updated' => UID,
                'bran_updated' => time()
            );

            $intResult = $serviceBrand->edit($arrData, $intBrandId);

            if ($intResult) {

                /*
                 * Write to Logs
                 */

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => $paramsRoute['__CONTROLLER__'],
                    'logs_action' => $paramsRoute['action'],
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Thương hiệu có id = ' . $intBrandId,
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa Danh mục hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function selectProductAction() {
        $params = $this->params()->fromQuery();
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');

        $arrProductList = $serviceProduct->getListLimit(array('prod_name_like' => trim($params['term']), 'not_cate_id' => $params['id'], 'not_main_cate_id' => $params['id'], 'prod_actived' => 1), 1, 7, 'prod_viewer DESC');
        $prod_name = array();
        foreach ($arrProductList as $val) {
            $prod_name[$val['prod_id']] = $val['prod_name'];
        }

        if ($arrProductList)
            return $this->getResponse()->setContent(json_encode($prod_name));
    }

    public function productAction() {
        $this->layout('layout/empty');  //disable layout
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());
        //p($params);die;
        if (empty($params['id_cate'])) {
            return $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
        }
        if (!empty($params['ord'])) {
            $serviceSort = $this->serviceLocator->get('My\Models\Sort');
            $arrListProd = explode(',', $params['listProd']);
            $arrAdd = $params['ord'];
            $listSort = $serviceSort->getList(array('sort_cate' => $params['id_cate']));
            if (!empty($listSort)) {
                foreach ($listSort as $sort) {
                    if (in_array($sort['sort_product'], $arrListProd)) {
                        if ($params['ord'][$sort['sort_product']] != 0)
                            $intResult = $serviceSort->edit(array('sort_ordering' => $params['ord'][$sort['sort_product']]), $sort['sort_id']);
                        else
                            $intResult = $serviceSort->delete(array('sort_cate' => $params['id_cate'], 'sort_product' => $sort['sort_product']));
                        // $arrUse[$sort['sort_product']] = $params['ord'][$sort['sort_product']];
                    }
                    unset($arrAdd[$sort['sort_product']]);
                }
            }

            foreach ($arrAdd as $key => $value) {
                if ($value != 0) {
                    $arrData = array(
                        'sort_cate' => $params['id_cate'],
                        'sort_product' => $key,
                        'sort_ordering' => $value
                    );
                    $intResult = $serviceSort->add($arrData);
                }
            }
            return $this->getResponse()->setContent(json_encode(array('success' => 1)));
        }
        $sort = 'sort.sort_ordering DESC';
        if (!empty($params['sort'])) {
            $sort = $params['sort'];
        }
        $intPage = $this->params()->fromPost('page', 1);
        $intLimit = 15;
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCateD = $serviceCategory->getDetail(array('cate_id' => $params['id_cate']));
        $arrCategoryListChild = $serviceCategory->getList(array('cate_status' => 1, 'cate_type' => 0, 'categrade' => $arrCateD['cate_grade']));
        $listCateChild = '';
        foreach ($arrCategoryListChild as $val) {
            $listCateChild .= (int) $val['cate_id'] . ",";   // => ex : 'a','b',
        }
        $listCateChild = rtrim($listCateChild, ',');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrayConditionProd = array(
            'cate_id_or_main_cate_id' => $listCateChild,
            'prod_actived' => 1,
            'prod_name_like' => General::clean(trim($params['s'])),
        );
        $arrayConditionSort = array(
            'sort_cate' => $params['id_cate']
        );
        $arrProductList = $serviceProduct->getListLimitJoinSort($arrayConditionProd, $arrayConditionSort, $intPage, $intLimit, $sort);
        $intTotal = $serviceProduct->getTotalProdJoinSort($arrayConditionProd, $arrayConditionSort);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', $params);

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductList' => $arrProductList,
        );
    }

    public function addProductAction() {
        $params = $this->params()->fromPost();
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $id_prod = $params['id_prod'];
        $id_cate = $params['id_cate'];
        $tmp = 0;

        $arrProductList = $serviceProduct->getList(array('listProductID' => $id_prod, 'prod_actived' => 1));
        if ($arrProductList) {
            foreach ($arrProductList as $val) {
                $cate_id = $val['cate_id'] . ',' . $id_cate;
                $result = $serviceProduct->edit(array('cate_id' => trim($cate_id, ','), 'prod_updated' => time()), $val['prod_id']);
                if (result)
                    $tmp++;
            }
            if ($tmp == count($arrProductList))
                return $this->getResponse()->setContent(json_encode(array('success' => 1, 'message' => 'Thêm sản phẩm thành công')));
        }

        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'message' => 'Thêm sản phẩm thất bại')));
    }

    public function deleteProductAction() {
        $params = $this->params()->fromPost();
        if (empty($params['id_prod']) || empty($params['id_cate'])) {
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'message' => 'Xóa sản phẩm thất bại')));
        }
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetailList = $serviceProduct->getList(array('listProductID' => $params['id_prod']));
        foreach ($arrDetailList as $val) {
            if ($val['main_cate_id'] == $params['id_cate']) {
                $val['main_cate_id'] = '';
            }

            $cate_id = explode(',', $val['cate_id']);
            foreach ($cate_id as $key => $value) {
                if ($value == $params['id_cate']) {
                    unset($cate_id[$key]);
                    $val['cate_id'] = implode(',', $cate_id);
                    break;
                }
            }
            $result = $serviceProduct->edit(array('cate_id' => $val['cate_id'], 'main_cate_id' => $val['main_cate_id'], 'prod_updated' => time()), $val['prod_id']);
        }

        if ($result) {
            return $this->getResponse()->setContent(json_encode(array('success' => 1, 'message' => 'Xóa sản phẩm thành công')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'message' => 'Xóa sản phẩm thất bại')));
    }

}
