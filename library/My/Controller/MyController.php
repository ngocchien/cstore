<?php

namespace My\Controller;

use Zend\Mvc\MvcEvent,
    Zend\Mvc\Controller\AbstractActionController;

class MyController extends AbstractActionController {
    /* @var $groupService \My\Models\Group */
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceTemplate \My\Models\Template */

    protected $defaultJS = '';
    protected $externalJS = '';
    protected $defaultCSS = '';
    protected $externalCSS = '';
    protected $serverUrl;
    protected $authservice;
    private $resource;
    private $renderer;

    public function onDispatch(MvcEvent $e) {
        if (php_sapi_name() != 'cli') {
            $this->serverUrl = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost();
            $this->params = $this->params()->fromRoute();
            $this->params['module'] = strtolower($this->params['module']);
            $this->params['controller'] = strtolower($this->params['__CONTROLLER__']);
            $this->params['action'] = strtolower($this->params['action']);
            $this->resource = $this->params['module'] . ':' . $this->params['controller'] . ':' . $this->params['action'];
            $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');

            $auth = $this->authenticate($this->params);

            if ($this->params['module'] === 'backend' && !$auth) {

                if (!$this->permission($this->params)) {
                    if ($this->request->isXmlHttpRequest()) {
                        die('Permission Denied!!!');
                    }
                    $this->layout('backend/error/accessDeny');
                    return false;
                }
            }

            $instanceStaticManager = new \My\StaticManager\StaticManager($this->resource, $this->serviceLocator);
            $instanceStaticManager
                    ->setJS(array('defaultJS' => $this->defaultJS))
                    ->setJS(array('externalJS' => $this->externalJS))
                    ->setCSS(array('defaultCSS' => $this->defaultCSS))
                    ->setCSS(array('externalCSS' => $this->externalCSS))
                    ->render(2.1);
            $this->setMeta($this->params);
        }
        return parent::onDispatch($e);
    }

    private function setMeta($arrData) {
        $this->renderer->headMeta()->setCharset('UTF-8');
        switch ($this->resource) {
            case 'frontend:index:index':
                $this->renderer->headTitle('Siêu thị đồ chơi công nghệ' . \My\General::TITLE_META);
                $this->renderer->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');
                $this->renderer->headMeta()->appendName('keywords', 'cstore, do choi cong nghe, phu kien dien thoai, phu kien dien tu, usb , tai nghe, cap sac , pin du phong mieng dan cương luc, op lung, gia re');
                $this->renderer->headMeta()->appendName('description', 'CSTORE - Chuyên bán các đồ chơi công nghệ, phụ kiện điện thoại , phụ kiện điện tử, các loại tai nghe , sạc - cáp - usb - pin dự phòng chất lượng cao - giá rẻ.');
                break;
            default:
                break;
        }
        if ($arrData['module'] === 'backend') {
            $this->renderer->headTitle('Administrator - CSTORE.COM');
            $this->renderer->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');
        }
    }

    private function permission($params) {

        $intUserRole = UID;
        //check group permission

        $groupService = $this->serviceLocator->get('My\Models\Group');
        $condition = array('grou_id' => GROU_ID, 'grou_status' => 1);

        $arrGroup = $groupService->getDetail($condition);
        //check can access CPanel
        if ($arrGroup['is_acp'] != 1) {
            return false;
        }
        //check use in fullaccess role
        if ($arrGroup && $arrGroup['is_fullaccess'] == 1) {
            define('FULL_ACCESS', 1);
            return true;
        }
        define('FULL_ACCESS', $arrGroup['is_fullaccess']);
        define('IS_ACP', $arrGroup['is_acp']);
        $serviceACL = $this->serviceLocator->get('ACL');

        $strActionName = $params['action'];
        if (strpos($params['action'], '-')) {
            $strActionName = '';
            $arrActionName = explode('-', $params['action']);
            foreach ($arrActionName as $k => $str) {
                if ($k > 0) {
                    $strActionName .= ucfirst($str);
                }
            }
            $strActionName = $arrActionName[0] . $strActionName;
        }

        $strControllerName = $params['controller'];
        if (strpos($params['controller'], '-')) {
            $strControllerName = '';
            $arrControllerName = explode('-', $params['controller']);
            foreach ($arrControllerName as $k => $str) {
                if ($k > 0) {
                    $strControllerName .= ucfirst($str);
                }
            }
            $strControllerName = $arrControllerName[0] . $strControllerName;
        }

        $strActionName = str_replace('_', '', $strActionName);
        $strControllerName = str_replace('_', '', $strControllerName);
        return $serviceACL->checkPermission($intUserRole, $params['module'], $strControllerName, $strActionName);
    }

