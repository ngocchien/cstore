<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class DistrictController extends MyController {
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */

    public function __construct() {
        $this->defaultJS = [
            'backend:district:index' => 'jquery.validate.min.js',
        ];
        $this->externalJS = [
            'backend:district:index' => STATIC_URL . '/b/js/my/??district.js'
        ];
    }

    public function indexAction() {
        $params = $this->params()->fromRoute();
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('city_status' => 0);
        $intLimit = 15;
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $arrCityList = $serviceCity->getList($arrCondition);
        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            if ($params['city_id'] > 0) {
                $serviceDistrict = $this->serviceLocator->get('My\Models\District');
                $districtName = trim($params['districtName']);
                //Validate district name
                $arrData = array(
                    'dist_name' => $districtName,
                    'city_id' => (int) $params['city_id'],
                    'dist_status' => 0,
                );
                $item = $serviceDistrict->getDetail($arrData);
                if (count($item) > 0) {
                    $errors[] = 'Dữ liệu đã tồn tại, Xin vui lòng kiểm tra lại';
                } else {
                    $arrData['dist_slug'] = General::getSlug($districtName);
                    $arrData['dist_ordering'] = (int) $params['ordering'];
                    $arrData['dist_is_focus'] = (int) $params['isFocus'];
                    $arrData['dist_rural'] = (int) $params['isRural'];
                    $intResult = $serviceDistrict->add($arrData);
                    if ($intResult) {

                        $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                        $arrLogs = array(
                            'user_id' => UID,
                            'logs_controller' => 'District',
                            'logs_action' => 'index',
                            'logs_time' => time(),
                            'logs_detail' => 'Thêm Quận/Huyện có id = ' . $intResult,
                        );
                        $serviceLogs->add($arrLogs);

                        $this->flashMessenger()->setNamespace('success-add-district')->addMessage('Thêm Quận/ Huyện mới thành công');
                        $this->redirect()->toRoute('backend', array('controller' => 'district', 'action' => 'index', 'id' => $params['city_id']));
                    } else {
                        $errors[] = 'Không thể thêm dữ liệu, xin vui lòng kiểm tra lại';
                    }
                }
            }
        }
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'city', 'action' => 'index', 'page' => $intPage));
        return array(
            'params' => $params,
            'page' => $intPage,
            'limit' => $intLimit,
            'arrCityList' => $arrCityList,
            'message' => $this->flashMessenger()->getMessages(),
            'errors' => $errors,
        );
    }

    public function editAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $intDistrictID = (int) $this->params()->fromRoute('id', 0);
            $strDistrictName = trim($params['districtName']);

            if (empty($intDistrictID)) {
                return $this->redirect()->toRoute('backend', array('controller' => 'district', 'action' => 'index'));
            }
            if (empty($strDistrictName)) {
                $errors['districtName'] = 'Vui lòng nhập tên Quận / Huyện.';
            }
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($strDistrictName, 'tbl_districts', 'dist_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), array('field' => 'dist_id', 'value' => $intDistrictID));
            if (empty($isNotExist)) {
                $errors['districtName'] = 'Quận / Huyện đã tồn tại trong hệ thống.';
            }
            if (empty($errors)) {
                $arrData = array(
                    'dist_name' => $strDistrictName,
                    'dist_slug' => General::getSlug($strDistrictName),
                    'dist_rural' => (int) $params['isRural'],
                    'dist_ordering' => (int) trim($params['ordering']),
                    'dist_is_focus' => (int) trim($params['isFocus']),
                );

                $serviceDistrict = $this->serviceLocator->get('My\Models\District');
                $intResult = $serviceDistrict->edit($arrData, $intDistrictID);
                if ($intResult) {
                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => 'District',
                        'logs_action' => 'edit',
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa Quận/Huyện có id = ' . $intDistrictID,
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-district')->addMessage('Chỉnh sửa Quận / Huyện thành công');
                    return $this->redirect()->toRoute('backend', array('controller' => 'district', 'action' => 'index'));
                }
            }
        }
        return $this->redirect()->toRoute('backend', array('controller' => 'district', 'action' => 'index'));
    }

    public function getDistrictAction() {
        $data = array('error' => 1,
            'msg' => 'Hiện tại chưa có Quận / Huyện nào'
        );
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $arrCondition = array('dist_status' => 0);
            $intLimit = 150;
            if ($params['city_id'] > 0) {
                $arrCondition['city_id'] = $params['city_id'];
                $intPage = $this->params()->fromRoute('page', 1);
                $serviceDistrict = $this->serviceLocator->get('My\Models\District');
                $arrDistrictList = $serviceDistrict->getListLimit($arrCondition, $intPage, $intLimit);
                $intTotal = $serviceDistrict->getTotal($arrCondition);
                if (count($arrDistrictList) > 0) {
                    $data = array('error' => 0,
                        'msg' => 'success',
                        'total' => $intTotal,
                        'data' => $arrDistrictList
                    );
                }
            }
        }
        return $this->getResponse()->setContent(json_encode($data));
    }

    public function getListAction() {
        $arrDistrictList = array();
        $intCityID = $this->params()->fromPost('cityID', 0);
        if ($intCityID) {
            $arrData = array('city_id' => $intCityID, 'is_deleted' => 0);
            $serviceDistrict = $this->serviceLocator->get('My\Models\District');
            $arrDistrictList = $serviceDistrict->getList($arrData);
        }
        if ($this->request->isPost()) {
            return $this->getResponse()->setContent(json_encode($arrDistrictList));
        }
        return $arrDistrictList;
    }

    public function deleteAction() {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $intDistrictID = (int) $params['districtID'];
            if (empty($intDistrictID)) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }
            $serviceDistrict = $this->serviceLocator->get('My\Models\District');
            $result = $serviceDistrict->edit(array('dist_status' => 1), $intDistrictID);
            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => 'District',
                    'logs_action' => 'edit',
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Quận/Huyện có id = ' . $intDistrictID,
                );
                $serviceLogs->add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Quận / Huyện hoàn tất')));
            }
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

    public function getListDistProShipAction() {
        $intCityID = $this->params()->fromPost('cityID', 0);
        $arrData = \My\Shipping\Proship::getDistricList($intCityID);
        return $this->getResponse()->setContent(json_encode($arrData));
    }
}
