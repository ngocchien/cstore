<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController,
    My\Validator\Validate;

class TagsController extends MyController {
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        $this->defaultJS = [
            'backend:tags:edit' => 'jquery.validate.min.js,jquery.sumoselect.min.js,bootstrap-select.js',
            'backend:tags:add' => 'jquery.validate.min.js,jquery.sumoselect.min.js,bootstrap-select.js',
        ];

        $this->defaultCSS = [
            'backend:tags:add' => 'sumoselect.css,bootstrap-select.css',
            'backend:tags:edit' => 'sumoselect.css,bootstrap-select.css',
        ];

        $this->externalJS = [
            'backend:tags:edit' => array(STATIC_URL . '/b/js/my/tags.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',),
            'backend:tags:index' => STATIC_URL . '/b/js/my/tags.js',
            'backend:tags:add' => array(STATIC_URL . '/b/js/my/tags.js',
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js',)
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromPost(), $this->params()->fromRoute());
        ;
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array(
            'not_tags_status' => -1,
            'tags_name_like' => General::clean(trim($this->params()->fromQuery('s'))));
        $intLimit = $this->params()->fromRoute('limit', 15);
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $intTotal = $serviceTags->getTotal($arrCondition);
        $arrTagsList = $serviceTags->getListLimit($arrCondition, $intPage, $intLimit, 'tags_sort ASC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());
        return array(
            'params' => $params,
            'arrTagsList' => $arrTagsList,
            'paging' => $paging
        );
    }

    public function addAction() {
        $paramsRoute = $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $errors = array();

            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ thông tin !';
            }

            if (empty($params['TagsName'])) {
                $errors['TagsName'] = 'Tên Tags không được bỏ trống !';
            }

            if (empty($errors)) {
                $strTagsName = trim($params['TagsName']);
                $strTagsSlug = General::getSlug($strTagsName);

                $serviceTags = $this->serviceLocator->get('My\Models\Tags');
                $arrConditionTag = array(
                    'tags_slug' => $strTagsSlug,
                    'not_tags_status' => -1
                );

                $arrTag = $serviceTags->getDetail($arrConditionTag);

                if (!empty($arrTag)) {
                    $errors['TagsSlug'] = 'Tags này đã tồn tại trong hệ thống !';
                }

                if (empty($errors)) {
                    $arrData = array(
                        'tags_name' => $strTagsName,
                        'tags_slug' => $strTagsSlug,
                        'tags_order' => $params['TagsOrder'],
                        'tags_description' => trim($params['tags_description']),
                        'tags_meta_title' => trim($params['TagsMetaTitle']),
                        'tags_meta_keyword' => trim($params['TagsMetaKeyword']),
                        'tags_meta_description' => trim($params['TagsMetaDescription']),
                        'tags_meta_social' => trim($params['TagsMetaSocial']),
                        'user_created' => UID,
                        'tags_status' => $params['tags_status'],
                        'tags_created' => time(),
                        'tags_status' => $params['tags_status']
                    );
                    $intResult = $serviceTags->add($arrData);

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
                            'logs_detail' => 'Thêm Tags có id = ' . $intResult,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-add-tags')->addMessage('Thêm Tags sản phẩm thành công !');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'add'));
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
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
        }
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $detailTags = $serviceTags->getDetail(array('tags_id' => $params['id']));
        if (empty($detailTags)) {
            return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
        }
        $arrCondition = array('not_tags_status' => -1, 'tags_grade' => $detailTags['tags_id']);
        $arrTagsList = $serviceTags->getListUnlike($arrCondition);

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['TagsName'])) {
                $errors['TagsName'] = 'Tên Tags không được để trống !';
            }

            $strTagsSlug = (empty($params['TagsSlug'])) ? General::getSlug(trim($params['TagsName'])) : General::getSlug(trim($params['TagsSlug']));
            $isNotExist = $serviceTags->getTotal(array('tags_slug' => $strTagsSlug, 'not_tags_id' => $detailTags['tags_id'], 'not_tags_status' => -1));

