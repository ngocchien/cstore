<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController,
    My\Validator\Validate;

class TagsController extends MyController {
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        $this->externalJS = array(
            STATIC_URL . '/b/js/my/tags.js',
            STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js'
        );
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromPost(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array(
            'not_tags_status' => -1,
            'tags_name_like' => General::clean(trim($this->params()->fromQuery('s'))));
        $intLimit = $this->params()->fromRoute('limit', 30);
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $intTotal = $serviceTags->getTotal($arrCondition);
        $arrTagsList = $serviceTags->getListLimit($arrCondition, $intPage, $intLimit, 'tags_order ASC');
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
        $arrParamsRoute = $arrParams = $this->params()->fromRoute();
        if ($this->request->isPost()) {

            $arrParams = $this->params()->fromPost();

            $serviceTags = $this->serviceLocator->get('My\Models\Tags');
            $validation = new \Backend\Validate\Tags($arrParams, $serviceTags);

            if (!$validation->isError()) {
                $arrData = $validation->getData();

                $intResult = $serviceTags->add($arrData);
                if ($intResult) {
                    $arrLogs = General::createLogs($arrParamsRoute, $arrData, $intResult);
                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-add-tags')->addMessage('Thêm tags thành công!');
                    return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
                }
                $errors['tags'] = 'Xảy ra lỗi trong qua trình xử lý, Vui lòng thử lại sau giây lát!';
            }
            $errors = $validation->getMessageError();
        }

        return array(
            'errors' => $errors,
            'arrParams' => $arrParams,
        );
    }

    public function editAction() {
        $arrParamsRoute = $arrParams = $this->params()->fromRoute();

        if (empty($arrParams['id'])) {
            return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
        }

        $intTagsId = (int) $arrParams['id'];
        $serviceTags = $this->serviceLocator->get('My\Models\Tags');
        $arrCondition = array(
            'tags_id' => $intTagsId,
            'not_tags_status' => -1
        );
        $detailTags = $serviceTags->getDetail($arrCondition);

        if (empty($detailTags)) {
            return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
        }

        if ($this->request->isPost()) {
            $arrParams = $this->params()->fromPost();
            $arrParams['tags_id'] = $intTagsId;
            $validation = new \Backend\Validate\Tags($arrParams, $serviceTags);

            if (!$validation->isError()) {
                $arrData = $validation->getData();
                $intResult = $serviceTags->edit($arrData, $intTagsId);
                if ($intResult) {
                    /*
                     * Save Logs
                     */
                    $arrLogs = General::createLogs($arrParamsRoute, $arrData, $intTagsId);
                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-tags')->addMessage('Chỉnh sửa tags thành công!');
                    return $this->redirect()->toRoute('backend', array('controller' => 'tags', 'action' => 'index'));
                }
                $errors['tags'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
            }
            $errors = $validation->getMessageError();
        }
        return array(
            'arrParams' => $arrParams,
            'errors' => $errors,
            'detailTags' => $detailTags,
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
        $arrParamsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $arrParams = $this->params()->fromPost();

            if (empty($arrParams['TagsID'])) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $intTagId = (int) $arrParams['TagsID'];

            /*
             *  Kiểm tra có tồn tại tag với id truyền với trong DB hay không
             */

            $serviceTags = $this->serviceLocator->get('My\Models\Tags');
            $arrCondition = array(
                'not_tags_status' => -1,
                'tags_id' => $intTagId
            );
            $arrTags = $serviceTags->getDetail($arrCondition);

            if (empty($arrTags)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Không tìm thấy thông tin Tag này trong hệ thống !')));
            }

            $arrData = array(
                'tags_status' => -1,
                'user_updated' => UID,
                'tags_updated' => time()
            );

            $intResult = $serviceTags->edit($arrData, $intTagId);

            if ($intResult) {

                /*
                 * Write to Logs
                 */

                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = General::createLogs($arrParamsRoute, $arrData, $intTagId);
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('st' => 1, 'ms' => 'Xóa Tags hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

    public function getTagAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $tagName = trim($params['tagName']);
            $strTagsId = $params['listTagsId'];

            if (empty($tagName)) {
                return $this->getResponse()->setContent(json_encode(array('st' => -1, 'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại !')));
            }

            $arrCondition = array(
                'like_tags_name' => $tagName,
                'not_in_tags_id' => $strTagsId,
                'not_tags_status' => -1
            );

            $serviceTags = $this->serviceLocator->get('My\Models\Tags');
            $arrTagsList = $serviceTags->getList($arrCondition);

            $arrReturn = array(
                'st' => 1,
                'data' => $arrTagsList
            );

            return $this->getResponse()->setContent(json_encode($arrReturn));
        }
    }

}
