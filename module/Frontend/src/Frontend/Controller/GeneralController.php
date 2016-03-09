<?php

namespace Frontend\Controller;

use My\Controller\MyController;

class GeneralController extends MyController {
    /* @var $serviceContent \My\Models\General */

    public function __construct() {
        $this->defaultJS = [
            'frontend:general:index' => 'jquery.lazyload.js,jquery.range.js,insilde.js',
        ];
        $this->defaultCSS = [
            'frontend:general:index' => 'jquery.range.css',
        ];
//        $this->externalJS = [
//                // 'frontend:content:index' => STATIC_URL . '/f/v1/js/my/??content.js',
//        ];
    }
     public function indexAction(){
        $params = $this->params()->fromRoute();
        if (empty($params['id'])) {
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
       
        $arrCondition = array(
            'gene_id' => $params['id'],
            'gene_status' => 1
        );
        $serviceGeneral = $this->serviceLocator->get('My\Models\General');
        $generalDetail = $serviceGeneral->getDetail($arrCondition);
        if(!$generalDetail){
            $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }
        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode($generalDetail['gene_title']));
        return array(
            'generalDetail' => $generalDetail
        );
     }
}