            if ($isNotExist > 0) {
                $errors['tags_name'] = 'Tags này đã tồn tại trong hệ thống!';
            }
            $strParent = empty($params['parentID']) ? 0 : $params['parentID'];
            if (empty($errors)) {
                $arrData = array(
                    'tags_name' => trim($params['TagsName']),
                    'tags_slug' => $strTagsSlug,
                    'tags_order' => $params['TagsOrder'],
                    'tags_parent' => $strParent,
                    'tags_description' => trim($params['tags_description']),
                    'tags_meta_title' => trim(strip_tags($params['TagsMetaTitle'])),
                    'tags_meta_keyword' => trim(strip_tags($params['TagsMetaKeyword'])),
                    'tags_meta_description' => trim(strip_tags($params['TagsMetaDescription'])),
                    'tags_meta_social' => trim(strip_tags($params['TagsMetaSocial'])),
                    'user_updated' => UID,
                    'tags_status' => $params['tags_status'],
                    'tags_position' => $params['tags_position'],
                    'tags_updated' => time()
                );
                $intResult = $serviceTags->edit($arrData, $detailTags['tags_id']);
                if ($intResult) {
                    if ($detailTags['tags_parent'] != $strParent || $detailTags['tags_order'] != $params['TagsOrder']) {
                        $detailParent = $serviceTags->getDetail(array('tags_id' => $strParent));
                        if (!empty($detailParent)) {
                            $dataUpdate = array(
                                'tags_id' => $detailTags['tags_id'],
                                'tags_grade' => $detailTags['tags_grade'],
                                'grade_update' => $detailParent['tags_grade'] . $detailTags['tags_id'] . ':',
                                'sort_update' => $detailParent['tags_sort'] . sprintf("%04d", $params['TagsOrder']) . '-' . $detailTags['tags_id'] . ':',
                                'tags_sort' => $detailTags['tags_sort'],
                                'tags_status' => $detailParent['tags_status'],
                                'parentID' => $params['parentID'],
                            );
                        } else {
                            $dataUpdate = array(
                                'tags_id' => $detailTags['tags_id'],
                                'tags_grade' => $detailTags['tags_grade'],
                                'grade_update' => $detailTags['tags_id'] . ':',
                                'sort_update' => sprintf("%04d", $params['TagsOrder']) . '-' . $detailTags['tags_id'] . ':',
                                'tags_sort' => $detailTags['tags_sort'],
                                'tags_status' => $params['tags_status'],
                                'parentID' => $params['parentID'],
                            );
                        }
                        $serviceTags->updateTree($dataUpdate);
                    }
                    if ($detailTags['tags_status'] != $params['tags_status']) {
                        $dataUpdate = array(
                            'tags_status' => $params['tags_status'],
                            'grade_update' => $detailTags['tags_grade'],
                        );
                        $serviceTags->updateStatusTree($dataUpdate);
                    }

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Tags',
                        'logs_action' => 'edit',
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa Tags có id = ' . $detailTags['tags_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-tags')->addMessage('Chỉnh sửa Tags thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'edit', 'id' => $detailTags['tags_id']));
                    }
                    // $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
                }
                $errors[] = 'Không thể chỉnh sửa dữ liệu. Hoặc tên Tags đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'detailTags' => $detailTags,
            'arrTagsList' => $arrTagsList,
        );
    }

    public function getproductAction() {
        $this->layout('layout/empty');  //disable layout
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrConditions = array(
            'tags_id_not' => $params['tags_id'],
            'prod_actived' => 1,
            'prod_name_like' => $params['search_name'],
        ); // not find_in_set
        $intTotal = $serviceProduct->getTotal($arrConditions);
        $arrProductList = $serviceProduct->getListLimit($arrConditions, $intPage, $intLimit, 'prod_updated DESC');

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'tags', 'action' => 'edit', 'page' => $intPage));

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductList' => $arrProductList,
        );
    }

    public function getproducttagsAction() {
        $this->layout('layout/empty');  //disable layout
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromPost());
        if (!empty($params['ord'])) {
            $serviceSort = $this->serviceLocator->get('My\Models\SortTags');
            $arrListProd = explode(',', $params['listProd']);
            $arrAdd = $params['ord'];
            $listSort = $serviceSort->getList(array('sort_tag' => $params['tags_id']));
            $arrUse = array();
            if (!empty($listSort)) {
                foreach ($listSort as $sort) {
                    if (in_array($sort['sort_product'], $arrListProd)) {

                        if ($params['ord'][$sort['sort_product']] != 0)
                            $intResult = $serviceSort->edit(array('sort_ordering' => $params['ord'][$sort['sort_product']]), $sort['sort_id']);
                        else
                            $intResult = $serviceSort->delete(array('sort_tag' => $params['tags_id'], 'sort_product' => $sort['sort_product']));
                        $arrUse[$sort['sort_product']] = $params['ord'][$sort['sort_product']];
                    }
                }
                $arrAdd = array_diff($params['ord'], $arrUse);
            }
            foreach ($arrAdd as $key => $value) {
                if ($value != 0) {
                    $arrData = array(
                        'sort_tag' => $params['tags_id'],
                        'sort_product' => $key,
                        'sort_ordering' => $value
                    );
                    $intResult = $serviceSort->add($arrData);
                }
            }
            return $this->getResponse()->setContent(json_encode(array('success' => 1)));
        }
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 10;
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');

        $arrConditions = array(
            'tags_id' => $params['tags_id'],
            'prod_status' => 1,
            'prod_name_like' => $params['search_name']
        );
        $arrConditionsSort = array('sort_tag' => $params['tags_id']);
        $intTotal = $serviceProduct->getTotal($arrConditions);
        $arrProducTagstList = $serviceProduct->getListLimitJoinSortTags($arrConditions, $arrConditionsSort, $intPage, $intLimit, 'sort.sort_ordering DESC');
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');   //phân trang ajax
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'tags', 'action' => 'edit', 'page' => $intPage));

        return array(
            'params' => $params,
            'paging' => $paging,
            'arrProductTagsList' => $arrProducTagstList,
        );
    }

    public function getindexAction() {
        $this->layout('layout/empty');
        $params = array_merge($this->params()->fromPost(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');

        $arrConditions = array('tags_name_like' => $params['search'], 'not_tags_status' => -1);

        $intTotal = $serviceTags->getTotal($arrConditions);
        $arrTagsList = $serviceTags->getListLimit($arrConditions, $intPage, $intLimit, 'tags_id DESC');

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Pagingajax');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'tags', 'action' => 'index', 'page' => $intPage));
        if ($arrTagsList)
            return array(
                'params' => $params,
                'paging' => $paging,
                'arrTagsList' => $arrTagsList,
            );
    }

    public function deleteProductAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params['tags_id']) || empty($params['prod_id'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $getDetail = $serviceProduct->getDetail(array('prod_id' => $params['prod_id']));
            //delete in table sort Tags
            $serviceSort = $this->serviceLocator->get('My\Models\SortTags');
            $intResult = $serviceSort->delete(array('sort_tag' => $params['tags_id'], 'sort_product' => $params['prod_id']));

            $arrTags = explode(',', $getDetail['tags_id']);     //xử lý cắt chuỗi 
            foreach ($arrTags as $key => $val) {
                if ($val == $params['tags_id']) {
                    unset($arrTags[$key]);
                    break;
                }
            }

            $strTags = implode(',', $arrTags);  //hợp array -> string

            $arrData = array('tags_id' => $strTags, 'prod_updated' => time(), 'user_updated' => UID);
            $intResult = $serviceProduct->edit($arrData, $params['prod_id']);

            if ($intResult) {

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Tags',
                    'logs_action' => 'delete',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa sản phẩm có id = ' . $params['prod_id'] . ' khỏi tags có id = ' . $params['tags_id'],
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Loại bỏ sản phẩm hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function addProductAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['tags_id']) || empty($params['prod_id'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $getDetail = $serviceProduct->getDetail(array('prod_id' => $params['prod_id']));

            $arrTags = explode(',', $getDetail['tags_id']);     //cắt chuỗi
            if (in_array($params['tags_id'], $arrTags)) {
                return;
            } else {
                array_push($arrTags, $params['tags_id']);     //xử lý add value vào chuỗi 
            }

            $strTags = ltrim(implode(',', $arrTags), ',');

            $arrData = array('tags_id' => $strTags, 'prod_updated' => time());
            $intResult = $serviceProduct->edit($arrData, $params['prod_id']);

            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Tags',
                    'logs_action' => 'addProduct',
                    'logs_time' => time(),
                    'logs_detail' => 'Thêm sản phẩm có id = ' . $params['prod_id'] . ' vào tags có id = ' . $getDetail['tags_id'],
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Thêm sản phẩm thành công')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function deleteAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['TagsID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceTags = $this->serviceLocator->get('My\Models\Tags');
            $arrCondition = array('tags_status' => 1, 'tagsgrade' => $params['TagsID']);
            $arrTagsList = $serviceTags->getListUnlike($arrCondition);
            if (count($arrTagsList) > 1) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không xóa được! Tags này có nhiều tags con! Vui lòng xóa các tags con trước !')));
            }
            $arrData = array('tags_status' => -1, 'user_updated' => UID, 'tags_updated' => time());
            $intResult = $serviceTags->edit($arrData, $params['TagsID']);
            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Tags',
                    'logs_action' => 'delete',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa tags có id = ' . $params['TagsID'],
                );
                $serviceLogs->add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa Tags hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

}
