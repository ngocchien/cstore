<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class PropertiesController extends MyController {
    /* @var $serviceProperties \My\Models\Properties */

    public function __construct() {
        $this->externalJS = [
            'backend:properties:index' => array(
                STATIC_URL . '/b/js/my/??properties.js'
            ),
            'backend:properties:add' => array(
                STATIC_URL . '/b/js/my/??properties.js'
            ),
            'backend:properties:edit' => array(
                STATIC_URL . '/b/js/my/??properties.js'
            )
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('prop_status' => 1, 'prop_name_like' => General::clean(trim($this->params()->fromQuery('s'))));
        $intLimit = $this->params()->fromRoute('limit', 15);
        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $intTotal = $serviceProperties->getTotal($arrCondition);
        $arrPropertiesList = $serviceProperties->getListLimit($arrCondition, $intPage, $intLimit, 'prop_grade ASC');

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        $params = array_merge($params, $this->params()->fromQuery());
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrPropertiesList' => $arrPropertiesList,
        );
    }

    public function addAction() {
        $params = $this->params()->fromRoute();
        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $arrCondition = array('prop_status' => 1);
        $arrPropertiesList = $serviceProperties->getList($arrCondition);
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['PropertiesName'])) {
                $errors['PropertiesName'] = 'Tên thuộc tính không được để trống !';
            }
            $validator = new Validate();

            $isNotExist = $validator->noRecordExists($params['PropertiesName'], 'tbl_properties', 'prop_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
            if (empty($isNotExist)) {
                $errors['PropertiesName'] = 'Thuộc tính này đã tồn tại !';
            }
            if (empty($errors)) {
                $arrData = array(
                    'prop_name' => $params['PropertiesName'],
                    'prop_slug' => General::getSlug($params['PropertiesName']),
                    'prop_parent' => $params['parentID'],
                    'user_created' => UID,
                    'prop_created' => time()
                );

                $intResult = $serviceProperties->add($arrData);
                if ($intResult > 0) {
                    if ($params['parentID'] > 0) {
                        $detailParent = $serviceProperties->getDetail(array('prop_id' => $params['parentID']));
                        $dataUpdate = array(
                            'prop_grade' => $detailParent['prop_grade'] . $intResult . ':',
                        );
                        $serviceProperties->edit($dataUpdate, $intResult);
                    }
                    if ($params['parentID'] == 0) {
                        $dataUpdate = array(
                            'prop_grade' => $intResult . ':',
                        );
                        $serviceProperties->edit($dataUpdate, $intResult);
                    }

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Properties',
                        'logs_action' => 'add',
                        'logs_time' => time(),
                        'logs_detail' => 'Thêm thuộc tính có id = ' . $intResult,
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-add-properties')->addMessage('Thêm thuộc tính sản phẩm thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'add'));
                    }
                    // $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'add'));
                }
                $errors[] = 'Không thể thêm dữ liệu. Hoặc thuộc tính này đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }
        return array(
            'errors' => $errors,
            'arrPropertiesList' => $arrPropertiesList,
            'params' => $params
        );
    }

    public function editAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'index'));
        }

        $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
        $detailProperties = $serviceProperties->getDetail(array('prop_id' => $params['id']));
        $arrCondition = array('prop_status' => 1, 'prop_grade' => $detailProperties['prop_id']);
        $arrPropertiesList = $serviceProperties->getListUnlike($arrCondition);
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            if (empty($params)) {
                $errors[] = 'Vui lòng nhập đầy đủ các thông tin !';
            }

            if (empty($params['PropertiesName'])) {
                $errors['PropertiesName'] = 'Tên thuộc tính không được để trống !';
            }
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($params['PropertiesName'], 'tbl_properties', 'prop_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), array('field' => 'prop_id', 'value' => $detailProperties['prop_id']));
            if (empty($isNotExist)) {
                $errors['PropertiesName'] = 'Thuộc tính này đã tồn tại trong hệ thống.';
            }
            if (empty($errors)) {
                $arrData = array(
                    'prop_name' => $params['PropertiesName'],
                    'prop_slug' => General::getSlug($params['PropertiesName']),
                    'prop_parent' => $params['parentID'],
                    'user_updated' => UID,
                    'prop_updated' => time()
                );
                $intResult = $serviceProperties->edit($arrData, $detailProperties['prop_id']);
                if ($intResult) {
                    if ($detailProperties['prop_parent'] != $params['parentID']) {
                        $detailParent = $serviceProperties->getDetail(array('prop_id' => $params['parentID']));
                        $dataUpdate = array('prop_id' => $detailProperties['prop_id'],
                            'prop_grade' => $detailProperties['prop_grade'],
                            'parentID' => $params['parentID'],
                            'parentGrade' => $detailParent['prop_grade']);
                        $serviceProperties->updateTree($dataUpdate);
                    }

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'Properties',
                        'logs_action' => 'edit',
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa thuộc tính có id = ' . $detailProperties['prop_id'],
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-properties')->addMessage('Chỉnh sửa thuộc tính sản phẩm thành công !');
                    if ($params['is_close'] == 1) {
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'index'));
                    } else {
                        $this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'edit', 'id' => $detailProperties['prop_id']));
                    }
                    //$this->redirect()->toRoute('backend', array('controller' => 'properties', 'action' => 'index'));
                }
                $errors[] = 'Không thể chỉnh sửa dữ liệu. Hoặc tên thuộc tính đã tồn tại. Xin vui lòng kiểm tra lại';
            }
        }


        return array(
            'errors' => $errors,
            'detailProperties' => $detailProperties,
            'arrPropertiesList' => $arrPropertiesList
        );
    }

    public function deleteAction() {
        $params = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if (empty($params['propertiesID'])) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceProperties = $this->serviceLocator->get('My\Models\Properties');
            $arrCondition = array('prop_status' => 1, 'propgrade' => $params['propertiesID']);
            $arrPropertiesList = $serviceProperties->getListUnlike($arrCondition);
            if (count($arrPropertiesList) > 1) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Không xóa được! Thuộc tính này có nhiều thuộc tính con! Vui lòng xóa các thuộc tính con trước !')));
            }
            $arrData = array('prop_status' => -1, 'user_updated' => UID, 'prop_updated', 'user_updated' => UID,);
            $intResult = $serviceProperties->edit($arrData, $params['propertiesID']);
            if ($intResult) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'Properties',
                    'logs_action' => 'delete',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa thuộc tính có id = ' . $params['propertiesID'],
                );
                $serviceLogs->add($arrLogs);

                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa thuộc tính hoàn tất')));
            }
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
        }
    }

}
