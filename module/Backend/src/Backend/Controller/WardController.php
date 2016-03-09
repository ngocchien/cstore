<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class WardController extends MyController {

    public function __construct() {
        $this->defaultJS = [
            'backend:ward:index' => 'jquery.validate.min.js',
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('ward_status' => 0);
        $intLimit = 15;
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $arrCityList = $serviceCity->getList($arrCondition);
        $serviceDistrict = $this->serviceLocator->get('My\Models\District');
        $arrDistrictList = $serviceCity->getList($arrCondition);
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if ($params['district_id'] && $params['city_id']) {
                $serviceWard = $this->serviceLocator->get('My\Models\Ward');
                $wardName = trim($params['wardName']);
                //Validate district name
                $arrData = array(
                    'ward_name' => $wardName,
                    'dist_id' => (int) $params['district_id'],
                    'city_id' => (int) $params['city_id'],
                    'ward_status' => 0,
                );
                $item = $serviceWard->getDetail($arrData);
                if (count($item) > 0) {
                    $errors[] = 'Dữ liệu đã tồn tại, Xin vui lòng kiểm tra lại';
                } else {
                    $arrData['ward_slug'] = General::getSlug($wardName);
                    $arrData['ward_ordering'] = (int) $params['ordering'];
                    $arrData['ward_is_focus'] = (int) $params['isFocus'];
                    $intResult = $serviceWard->add($arrData);
                    if ($intResult) {
                        $this->flashMessenger()->setNamespace('success-add-ward')->addMessage('Thêm Phường/ Xã mới thành công');
                        $this->redirect()->toRoute('backend', array('controller' => 'ward', 'action' => 'index', 'id' => $params['city_id']));
                    } else {
                        $errors[] = 'Không thể thêm dữ liệu, xin vui lòng kiểm tra lại';
                    }
                }
            }
        }

        return array(
            'params' => $params,
            //'page' => $intPage,
            //'limit' => $intLimit,
            'arrCityList' => $arrCityList,
            'arrDistrictList' => $arrDistrictList,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
        );
    }

    public function editAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $intWardID = (int) $this->params()->fromRoute('id', 0);
            $strWardName = trim($params['wardName']);
            if (empty($intWardID)) {
                return $this->redirect()->toRoute('backend', array('controller' => 'ward', 'action' => 'index'));
            }
            if (empty($strWardName)) {
                $errors['wardName'] = 'Vui lòng nhập tên Quận / Huyện.';
            }
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($strWardName, 'tbl_wards', 'ward_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
            if (empty($isNotExist)) {
                $errors['wardName'] = 'Quận / Huyện đã tồn tại trong hệ thống.';
            }
            if (empty($errors)) {
                $arrData = array(
                    'ward_name' => $strWardName,
                    'ward_slug' => General::getSlug($strWardName),
                    'ward_ordering' => (int) trim($params['ordering']),
                    'ward_is_focus' => (int) trim($params['isFocus']),
                );
                $serviceWard = $this->serviceLocator->get('My\Models\Ward');
                $intResult = $serviceWard->edit($arrData, $intWardID);
                print_r($intResult);
                die;
                if ($intResult) {
                    $this->flashMessenger()->setNamespace('success-edit-ward')->addMessage('Chỉnh sửa Phường / Xã thành công');
                    return $this->redirect()->toRoute('backend', array('controller' => 'ward', 'action' => 'index'));
                }
            }
        }
        return $this->redirect()->toRoute('backend', array('controller' => 'ward', 'action' => 'index'));
    }

    public function getWardAction() {
        $data = array('error' => 1,
            'msg' => 'Hiện tại chua có Phường / Xã nào'
        );
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            //print_r($params);die;
            $arrCondition = array('ward_status' => 0);
            $intLimit = 15;
            if ($params['district_id'] > 0) {
                $arrCondition['dist_id'] = $params['district_id'];
                $intPage = $this->params()->fromRoute('page', 1);
                $serviceWard = $this->serviceLocator->get('My\Models\Ward');
                $arrWardList = $serviceWard->getListLimit($arrCondition, $intPage, $intLimit, 'ward_is_focus DESC, ward_ordering ASC');
                //print_r($arrWardList);die;
                $intTotal = $serviceWard->getTotal($arrCondition);
                if (count($arrWardList) > 0) {
                    $data = array('error' => 0,
                        'msg' => 'success',
                        'total' => $intTotal,
                        'data' => $arrWardList
                    );
                }
            }
        }
        return $this->getResponse()->setContent(json_encode($data));
    }

    public function getListAction() {
        $arrWardList = array();
        $intDistrictID = $this->params()->fromPost('districtID', 0);
        if ($intDistrictID) {
            $arrData = array('dist_id' => $intDistrictID, 'is_deleted' => 0);
            $serviceWard = $this->serviceLocator->get('My\Models\Ward');
            $arrWardList = $serviceWard->getList($arrData);
        }
        if ($this->request->isPost()) {
            return $this->getResponse()->setContent(json_encode($arrWardList));
        }
        return $arrWardList;
    }

    public function deleteAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $intWardID = (int) $params['wardID'];

            if (empty($intWardID)) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceWard = $this->serviceLocator->get('My\Models\Ward');
            $serviceWard->edit(array('is_deleted' => 1), $intWardID);
            return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Quận / Huyện hoàn tất')));
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

    public function getListWardProshipAction() {
        $intDistrictID = $this->params()->fromPost('districtID', 0);
        $arrData = \My\Shipping\Proship::getWardList($intDistrictID);
        return $this->getResponse()->setContent(json_encode($arrData));
    }

}
