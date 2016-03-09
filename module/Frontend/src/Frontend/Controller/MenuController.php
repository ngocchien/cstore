<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    Zend\View\Model\ViewModel;


class MenuController extends MyController {
    /* @var $serviceTemplate \My\Models\Template */
    /* @var $serviceContent \My\Models\Content */
    /* @var $serviceCategory \My\Models\Category */
    /* @var $serviceProduct \My\Models\Product */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:index:index' => 'jquery.lazyload.js',
            ];
        }
    }

    public function loadMenuAction() {
        $params = $this->params()->fromPost();
        $view = new ViewModel();
        $view->setTemplate('layout/main-menu-ajax');
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $content = $viewRender->render($view);
        return $this->getResponse()->setContent(json_encode(array('st' => 1, 'data' => $content)));
    }

}
