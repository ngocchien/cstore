<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class CategoryController extends MyController {
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProperties \My\Models\Properties */

    public function __construct() {

        $this->defaultJS = [
            'backend:category:add' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:category:edit' => 'jquery-ui-1.9.2.custom.min.js,jquery.sumoselect.min.js,bootstrap-fileupload.js,jquery.tagsinput.js,bootstrap-select.js',
            'backend:category:product' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js',
        ];
        $this->defaultCSS = [
            'backend:category:add' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css,style.css',
            'backend:category:edit' => 'sumoselect.css,bootstrap-fileupload.css,bootstrap-select.css,style.css',
            'backend:category:product' => 'sumoselect.css,bootstrap-fileupload.css',
        ];

        $this->externalJS = [
            'backend:category:index' => array(
                STATIC_URL . '/b/js/my/??category.js'
            ),
            'backend:category:add' => array(
                STATIC_URL . '/b/js/my/??category.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
            ),
            'backend:category:edit' => array(
                STATIC_URL . '/b/js/my/??category.js,addProduct_Cate.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',
            )
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('not_cate_status' => -1, 'cate_type' => 0, 'cate_name_like' => General::clean(trim($this->params()->fromQuery('s'))));
        $intLimit = $this->params()->fromRoute('limit', 30);
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $intTotal = $serviceCategory->getTotal($arrCondition);
        $arrCategoryList = $serviceCategory->getListLimit($arrCondition, $intPage, $intLimit, 'cate_grade ASC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());
        return array(
            'params' => $params,
            'arrCategoryList' => $arrCategoryList,
            'paging' => $paging
        );
    }

    public function addAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrCondition = array('not_cate_status' => -1, 'cate_type' => 0);
        $arrCategoryList = $serviceCategory->getList($arrCondition);
        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $arrPropertiesList = $serviceProperties->getList(array('prop_parent' => 0, 'prop_status' => 1));
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['CategoryName'])) {
                $errors['CategoryName'] = 'Tên Danh mục không được để trống !';
            }

            $strProperties = (empty($params['Properties'])) ? NULL : implode(',', $params['Properties']);
            $cateSlug = (empty($params['CateSlug'])) ? General::getSlug(trim($params['CategoryName'])) : General::getSlug(trim($params['CateSlug']));
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($cateSlug, 'tbl_categorys', 'cate_slug', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), array('field' => 'cate_status', 'value' => '-1'));

            if (empty($isNotExist)) {
                $errors['CategoryName'] = 'Danh mục này đã tồn tại !';
            }
            if (empty($errors)) {
                $arrData = array(
                    'cate_name' => trim(strip_tags($params['CategoryName'])),
                    'cate_slug' => $cateSlug,
                    'cate_parent' => trim($params['parentID']),
                    'prop_id' => $strProperties,
                    'cate_type' => 0,
                    'cate_order' => $params['CateOrder'],
                    'cate_description' => trim($params['cate_description']),
                    'cate_meta_title' => trim(strip_tags($params['CateMetaTitle'])),
                    'cate_meta_keyword' => trim(strip_tags($params['CateMetaKeyword'])),
                    'cate_meta_description' => trim(strip_tags($params['CateMetaDescription'])),
                    'cate_meta_social' => trim(strip_tags($params['CateMetaSocial'])),
                    'user_created' => UID,
                    'cate_created' => time(),
                    'cate_sort' => $params['CateOrder']
                );
                $intResult = $serviceCategory->add($arrData);
                if ($intResult > 0) {
                    if ($params['parentID'] > 0) {
                        if (empty($params['parentID'])) {
                            $params['parentID'] = 0;
                        }
                        $detailParent = $serviceCategory->getDetail(array('cate_id' => $params['parentID']));
                        $dataUpdate = array(
                            'cate_grade' => $detailParent['cate_grade'] . sprintf("%04d", $params['CateOrder']) . ':' . sprintf("%04d", $intResult) . ':',
                            'cate_status' => $detailParent['cate_status']
                        );
                        $serviceCategory->edit($dataUpdate, $intResult);
                    }
                    if ($params['parentID'] == 0) {
                        $dataUpdate = array(
                            'cate_grade' => sprintf("%04d", $params['CateOrder']) . ':' . sprintf("%04d", $intResult) . ':',
                            'cate_status' => $params['cate_status'],
                        );
                        $serviceCategory->edit($dataUpdate, $intResult);
                    }

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => $paramsRoute['__CONTROLLER__'],
                        'logs_action' => $paramsRoute['action'],
                        'logs_time' => time(),
                        'logs_detail' => 'Thêm Danh mục có id = ' . $intResult,
                    );
                    $serviceLogs->add($arrLogs);
                    $this->flashMessenger()->setNamespace('success-add-category')->addMessage('Thêm danh mục thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'add'));
                    }
                }
                $errors[] = 'Không thể thêm dữ liệu. Hoặc danh mục này đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'errors' => $errors,
            'arrCategoryList' => $arrCategoryList,
            'params' => $params,
            'arrPropertiesList' => $arrPropertiesList
        );
    }

    public function editAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
        }
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $detailCategory = $serviceCategory->getDetail(array('cate_id' => $params['id']));
        $arrCondition = array('not_cate_status' => -1, 'cate_type' => 0, 'cate_grade' => $detailCategory['cate_grade']);
        $arrCategoryList = $serviceCategory->getListUnlike($arrCondition);
        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $arrPropertiesList = $serviceProperties->getList(array('prop_parent' => 0, 'prop_status' => 1));
        $arrListPropertiesinCategory = explode(',', $detailCategory['prop_id']);

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['CategoryName'])) {
                $errors['CategoryName'] = 'Tên danh mục không được để trống !';
            }
            $strProperties = (empty($params['Properties'])) ? NULL : implode(',', $params['Properties']);

            $cateSlug = (empty($params['CateSlug'])) ? General::getSlug(trim($params['CategoryName'])) : General::getSlug(trim($params['CateSlug']));
            $isNotExist = $serviceCategory->getTotal(array('cate_slug' => $cateSlug, 'not_cate_id' => $detailCategory['cate_id'], 'not_cate_status' => -1));

            if ($isNotExist > 0) {
                $errors['cate_name'] = 'Danh mục này đã tồn tại trong hệ thống!';
            }
            $intParent = empty($params['parentID']) ? 0 : (int) $params['parentID'];
            $intOrder = empty($params['CateOrder']) ? 0 : (int) $params['CateOrder'];
            if (empty($errors)) {
                $arrData = array(
                    'cate_name' => trim($params['CategoryName']),
                    'cate_slug' => $cateSlug,
                    'cate_order' => $intOrder,
                    'cate_parent' => $intParent,
                    'prop_id' => $strProperties,
                    'cate_description' => trim($params['cate_description']),
                    'cate_meta_title' => trim(strip_tags($params['CateMetaTitle'])),
                    'cate_meta_keyword' => trim(strip_tags($params['CateMetaKeyword'])),
                    'cate_meta_description' => trim(strip_tags($params['CateMetaDescription'])),
                    'cate_meta_social' => trim(strip_tags($params['CateMetaSocial'])),
                    'user_updated' => UID,
                    'cate_status' => $params['cate_status'],
                    'cate_updated' => time(),
                );

                $intResult = $serviceCategory->edit($arrData, $detailCategory['cate_id']);

                if ($intResult) {
                    if ($detailCategory['cate_parent'] != $intParent || $detailCategory['cate_order'] != $intOrder) {
                        $detailParent = $serviceCategory->getDetail(array('cate_id' => $intParent));
                        if (!empty($detailParent)) {
                            $dataUpdate = array(
                                'cate_grade' => $detailCategory['cate_grade'],
                                'grade_update' => $detailParent['cate_grade'] . sprintf("%04d", $params['CateOrder']) . ':' . sprintf("%04d", $detailCategory['cate_id']) . ':',
                                'cate_status' => $detailParent['cate_status'],
                                'parentID' => $params['parentID'],
                            );
                        } else {
                            $dataUpdate = array(
                                'cate_grade' => $detailCategory['cate_grade'],
                                'grade_update' => sprintf("%04d", $intOrder) . ':' . sprintf("%04d", $detailCategory['cate_id']) . ':',
                                'cate_status' => $params['cate_status'],
                                'parentID' => $params['parentID'],
                            );
                        }

                        $serviceCategory->updateTree($dataUpdate);
                    }

                    if ($detailCategory['cate_status'] != $params['cate_status']) {
                        $dataUpdate = array(
                            'cate_status' => $params['cate_status'],
                            'grade_update' => $detailCategory['cate_grade'],
                        );
                        $serviceCategory->updateStatusTree($dataUpdate);
                    }

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => $paramsRoute['__CONTROLLER__'],
                        'logs_action' => $paramsRoute['action'],
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa Danh mục có id = ' . $detailCategory['cate_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-category')->addMessage('Chỉnh sửa Danh mục thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'edit', 'id' => $detailCategory['cate_id']));
                    }
                    //$this->redirect()->toRoute('backend', array('controller' => 'category', 'action' => 'index'));
                }
                $errors[] = 'Không thể chỉnh sửa dữ liệu. Hoặc tên Danh mục đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'errors' => $errors,
            'params' => $params,
            'paging' => $paging,
            'detailCategory' => $detailCategory,
            'arrCategoryList' => $arrCategoryList,
            'arrPropertiesList' => $arrPropertiesList,
            'arrListPropertiesinCategory' => $arrListPropertiesinCategory,
        );
    }

    public function deleteAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();

            if (empty($params['categoryID'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceCategory = $this->serviceLocator->get('My\Models\Category');
            $arrCateDetail = $serviceCategory->getDetail(array('cate_id' => $params['categoryID']));

            $arrCondition = array('cate_status' => 1, 'cate_type' => 0, 'categrade' => $arrCateDetail['cate_grade']);
            $arrCategoryList = $serviceCategory->getListUnlike($arrCondition);

            if (count($arrCategoryList) > 1) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không xóa được! Danh mục này có nhiều danh mục con! Vui lòng xóa các danh mục con trước !')));
            }

            $arrData = array('cate_status' => -1, 'user_updated' => UID, 'cate_updated' => time());

            $intResult = $serviceCategory->edit($arrData, $params['categoryID']);

            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => $paramsRoute['__CONTROLLER__'],
                    'logs_action' => $paramsRoute['action'],
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Danh mục có id = ' . $params['categoryID'],
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Danh mục hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
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
