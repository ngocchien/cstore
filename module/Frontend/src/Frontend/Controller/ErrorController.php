<?php

namespace Frontend\Controller;

use My\Controller\MyController;

class ErrorController extends MyController {
    /* @var $serviceProduct \My\Models\Product */
    /* @var $serviceComment \My\Models\Comment */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProperties \My\Models\Properties */
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceContent  \My\Models\Content */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:error:e404' => 'jquery.lazyload.js',
            ];
            $this->defaultCSS = [
                'frontend:error:e404' => 'jquery.range.css,checkbox.css,jquery.jqzoom.css,jRating.jquery.css',
            ];
        }
    }

    public function indexAction() {
//        $params = $this->params()->fromRoute();
//        $intPage = $this->params()->fromRoute('page', 1);
//        $intLimit = 15;
//        $arrCondition = array('not_prod_actived' => -1);
//        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
//        $intTotal = $serviceProduct->getTotal($arrCondition);
//        $arrProdutList = $serviceProduct->getListLimit($arrCondition, $intPage, $intLimit, 'prod_id DESC');
//        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
//        $paging = $helper($params['module'], $params['__CONTROLLER__'], $params['action'], $intTotal, $intPage, $intLimit, $route, $params);
//        return array(
//            'params' => $params,
//            'paging' => $paging,
//            'arrProdutList' => $arrProdutList
//        );
    }

    public function E404Action() {
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle("404 NOT FOUND");
    }

    public function redirectAction() {
        return $this->redirect()->toRoute('404', array());
//        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
//        $this->renderer->headTitle("404 NOT FOUND");
    }

}
