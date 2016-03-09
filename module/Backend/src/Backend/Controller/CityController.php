<?php

namespace Backend\Controller;

use My\General,
    My\Validator\Validate,
    My\Controller\MyController;

class CityController extends MyController {
    /* @var $serviceCity \My\Models\City */

    public function __construct() {
        $this->defaultJS = [
            'backend:city:index' => 'jquery.validate.min.js',
        ];
        $this->externalJS = [
            'backend:city:index' => STATIC_URL . '/b/js/my/??city.js',
            'backend:city:add' => STATIC_URL . '/b/js/my/??city.js',
            'backend:city:edit' => STATIC_URL . '/b/js/my/??city.js'
        ];
    }

    public function indexAction() {
        $paramsRoute = $params = $this->params()->fromRoute();

        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('city_status' => 0);
        $intLimit = 15;
        $serviceCity = $this->serviceLocator->get('My\Models\City');
        $intTotal = $serviceCity->getTotal($arrCondition);
        $arrCityList = $serviceCity->getListLimit($arrCondition, $intPage, $intLimit, 'city_ordering ASC');

        if ($this->request->isPost()) {
            $errors = array();
            $params = $this->params()->fromPost();
            $strCityName = trim($params['cityName']);
            if (empty($strCityName)) {
                $errors['cityName'] = 'Vui lòng nhập tên Tỉnh / Thành.';
            }
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($strCityName, 'tbl_cities', 'city_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'));
            if (empty($isNotExist)) {
                $errors['cityName'] = 'Tỉnh / Thành đã tồn tại trong hệ thống.';
            }
            if (empty($errors)) {
                $arrData = array(
                    'city_name' => $strCityName,
                    'city_slug' => General::getSlug($strCityName),
                    'city_ordering' => (int) trim($params['ordering']),
                    'city_is_focus' => (int) trim($params['isFocus']),
                );
                $intResult = $serviceCity->add($arrData);
                if ($intResult) {

                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => $paramsRoute['__CONTROLLER__'],
                        'logs_action' => $paramsRoute['action'],
                        'logs_time' => time(),
                        'logs_detail' => 'Thêm Tỉnh/thành có id = ' . $intResult,
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-add-city')->addMessage('Thêm Tỉnh / Thành mới thành công');
                    return $this->redirect()->toRoute('backend', array('controller' => 'city', 'action' => 'index'));
                }
            }
        }
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, 'backend', array('controller' => 'city', 'action' => 'index', 'page' => $intPage));
        return array(
            'errors' => $errors,
            'params' => $params,
            'paging' => $paging,
            'arrCityList' => $arrCityList,
        );
    }

    public function editAction() {
        $paramsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            $intCityID = (int) $this->params()->fromRoute('id', 0);
            $strCityName = trim($params['cityName']);
            if (empty($intCityID)) {
                return $this->redirect()->toRoute('backend', array('controller' => 'city', 'action' => 'index'));
            }
            if (empty($strCityName)) {
                $errors['cityName'] = 'Vui lòng nhập tên Tỉnh / Thành.';
            }
            $validator = new Validate();
            $isNotExist = $validator->noRecordExists($strCityName, 'tbl_cities', 'city_name', $this->serviceLocator->get('Zend\Db\Adapter\Adapter'), array('field' => 'city_id', 'value' => $intCityID));
            if (empty($isNotExist)) {
                $errors['cityName'] = 'Tỉnh / Thành đã tồn tại trong hệ thống.';
            }

            if (empty($errors)) {
                $arrData = array(
                    'city_name' => $strCityName,
                    'city_slug' => General::getSlug($strCityName),
                    'city_ordering' => (int) trim($params['ordering']),
                    'city_is_focus' => (int) trim($params['isFocus']),
                );
                $serviceCity = $this->serviceLocator->get('My\Models\City');
                $intResult = $serviceCity->edit($arrData, $intCityID);
                if ($intResult) {
                    $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                    $arrLogs = array(
                        'user_id' => UID,
                        'logs_controller' => $paramsRoute['__CONTROLLER__'],
                        'logs_action' => $paramsRoute['action'],
                        'logs_time' => time(),
                        'logs_detail' => 'Chỉnh sửa Tỉnh/thành có id = ' . $intCityID,
                    );
                    $serviceLogs->add($arrLogs);

                    $this->flashMessenger()->setNamespace('success-edit-city')->addMessage('Chỉnh sửa Tỉnh / Thành thành công');
                    return $this->redirect()->toRoute('backend', array('controller' => 'city', 'action' => 'index'));
                }
            }
        }
        return $this->redirect()->toRoute('backend', array('controller' => 'city', 'action' => 'index'));
    }

    public function deleteAction() {
        $paramsRoute = $this->params()->fromRoute();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $intCityID = (int) $params['cityID'];

            if (empty($intCityID)) {
                return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
            }

            $serviceCity = $this->serviceLocator->get('My\Models\City');
            $result = $serviceCity->edit(array('city_status' => 1), $intCityID);

            if ($result) {
                $serviceLogs = $this->serviceLocator->get('My\Models\Logs');
                $arrLogs = array(
                    'user_id' => UID,
                    'logs_controller' => $paramsRoute['__CONTROLLER__'],
                    'logs_action' => $paramsRoute['action'],
                    'logs_time' => time(),
                    'logs_detail' => 'Xóa Tỉnh/thành có id = ' . $intCityID,
                );
                $serviceLogs->add($arrLogs);
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'message' => 'Xóa Tỉnh / Thành hoàn tất')));
            }
        }
        return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0, 'message' => 'Xảy ra lỗi trong quá trình xử lý. Xin vui lòng thử lại')));
    }

}
