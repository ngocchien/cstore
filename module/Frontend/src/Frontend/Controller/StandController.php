<?php

namespace Frontend\Controller;

use My\Controller\MyController,
    My\General,
    My\Validator\Validate;

class StandController extends MyController {
    /* @var $serviceUser \My\Models\User */
    /* @var $serviceCity \My\Models\City */
    /* @var $serviceDistrict \My\Models\District */
    /* @var $serviceDistrict \My\Models\Stand */

    public function __construct() {
        if (FRONTEND_TEMPLATE == 'v1') {
            $this->defaultJS = [
                'frontend:stand:index' => 'jquery.range.js,category.js,insilde.js',
                'frontend:stand:edit' => 'insilde.js,Nileupload-min.js',
                'frontend:stand:register' => 'Nileupload-min.js',
                'frontend:stand:add-product' => 'Nileupload-min.js',
                'frontend:stand:edit-product' => 'Nileupload-min.js',
                'frontend:stand:view' => 'jquery.lazyload.js,jquery.range.js,insilde.js,category.js',
            ];
            $this->defaultCSS = [
                'frontend:stand:index' => 'jquery.range.css,checkbox.css,stand.css',
                'frontend:stand:edit' => 'jquery.range.css,checkbox.css,stand.css',
                'frontend:stand:register' => 'stand.css',
                'frontend:stand:add-product' => 'stand.css',
                'frontend:stand:edit-product' => 'stand.css',
                'frontend:stand:view' => 'jquery.range.css,checkbox.css',
            ];

            $this->externalJS = [
                'frontend:stand:edit' => STATIC_URL . '/f/v1/js/my/??stand.js',
                'frontend:stand:index' => STATIC_URL . '/f/v1/js/my/??stand.js',
                'frontend:stand:register' => STATIC_URL . '/f/v1/js/my/??stand.js',
                'frontend:stand:add-product' => array(
                    STATIC_URL . '/f/v1/js/library/tinymce/??tinymce.min.js',
                    STATIC_URL . '/f/v1/js/my/??stand.js',
                ), 'frontend:stand:edit-product' => array(
                    STATIC_URL . '/f/v1/js/library/tinymce/??tinymce.min.js',
                    STATIC_URL . '/f/v1/js/my/??stand.js',
                )
            ];
        }
    }

