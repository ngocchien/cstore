<?php

if (APPLICATION_ENV === 'dev') {
    $display_not_found_reason = true;
    $display_exceptions = true;
    $errorHandler = array(
        'display' => true,
        'ajax_only' => true,
        'show_trace' => true
    );
} else {
    $display_not_found_reason = false;
    $display_exceptions = false;
    $errorHandler = array(
        'display' => false,
        'ajax_only' => false,
        'show_trace' => false
    );
}

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'frontend' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action][/page:page]][/]',
                    'constraints' => array(
                        'controller' => 'index',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'frontend-search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/s/[tim-kiem-[:keySlug[.html]]][:brand/][?[s=:s][&price=:price][&sort=:sort][&page=:page]]',
                    'constraints' => array(
                        'controller' => 'search',
                        'index' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        's' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'categorySlug' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'categoryID' => '[0-9]+',
                        'brand' => '[a-zA-Z0-9_-]*',
                        'keySlug' => '[a-zA-Z0-9_-]*',
                        'price' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'search',
                        'action' => 'index',
                    ),
                ),
            ),
            'frontend-order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action][?[s=:s][&page=:page]]]',
                    'constraints' => array(
                        'controller' => 'order',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        //'category' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        's' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'order-view' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action][/id/:id]]',
                    'constraints' => array(
                        'controller' => 'order',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'product_rate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:rate].html',
                    'constraints' => array(
                        'rate' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'product',
                        'action' => 'rate'
                    ),
                ),
            ),
            'search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action]]',
                    'constraints' => array(
                        'controller' => 'search',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'search',
                        'action' => 'index',
                    ),
                ),
            ),
            'product' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/pd[[/:productslug]-[:productId].html]',
                    'constraints' => array(
                        //'categorySlug' => '[a-zA-Z0-9_-]*',
                        //'categoryId' => '[a-zA-Z0-9_-]*',
                        'productslug' => '[a-zA-Z0-9_-]*',
                        'productId' => '[0-9]+',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'product',
                        'action' => 'detail',
                        //'categorySlug' => '',
                        //'categoryId' => 0,
                        'productslug' => '',
                        'productId' => 0
                    ),
                ),
            ),
            'tags' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/tags[/:tagsSlug]-[:tagsID][/:brand][.html][?[&price=:price][&sort=:sort][&page=:page]]',
                    'constraints' => array(
                        'tagsSlug' => '[a-zA-Z0-9_-]*',
                        'tagsID' => '[a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z0-9_-]*',
                        'brand' => '[a-zA-Z0-9_-]*',
                        'price' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'tags',
                        'action' => 'index',
                        'tagsSlug' => '',
                        'tagsID' => 0,
                    ),
                ),
            ),
            'tags-content' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/ctags[/:tagsSlug]-[:tagsID][.html][?[&page=:page]]',
                    'constraints' => array(
                        'tagsSlug' => '[a-zA-Z0-9_-]*',
                        'tagsID' => '[a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'TagsContent',
                        'action' => 'index',
                        'tagsSlug' => '',
                        'tagsID' => 0,
                    ),
                ),
            ),
            'category' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/ca[/:categorySlug]-[:categoryID][/:brand][/:action][.html][?[s=:s][&price=:price][&sort=:sort][&page=:page]]]',
                    'constraints' => array(
                        'controller' => 'category',
                        'action' => '[a-zA-Z0-9_-]*',
                        'categorySlug' => '[a-zA-Z0-9_-]*',
                        'categoryID' => '[0-9]+',
                        's' => '[a-zA-Z0-9_-]*',
                        'brand' => '[a-zA-Z0-9_-]*',
                        'price' => '[a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'category',
                        'action' => 'index',
                        'categorySlug' => '',
                        'categoryId' => 0,
                    ),
                ),
            ),
            'cate' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action]]',
                    'constraints' => array(
                        'controller' => 'category',
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'category',
                        'action' => 'index',
                    ),
                ),
            ),
            'brand' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/br[/:brandSlug][[/:categorySlug]-[:categoryID]].html][?[s=:s][&price=:price][&sort=:sort][&page=:page]]',
                    'constraints' => array(
                        'controller' => 'brand',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'brandSlug' => '[a-zA-Z0-9_-]*',
                        's' => '[a-zA-Z0-9_-]*',
                        'categorySlug' => '[a-zA-Z0-9_-]*',
                        'categoryID' => '[0-9]+',
                        'price' => '[a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'brand',
                        'action' => 'index',
                        'brandSlug' => '',
                        'brandID' => 0,
                    ),
                ),
            ),
            'brand-ajax' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/thuong-hieu.html',
                    'constraints' => array(
                        'module' => 'frontend',
                        'controller' => 'brand',
                        'action' => 'get-ajax'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'brand',
                        'action' => 'get-ajax'
                    ),
                ),
            ),
            'product_print' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/in-san-pham-[:orderID].html',
                    'constraints' => array(
                        'controller' => 'product',
                        'action' => 'print-product',
                        'orderID' => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'product',
                        'action' => 'print-product'
                    ),
                ),
            ),
            'product_order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/thong-tin-khach-hang.html',
                    'constraints' => array(
                        'controller' => 'product',
                        'action' => 'info-order',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'product',
                        'action' => 'info-order'
                    ),
                ),
            ),
            'insert_order' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/them-don-hang.html',
                    'constraints' => array(
                        'controller' => 'product',
                        'action' => 'insert-order',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'product',
                        'action' => 'insert-order'
                    ),
                ),
            ),
            'auth' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[-:action]]',
                    'constraints' => array(
                        'controller' => 'auth',
                        'action' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'auth',
                        'action' => '[a-zA-Z0-9_-]*',
                    ),
                ),
            ),
            'captcha' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[-:action]]',
                    'constraints' => array(
                        'controller' => 'captcha',
                        'action' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'captcha',
                        'action' => '[a-zA-Z0-9_-]*',
                    ),
                ),
            ),
            'checkout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/][:action/]]',
                    'constraints' => array(
                        'controller' => 'checkout',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'index',
                    ),
                ),
            ),
            'checkout_step_one' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/xac-nhan-thong-tin-don-hang.html',
                    'constraints' => array(
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'step-one',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'step-one',
                    ),
                ),
            ),
            'checkout_step_two' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dat-hang-thanh-cong.html',
                    'constraints' => array(
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'step-two',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'step-two',
                    ),
                ),
            ),
            'checkout_add_product' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/checkout/[:slug]-[:prod_id].html',
                    'constraints' => array(
                        'slug' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'prod_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'checkout',
                        'action' => 'index',
                        'slug' => 'khong-them-san-pham-nao',
                        'prod_id' => 0,
                    ),
                ),
            ),
            'comment' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller/[:action/]]',
                    'constraints' => array(
                        'controller' => 'comment',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'comment',
                        'action' => 'index',
                    ),
                ),
            ),
            'profile' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action]]',
                    'constraints' => array(
                        'controller' => 'profile',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            'frontend-stand' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/gian-hang[/:action][/:id][?[&page=:page]]',
                    'constraints' => array(
                        'controller' => 'stand',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9_-]*',
                        'page' => '[0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'stand',
                        'action' => 'index',
                        'id' => 1,
                    ),
                ),
            ),
            'frontend-view-stand' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/danh-sach-gian-hang[/:id][?[&page=:page]]',
                    'constraints' => array(
                        'controller' => 'stand',
                        'id' => '[0-9_-]*',
                        'page' => '[0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'stand',
                        'action' => 'view',
                        'id' => 1,
                    ),
                ),
            ),
            'frontend-uploader' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/uploader[/:action]',
                    'constraints' => array(
                        'controller' => 'uploader',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'uploader',
                        'action' => 'index',
                    ),
                ),
            ),
            'general' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/ge/[:code]-[:id].html',
                    'constraints' => array(
                        'code' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'general',
                        'action' => 'index',
                        'code' => '',
                        'id' => 0,
                    ),
                ),
            ),
            'content' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/tt[[/:categorySlug]-[:categoryID].html][?[&page=:page]]',
                    'constraints' => array(
                        'slug' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'content',
                        'action' => 'index',
                        'slug' => 'tt',
                        'id' => 0,
                    ),
                ),
            ),
            'content_detail' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/co/[:contslug]-[:contId].html',
                    'constraints' => array(
                        'contslug' => '[a-zA-Z0-9_-]*',
                        'contId' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'content',
                        'action' => 'view',
                        'contslug' => 'chi-tiet',
                        'contId' => 0,
                    ),
                ),
            ),
            'ordertracking' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action][?id=:id][&email=:email]]',
                    'constraints' => array(
                        'controller' => 'ordertracking',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'email' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'index',
                        'action' => 'index',
                    ),
                ),
            ),
            '404' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/404.html',
                    'constraints' => array(
                        'controller' => 'error',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'error',
                        'action' => 'e404',
                    ),
                ),
            ),
            'notfound' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/s[?[s=:s][&price=:price][&sort=:sort][&page=:page]]',
                    'constraints' => array(
                        'controller' => 'error',
                        'index' => 'redirect',
                        's' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'brand' => '[a-zA-Z0-9_-]*',
                        'price' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'sort' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'page' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'error',
                        'action' => 'redirect',
                    ),
                ),
            ),
            'menu' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/][:action[/]]]',
                    'constraints' => array(
                        'controller' => 'menu',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'menu',
                        'action' => 'load-menu',
                    ),
                ),
            ),
            'sitemap' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '[/:controller[/:action].html][?[&page=:page]]',
                    'constraints' => array(
                        'controller' => 'sitemap',
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'sitemap',
                        'action' => 'index',
                    ),
                ),
            ), 'forget-pasword' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/lay-mat-khau.html',
                    'constraints' => array(
                        'controller' => 'sitemap',
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Frontend\Controller',
                        'module' => 'frontend',
                        'controller' => 'Auth',
                        'action' => 'confirmResetPassword',
                    ),
                ),
            ),
        ),
    ),
    'module_layouts' => array(
        'Frontend' => FRONTEND_TEMPLATE . '/layout/layout'
    ),
    'view_helpers' => array(
        'invokables' => array(
            'paging' => 'My\View\Helper\Paging',
        )
    ),
    'translator' => array('locale' => 'en_US'),
    'view_manager' => array(
        'display_not_found_reason' => $display_not_found_reason,
        'display_exceptions' => $display_exceptions,
        'doctype' => 'HTML5',
        'template_map' => array(
            FRONTEND_TEMPLATE . '/layout/layout' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/layout.phtml',
            'frontend/createOrder' => __DIR__ . '/../view/email/create_order.phtml',
            'frontend/topbar' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/topbar.phtml',
            'frontend/vertical-menu' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/vertical-menu.phtml',
            'frontend/horizontal-menu' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/horizontal-menu.phtml',
            'frontend/main-left-banner' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/main-left-banner.phtml',
            'frontend/main-right-banner' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/main-right-banner.phtml',
            'frontend/main-bottom-banner' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/main-bottom-banner.phtml',
            'frontend/user-nav' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/user-nav.phtml',
            'frontend/footer' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/footer.phtml',
            'frontend/layout/viewQuotation' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/view-quotation.phtml',
            'frontend/layout/viewCategory' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/view-category.phtml',
            'frontend/layout/viewTags' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/view-tags.phtml',
            'frontend/catagory-banner' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/catagory-banner.phtml',
            'error/production' => __DIR__ . '/../view/error/production.phtml',
            'frontend/order/add-product-cart' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/order/add-product-cart.phtml',
            'frontend/order/show-cart' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/order/show-cart.phtml',
            'frontend/index/template_1' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/index/template_1.phtml',
            'frontend/index/template_2' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/index/template_2.phtml',
            'frontend/index/template_3' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/index/template_3.phtml',
            'frontend/index/template_4' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/index/template_4.phtml',
            'frontend/cart-frame' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/cart-frame.phtml',
            'frontend/form' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/form.phtml',
            'frontend/template_email' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/template_email.phtml',
            'frontend/menu' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/menu.phtml',
            'frontend/leftmenu-order' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/profile/leftmenu.phtml',
            'frontend/layout/comment' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/comment.phtml',
            'frontend/layout/comment-content' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/comment_content.phtml',
            'frontend/search_ajax' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/search/search_ajax.phtml',
            'frontend/layout/main-menu-ajax' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/main-menu-ajax.phtml',
            'frontend/brand_ajax' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/brand/brand_ajax.phtml',
            'info-order' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/frontend/product/info_order.phtml',
            'frontend/template_register_user' => __DIR__ . '/../view/' . FRONTEND_TEMPLATE . '/layout/template_register_user.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view/' . FRONTEND_TEMPLATE,
        ),
        'json_exceptions' => $errorHandler,
    ),
);
