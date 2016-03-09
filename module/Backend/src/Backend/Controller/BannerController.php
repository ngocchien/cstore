<?php

namespace Backend\Controller;

use My\General,
    My\Controller\MyController,
    My\Validator\Validate;

class BannerController extends MyController {
    /* @var $serviceTags \My\Models\Tags */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        $this->defaultJS = [
            //'backend:product:index'=>
            'backend:banner:add' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:banner:edit' => 'jquery.sumoselect.min.js,bootstrap-fileupload.js,bootstrap-select.js',
            'backend:banner:index' => 'jquery.sumoselect.min.js,bootstrap-select.js',
        ];
        $this->externalJS = [
            'backend:banner:edit' => array(
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js', 
                STATIC_URL . '/b/js/my/banner.js', 
                STATIC_URL . '/b/js/library/Nileupload-min.js'
            ),
            'backend:banner:index' => array(
                STATIC_URL . '/b/js/my/banner.js', 
            ),
            'backend:banner:add' => array(
                STATIC_URL . '/b/js/library/tinymce/??tinymce.min.js', 
                STATIC_URL . '/b/js/my/banner.js', 
                STATIC_URL . '/b/js/library/Nileupload-min.js'
            ),
        ];
        $this->defaultCSS = [
            'backend:banner:add' => 'sumoselect.css,bootstrap-select.css',
            'backend:banner:edit' => 'sumoselect.css,bootstrap-select.css',
            'backend:banner:index' => 'sumoselect.css,bootstrap-select.css',
        ];
    }

    public function indexAction() {
        $params = array_merge($this->params()->fromQuery(), $this->params()->fromRoute());
        $intPage = $this->params()->fromRoute('page', 1);
        $intLimit = 15;
        $serviceBanner = $this->serviceLocator->get('My\Models\Banners');
        if (is_array($params['ban_cate_id'])) {
            $params['ban_cate_id'] = implode(',', $params['ban_cate_id']);
        }
        if (is_array($params['ban_location'])) {
            $params['ban_location'] = implode(',', $params['ban_location']);
        }
        $arrCondition = array(
            'is_delete' => 0,
            's' => General::clean($params['s']),
            'list_ban_cate_id' => $params['ban_cate_id'],
            'list_ban_location' => $params['ban_location'],
        );
        $intTotal = $serviceBanner->getTotal($arrCondition);
        $listBanner = $serviceBanner->getListLimit($arrCondition, $intPage, $intLimit);
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', $params);

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrConditionCate = array(
            'cate_status' => 1,
            'cate_type' => 0
        );
        $listCategory = $serviceCategory->getList($arrConditionCate);
        foreach ($listCategory as $category) {
            $arrCategory[$category['cate_id']] = $category;
        }
        return array(
            'params' => $params,
            'listBanner' => $listBanner,
            'arrCategory' => $arrCategory,
            'paging' => $paging,
        );
    }

    public function addAction() {
        $params = $this->params()->fromRoute();
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $arrConditionCate = array(
            'cate_status' => 1,
            'cate_type' => 0
        );
        $listCategory = $serviceCategory->getList($arrConditionCate);
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {

                $errors = array();
                $validator = new Validate();

                $strBan_image = trim($params['ban_image']);
                $strBan_html = trim($params['ban_html']);
                if (!$strBan_html && $strBan_image) {
                    $errors[] = 'Nội hình ảnh banner không được trống';
                }
                $ban_location = $params['ban_location'];
                if (count($ban_location) < 1) {
                    $errors[] = 'Chưa chọn vị trí cho banner';
                }
                $params['ban_location'] = implode(',', $params['ban_location']);
                $params['ban_cate_id'] = implode(',', $params['ban_cate_id']);
                if (empty($errors)) {
                    $arrData = array(
                        'ban_title' => trim($params['ban_title']),
//                        'ban_url' => trim($params['ban_url']),
                        'ban_location' => $params['ban_location'],
                        'ban_status' => $params['ban_status'],
                        'ban_description' => trim($params['ban_description']),
                        'ban_image' => trim($params['ban_image']),
                        'ban_html' => $params['ban_html'],
                        'ban_cate_id' => $params['ban_cate_id'],
                        'is_delete' => 0,
                    );
                    $serviceBanner = $this->serviceLocator->get('My\Models\Banners');
                    $intResult = $serviceBanner->add($arrData);
                    if ($intResult) {
                        $this->flashMessenger()->setNamespace('success-add-banner')->addMessage('Thêm banner thành công !');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'banner', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'banner', 'action' => 'add'));
                        }
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'arrayCategory' => $listCategory
        );
    }

    public function editAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'banner', 'action' => 'index'));
        }
        $id = $params['id'];
        $serviceBanner = $this->serviceLocator->get('My\Models\Banners');
        $params = $serviceBanner->getDetail(array('ban_id' => $params['id']));

        $serviceCategory = $this->serviceLocator->get('My\Models\Category');

        $arrConditionCate = array(
            'cate_status' => 1,
            'cate_type' => 0
        );
        $listCategory = $serviceCategory->getList($arrConditionCate);
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if ($params && is_array($params)) {

                $errors = array();
                $validator = new Validate();

                $strBan_image = trim($params['ban_image']);
                $strBan_html = trim($params['ban_html']);
                if (!$strBan_html && $strBan_image) {
                    $errors[] = 'Nội dung HTML không được trống';
                }

                $ban_location = $params['ban_location'];
                if (count($ban_location) < 1) {
                    $errors[] = 'Chưa chọn vị trí cho banner';
                }
                $params['ban_location'] = implode(',', $params['ban_location']);
                $params['ban_cate_id'] = implode(',', $params['ban_cate_id']);
                if (empty($errors)) {
                    $arrData = array(
                        'ban_title' => trim($params['ban_title']),
                        'ban_location' => $params['ban_location'],
                        'ban_status' => $params['ban_status'],
                        'ban_description' => trim($params['ban_description']),
                        'ban_image' => trim($params['ban_image']),
                        'ban_html' => $params['ban_html'],
                        'ban_cate_id' => $params['ban_cate_id'],
                        'is_delete' => 0,
                    );

                    $intResult = $serviceBanner->edit($arrData, $id);

                    if ($intResult >= 0) {
                        $this->flashMessenger()->setNamespace('success-edit-banner')->addMessage('Chỉnh sửa banner thành công !');
                        if ($params['is_close'] == 1) {
                            $this->redirect()->toRoute('backend', array('controller' => 'banner', 'action' => 'index'));
                        } else {
                            $this->redirect()->toRoute('backend', array('controller' => 'banner', 'action' => 'edit', 'id' => $id));
                        }
                    }
                }
            }
        }
        return array(
            'params' => $params,
            'errors' => $errors,
            'arrayCategory' => $listCategory
        );
    }

    public function deleteAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params['ban_id'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceTags = $this->serviceLocator->get('My\Models\Banners');
            $arrData = array('is_delete' => 1);
            if (is_array($params['ban_id'])) {
                foreach ($params['ban_id'] as $id) {
                    $intResult = $serviceTags->edit($arrData, $id);
                }
            } else {
                $intResult = $serviceTags->edit($arrData, $params['ban_id']);
            }
            if ($intResult) {

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa banner hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

}