    public function indexAction() {

        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $arrDetailStand = $serviceStand->getDetail($arrCondition);
        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'register'));
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Danh sách sản phẩm trong gian h') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Danh sách sản phẩm!'));

        $route = 'frontend-stand';
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);
        $arrCondition = array(
            'user_id' => UID,
            'stan_prod_status' => 1
        );
        $intTotal = $serviceStandProduct->getTotal($arrCondition);
        $arrStandProductList = $serviceStandProduct->getListLimit($arrCondition, $intPage, $intLimit, 'stan_prod_id DESC');
        // lấy hết id của sản phẩm ra
        $arrIDProduct = [];
        foreach ($arrStandProductList as $arrStandProduct) {
            $arrIDProduct[] = $arrStandProduct["prod_id"];
        }
        $arrListProduct = [];
        if (!empty($arrIDProduct)) {
            $arrListProduct = $serviceProduct->getList(["list_prod_id" => implode(",", $arrIDProduct)]);
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper("frontend", "stand", "index", $intTotal, $intPage, $intLimit, $route, $params);


        return array(
            'params' => $params,
            'arrListProduct' => $arrListProduct,
            'paging' => $paging,
        );
    }

    public function registerAction() {

        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $arrDetailStand = $serviceStand->getDetail($arrCondition);
        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (!empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'index'));
        }
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params["stan_name"])) {
                $errors[] = "Vui lòng nhập tên gian hàng";
            }

            if (empty($params["stan_email"])) {
                $errors[] = "Vui lòng nhập địa chỉ email gian hàng";
            }

            if (empty($params["stan_phone"])) {
                $errors[] = "Vui lòng nhập số điện thoại gian hàng";
            }

            if (empty($params["stan_address"])) {
                $errors[] = "Vui lòng nhập địa chỉ gian hàng";
            }
            if (empty($params["stan_logo"])) {
                $errors[] = "Vui lòng upload logo gian hàng";
            }

            if (empty($errors)) {
                // Tiến hành thêm thông tin gian hàng
                $serviceStand->add([
                    'user_id' => UID,
                    'stan_name' => $params["stan_name"],
                    'stan_email' => $params["stan_email"],
                    'stan_phone' => $params["stan_phone"],
                    'stan_address' => $params["stan_address"],
                    'stan_logo' => $params["stan_logo"]
                ]);

                // đăng ký thành công chuyển về gian hàng
                return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'index'));
            }
        }


        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Đăng ký gian hàng') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Đăng ký gian hàng !'));
        return array(
            'params' => $params,
            'errors' => $errors
        );
    }

    public function editAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');

        $errors = array();
        if ($this->request->isPost()) {

            $params = $this->params()->fromPost();

            if (empty($params["stan_name"])) {
                $errors[] = "Vui lòng nhập tên gian hàng";
            }

            if (empty($params["stan_email"])) {
                $errors[] = "Vui lòng nhập địa chỉ email gian hàng";
            }

            if (empty($params["stan_phone"])) {
                $errors[] = "Vui lòng nhập số điện thoại gian hàng";
            }

            if (empty($params["stan_address"])) {
                $errors[] = "Vui lòng nhập địa chỉ gian hàng";
            }
            if (empty($params["stan_logo"])) {
                $errors[] = "Vui lòng upload logo gian hàng";
            }
            $standId = $params["stan_id"];
            // kiểm tra id stan có đúng của user này không

            $arrDetailStand = $serviceStand->getDetail(["user_id" => UID, "stan_id" => $standId]);
            if (empty($arrDetailStand)) {
                $errors[] = "Bạn không có quyền sửa gian hàng này";
            }
            if (empty($errors)) {
                // Tiến hành thêm thông tin gian hàng

                $serviceStand->edit([
                    'user_id' => UID,
                    'stan_name' => $params["stan_name"],
                    'stan_email' => $params["stan_email"],
                    'stan_phone' => $params["stan_phone"],
                    'stan_address' => $params["stan_address"],
                    'stan_logo' => $params["stan_logo"]
                        ], $standId);

                // đăng ký thành công chuyển về gian hàng
                //return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'edit'));
            }
        }
        $arrDetailStand = $serviceStand->getDetail($arrCondition);
        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'register'));
        }

        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Chỉn sửa thông tin gian hàng') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Megavita.vn - Chỉnh sửa thông tin gian hàng!'));
        return array(
            'params' => $params,
            'arrDetailStand' => $arrDetailStand,
            'errors' => $errors
        );
    }

    public function viewAction() {

        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());


        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $serviceCategory = $this->serviceLocator->get('My\Models\Category');
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');

        $arrDetailStand = $serviceStand->getDetail(["stan_id" => $params["id"]]);
        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('404', array());
        }


        $arrCategoryList = $serviceCategory->getList(array('cate_type' => 0, 'cate_status' => 1));

        $intPage = $this->params()->fromQuery('page', 1);
        $intLimit = $this->params()->fromQuery('limit', 15);
        $arrCondition = array(
            'stan_id' => $params["id"],
            'stan_prod_status' => 1
        );

        $intTotal = $serviceStandProduct->getTotal($arrCondition);
        $arrStandProductList = $serviceStandProduct->getListLimit($arrCondition, $intPage, $intLimit, 'stan_prod_id DESC');

        // lấy hết id của sản phẩm ra
        $arrIDProduct = [];
        foreach ($arrStandProductList as $arrStandProduct) {
            $arrIDProduct[] = $arrStandProduct["prod_id"];
        }
        $arrProductList = [];
        if (!empty($arrIDProduct)) {
            $arrProductList = $serviceProduct->getList(["list_prod_id" => implode(",", $arrIDProduct)]);
        }

        $helper = $this->serviceLocator->get('viewhelpermanager')->get('Paging');
        $paging = $helper("frontend", "stand", "view", $intTotal, $intPage, $intLimit, 'frontend-view-stand', $params);


        // lấy thông tin gian hàng
        $arrStand = $serviceStand->getDetail(["stan_id" => $params["id"]]);
        $arrCategoryParentList = array();
        foreach ($arrCategoryList as $value) {
            $arrCategoryParentList[] = $value;
        }

        return array(
        'arrStand' => $arrStand,
        'paging' => $paging,
        'arrProductList' => $arrProductList,
        'arrCategoryParentList' => $arrCategoryParentList,
        );
    }

    public function addProductAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetailStand = $serviceStand->getDetail($arrCondition);

        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'register'));
        }


        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params["prod_name"])) {
                $errors[] = "Vui lòng nhập tên sản phẩm";
            }

            if (empty($params["prod_price"])) {
                $errors[] = "Vui lòng nhập giá sản phẩm";
            }

            if (empty($params["prod_image"])) {
                $errors[] = "Vui lòng nhập hình ảnh chính";
            }

            if (empty($params["prod_image_sub"])) {
                $errors[] = "Vui lòng nhập hính ảnh slide";
            }

            if (empty($errors)) {
                $prodSlug = General::getSlug($params['prod_name']);
                $idProduct = $serviceProduct->add([
                    "prod_name" => $params["prod_name"],
                    "prod_slug" => $prodSlug,
                    "prod_price" => $params["prod_price"],
                    "prod_description" => $params["prod_description"],
                    "prod_detail" => $params["prod_detail"],
                    "prod_created" => time(),
                    "prod_image" => $params["prod_image"],
                    "prod_image_sub" => json_encode($params["prod_image_sub"])
                ]);

                // insert vào bảng sand prod
                if (!empty($idProduct)) {
                    $serviceStandProduct->add(["prod_id" => $idProduct, "user_id" => UID, "stan_id" => $arrDetailStand["stan_id"]]);
                }
            }
        }


        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Thêm sản phẩm vào gian hàng') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Thêm sản phẩm trong gian hàng !'));
        return array(
            'params' => $params,
            'arrDetailStand' => $arrDetailStand,
            'errors' => $errors
        );
    }

    public function editProductAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $params = $this->params()->fromRoute();
        $prodId = $params["id"];
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetailStand = $serviceStand->getDetail($arrCondition);

        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'register'));
        }

        $arrProduct = $serviceProduct->getDetail(["prod_id" => $prodId, "prod_actived" => 1]);
        if (empty($arrProduct)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'index'));
        }
        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();

            if (empty($params["prod_name"])) {
                $errors[] = "Vui lòng nhập tên sản phẩm";
            }

            if (empty($params["prod_price"])) {
                $errors[] = "Vui lòng nhập giá sản phẩm";
            }

            if (empty($params["prod_image"])) {
                $errors[] = "Vui lòng nhập hình ảnh chính";
            }

            if (empty($params["prod_image_sub"])) {
                $errors[] = "Vui lòng nhập hính ảnh slide";
            }

            if (empty($errors)) {
                $prodSlug = General::getSlug($params['prod_name']);
                $idProduct = $serviceProduct->edit([
                    "prod_name" => $params["prod_name"],
                    "prod_slug" => $prodSlug,
                    "prod_price" => $params["prod_price"],
                    "prod_description" => $params["prod_description"],
                    "prod_detail" => $params["prod_detail"],
                    "prod_created" => time(),
                    "prod_image" => $params["prod_image"],
                    "prod_image_sub" => json_encode($params["prod_image_sub"])
                        ], $prodId);
            }
        }


        $this->renderer = $this->serviceLocator->get('Zend\View\Renderer\PhpRenderer');
        $this->renderer->headTitle(html_entity_decode('Thêm sản phẩm vào gian hàng') . General::TITLE_META);
        $this->renderer->headMeta()->appendName('description', html_entity_decode('Sửa sản phẩm trong gian hàng!'));



        return array(
            'params' => $params,
            'arrProduct' => $arrProduct,
            'errors' => $errors
        );
    }

    public function removeProductAction() {
        if (UID == 0) {
            return $this->redirect()->toRoute('frontend', array('controller' => 'index', 'action' => 'index'));
        }

        $params = $this->params()->fromRoute();
        $arrCondition = array('user_id' => UID);
        $serviceStand = $this->serviceLocator->get('My\Models\Stand');
        $serviceStandProduct = $this->serviceLocator->get('My\Models\StandProduct');
        $serviceProduct = $this->serviceLocator->get('My\Models\Product');
        $arrDetailStand = $serviceStand->getDetail($arrCondition);

        // nếu không tồn tại  gian hàng redirect về trang đăng ký gian hàng
        if (empty($arrDetailStand)) {
            return $this->redirect()->toRoute('frontend-stand', array('controller' => 'stand', 'action' => 'register'));
        }


        $errors = array();
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $prodId = $params["prod_id"];
            if (empty($prodId)) {
                $errors[] = "ID sản phẩm không tồn tại";
            }
            // kiểm tra có quyền xóa sản phẩm này hay  không
            $arrDetailStand = $serviceStandProduct->getDetail(["user_id" => UID, "prod_id" => $prodId]);
            if (empty($arrDetailStand)) {
                $errors[] = "Bạn không có quyền xóa sản phẩm này";
            }

            if (empty($errors)) {
                $serviceStandProduct->edit(["stan_prod_status" => 0], $arrDetailStand["stan_prod_id"]);
                $serviceProduct->edit(["prod_actived" => 0], $prodId);
                // update status product
            }
        }

        return $this->getResponse()->setContent(json_encode(array('st' => empty($errors) ? 1 : -1, 'msg' => implode("<br/>", $errors))));
    }

}
