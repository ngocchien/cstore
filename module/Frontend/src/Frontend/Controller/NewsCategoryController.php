<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\Validator\Validate;

class NewsCategoryController extends MyController {
    
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceContent \My\Models\Content */

    public function __construct() {
        
    }

    public function indexAction() {
        
    }

    public function viewAction() {
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $validator = new Validate();
        if (!$validator->Digits($params['id'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $detailCategory = $serviceCategory->getDetail(array('cate_id' => 1));
        if (count($detailCategory) == 0) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $intPage = $this->params()->fromRoute('page', 1);
        $arrCondition = array('cate_id' => 1, 'not_cont_status' => -1);
        $intLimit = 15;
        $serviceContent = $this->serviceLocator->get('My\Models\Content');
        //$intTotal = $serviceContent->getTotal($arrCondition);
        //$arrContentList = $serviceContent->getListLimit($arrCondition, $intPage, $intLimit, 'cont_id DESC');

        $instanceSearchContent = new \My\Search\Content();
        $instanceSearchContent->setParams($arrCondition);
        $intTotal = $instanceSearchContent->getTotalData();
        
        $instanceSearchContent = new \My\Search\Content();
        $arrCondition = array('cate_id' => 1, 'not_cont_status' => -1,'page'=>1);
        $instanceSearchContent->setParams($arrCondition)->setLimit($intLimit);
        $arrContentList = $instanceSearchContent->getListLimit();
        
        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
        return array(
            'params' => $params,
            'paging' => $paging,
            'arrContentList' => $arrContentList,
            'detailCategory' => $detailCategory,
        );
    }

}