    protected function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }
        return $this->authservice;
    }

    public function autocompleteAction() {
        $params = $this->params()->fromPost();
        if ($params && is_array($params)) {
            $serviceProduct = $this->serviceLocator->get('My\Models\Product');
            $serviceCategory = $this->serviceLocator->get('My\Models\Category');
            $url = $this->serviceLocator->get('viewhelpermanager')->get('URL');

            $arrProductList = $serviceProduct->getListLimit(array('prod_name_like' => trim($params['s']), 'prod_status' => 1), 1, 5, 'prod_viewer DESC');
            foreach ($arrProductList as &$val) {
                $prod_link = BASE_URL . $url('product', array('controller' => 'product', 'action' => 'index', 'productslug' => $val['prod_slug'], 'productId' => $val['prod_id']));
                $val['prod_link'] = $prod_link;
            }

            $arrCategoryList = $serviceCategory->getListLimit(array('cate_name_like' => trim($params['s']), 'cate_type' => '0', 'cate_status' => 1), 1, 5, 'cate_id DESC');
            foreach ($arrCategoryList as &$val) {
                $cate_link = BASE_URL . $url('category', array('controller' => 'category', 'action' => 'index', 'categorySlug' => $val['cate_slug'], 'categoryID' => $val['cate_id']));
                $val['cate_link'] = $cate_link;
            }
            if ($arrProductList || $arrCategoryList)
                return $this->getResponse()->setContent(json_encode(array('error' => 0, 'success' => 1, 'prod' => $arrProductList, 'cate' => $arrCategoryList)));
            return $this->getResponse()->setContent(json_encode(array('error' => 1, 'success' => 0)));
        }
    }

    private function authenticate($arrData) {
        
        $arrUserData = $this->getAuthService()->getIdentity();

        if ($arrData['module'] === 'backend') {
            
            if (empty($arrUserData)) {
                return $this->redirect()->toRoute('backend', array('controller' => 'auth', 'action' => 'login'));
            }

            define('UID', (int) $arrUserData['user_id']);
            define('MODULE', $arrData['module']);
            define('CONTROLLER', $arrData['controller']);
            define('FULLNAME', $arrUserData['user_fullname']);
            define('USERNAME', $arrUserData['user_name']);
            define('EMAIL', $arrUserData['user_email']);
            define('GROU_ID', $arrUserData['grou_id'] ? (int) $arrUserData['grou_id'] : 0);
            define('IS_ACP', (empty($arrUserData['grou_id']) ? 0 : 1));
            define('PERMISSION', json_encode($arrUserData['permission']));
        }

        if ($arrData['module'] === 'frontend') {
            define('UID', $arrUserData['user_id'] ? (int) $arrUserData['user_id'] : 0);
            define('FULLNAME', $arrUserData['user_fullname'] ? $arrUserData['user_fullname'] : '');
            define('USERNAME', $arrUserData['user_name'] ? $arrUserData['user_name'] : '');
            define('EMAIL', $arrUserData['user_email'] ? $arrUserData['user_email'] : '');
            define('PHONE', $arrUserData['user_phone'] ? $arrUserData['user_phone'] : '');
            define('AVATAR', $arrUserData['user_avatar'] ? $arrUserData['user_avatar'] : '');
            define('ADDRESS', $arrUserData['user_address'] ? $arrUserData['user_address'] : '');
            define('CITY_ID', $arrUserData['city_id'] ? $arrUserData['city_id'] : 0);
            define('DISTRICT_ID', $arrUserData['dist_id'] ? $arrUserData['dist_id'] : 0);
            define('WARD_ID', $arrUserData['ward_id'] ? $arrUserData['ward_id'] : 0);
            define('GROU_ID', $arrUserData['grou_id'] ? (int) $arrUserData['grou_id'] : 0);
            define('CHAT_EMAIL', $arrUserData['user_email'] ? $arrUserData['user_email'] : $this->request->getHeaders()->get('Cookie')->email);
            define('LOGGED', $arrUserData ? 1 : 0);
            define('CONTROLLER', $arrData['controller']);
            define('IS_ACP', (empty($arrUserData['grou_id']) ? 0 : 1));
            $serviceCategory = $this->serviceLocator->get('My\Models\Category');
            $serviceTemplate = $this->serviceLocator->get('My\Models\Template');
            $arrMenu = $serviceTemplate->getDetail(1);
            $servicMenu = $this->serviceLocator->get('My\Models\Menu');
            if (FRONTEND_TEMPLATE == 'v1') {
                $arrMenuTopBottom = $servicMenu->getList(array('menu_status' => 1, 'menu_type' => 0));
            }
            if (FRONTEND_TEMPLATE == 'mobile') {
                $arrMenuTopBottom = $servicMenu->getList(array('menu_status' => 1, 'menu_type' => 1));
            }

            $serviceBanner = $this->serviceLocator->get('My\Models\Banners');
            $arrBanner = $serviceBanner->getList(array('not_ban_cate_id' => 1, 'is_delete' => 0));
            define('ARR_BANNER', serialize($arrBanner));
            define('ARR_MENU', serialize($arrMenu));
            define('ARR_MENU_TOP_BOTTOM', serialize($arrMenuTopBottom));
            //Xu hướng tìm kiếm
            $serviceGeneral = $this->serviceLocator->get('My\Models\General');
            $keySearch = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'KEYWORD_SEARCH'));
            define('KEYWORD_SEARCH', $keySearch['gene_content']);
            $hotlineNumber = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'HOTLINE_NUMBER'));
            define('HOTLINE_NUMBER', $hotlineNumber['gene_content']);
            if ($arrData['controller'] == "index") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_INDEX'));
            } else if ($arrData['controller'] == "content") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_CONT'));
            } else if ($arrData['controller'] == "category") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_CATE'));
            } else if ($arrData['controller'] == "brand") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_BRAN'));
            } else if ($arrData['controller'] == "product") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_PROD'));
            } else if ($arrData['controller'] == "search") {
                $footer = $serviceGeneral->getDetail(array('gene_status' => 1, 'gene_code' => 'FO_SEAR'));
            } else {
                
            }

            define('FOOTER_CONTENT', empty($footer['gene_content']) ? "" : $footer['gene_content']);
        }
    }

}
