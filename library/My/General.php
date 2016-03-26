<?php

namespace My;

class General {

    const TITLE_META = " | CSTORE - Đồ chơi Công nghệ";
    const MEMBER = 1;
    const ADMINISTRATOR = 2;
    const MODERATOR = 3;
    const SEO = 4;
    const SUPPORTER = 5;
    const EDITOR = 6;
    const MS = 0;
    const MR = 1;
    const MRS = 2;
    const PREFIX_IN = '';
    const ORDER_CODE = 'CS';
    //1:chuyển khoản || 2 : thanh toán qua bảo kim || 3 : trả tiền khi nhận hàng
    const PAYMENT_TRANSFER = 1;
    const PAYMENT_BAOKIM = 2;
    const PAYMENT_RECEIVED = 3;
    // status order
    const ORDER_STATUS_CENCAL = -1; // da huy
    const ORDER_STATUS_BENDING = 0; // cho kiem duyet
    const ORDER_STATUS_APPROVED = 1; // da kiem duyet
    const ORDER_STATUS_DELIVERY = 2;   //dang giao hang
    const ORDER_STATUS_RECIEVED = 3;    // da nhan hang
    const ORDER_STATUS_PAID = 4; // da thu tien
    const ORDER_STATUS_RETURN = 5; //Khách trả lại hàng
    const ORDER_STATUS_WAITING = 6; //Chờ hàng về
    //delivery
    const MENU_LOCALTION_TOP = 1;
    const MENU_LOCALTION_BUTTON_1 = 2;
    const MENU_LOCALTION_BUTTON_2 = 3;
    const MENU_LOCALTION_BUTTON_3 = 4;
    const MENU_LOCALTION_BUTTON_4 = 5;
    const MENU_LOCALTION_BUTTON_5 = 6;
    // vi tri dat banner
    const LOCATION_CATE_1 = 1;
    const LOCATION_CATE_2 = 2;
    const LOCATION_CATE_3 = 3;
    const LOCATION_CONT_1 = 4;
    const LOCATION_CONT_2 = 5;
    const LOCATION_CONT_3 = 6;
    const LOCATION_MOBI_HOME = 7;
    const DISABLED = 0;
    const ENABLED = 1;
    //define email admin 
    const EMAIL_SUPPORT = 'megavita.vn@gmail.com';
    const EMAIL_BCC = 'megavita.vn@gmail.com';
    const EMAIL_SYS = 'megavita.vn@gmail.com';
    const EMAIL_SYS_PASSWORD = 'muathuoctot';
    const EMAIL_BUY_PRODUCT = 'ncongphuoc@gmail.com';
    const EMAIL_CC_BUY_PRODUCT = '';
    //define host mail
    const HOST_MAIL = 'smtp.gmail.com';
    //define phương thức vận chuyển
    const SHIP_PROSHIP = 1;
    const SHIP_GIAONHAN = 2;
    const SHIP_POST = 3;

    private $headlink;
    static $typeMsg = array(
        '0' => 'Lịch chăm sóc khách',
        '1' => 'Giao việc',
        '2' => 'Thông báo'
    );
    static $foreground_colors = array(
        'black' => '0;30', 'dark_gray' => '1;30',
        'blue' => '0;34', 'light_blue' => '1;34',
        'green' => '0;32', 'light_green' => '1;32',
        'cyan' => '0;36', 'light_cyan' => '1;36',
        'red' => '0;31', 'light_red' => '1;31',
        'purple' => '0;35', 'light_purple' => '1;35',
        'brown' => '0;33', 'yellow' => '1;33',
        'light_gray' => '0;37', 'white' => '1;37',
    );
    static $background_colors = array(
        'black' => '40', 'red' => '41',
        'green' => '42', 'yellow' => '43',
        'blue' => '44', 'magenta' => '45',
        'cyan' => '46', 'light_gray' => '47',
    );

    public static function getSlug($string, $maxLength = 255, $separator = '-') {
        $arrCharFrom = array("ạ", "á", "à", "ả", "ã", "Ạ", "Á", "À", "Ả", "Ã", "â", "ậ", "ấ", "ầ", "ẩ", "ẫ", "Â", "Ậ", "Ấ", "Ầ", "Ẩ", "Ẫ", "ă", "ặ", "ắ", "ằ", "ẳ", "ẵ", "ẫ", "Ă", "Ắ", "Ằ", "Ẳ", "Ẵ", "Ặ", "Ẵ", "ê", "ẹ", "é", "è", "ẻ", "ẽ", "Ê", "Ẹ", "É", "È", "Ẻ", "Ẽ", "ế", "ề", "ể", "ễ", "ệ", "Ế", "Ề", "Ể", "Ễ", "Ệ", "ọ", "ộ", "ổ", "ỗ", "ố", "ồ", "Ọ", "Ộ", "Ổ", "Ỗ", "Ố", "Ồ", "Ô", "ô", "ó", "ò", "ỏ", "õ", "Ó", "Ò", "Ỏ", "Õ", "ơ", "ợ", "ớ", "ờ", "ở", "ỡ", "Ơ", "Ợ", "Ớ", "Ờ", "Ở", "Ỡ", "ụ", "ư", "ứ", "ừ", "ử", "ữ", "ự", "Ụ", "Ư", "Ứ", "Ừ", "Ử", "Ữ", "Ự", "ú", "ù", "ủ", "ũ", "Ú", "Ù", "Ủ", "Ũ", "ị", "í", "ì", "ỉ", "ĩ", "Ị", "Í", "Ì", "Ỉ", "Ĩ", "ỵ", "ý", "ỳ", "ỷ", "ỹ", "Ỵ", "Ý", "Ỳ", "Ỷ", "Ỹ", "đ", "Đ");
        $arrCharEnd = array("a", "a", "a", "a", "a", "A", "A", "A", "A", "A", "a", "a", "a", "a", "a", "a", "A", "A", "A", "A", "A", "A", "a", "a", "a", "a", "a", "a", "a", "A", "A", "A", "A", "A", "A", "A", "e", "e", "e", "e", "e", "e", "E", "E", "E", "E", "E", "E", "e", "e", "e", "e", "e", "E", "E", "E", "E", "E", "o", "o", "o", "o", "o", "o", "O", "O", "O", "O", "O", "O", "O", "o", "o", "o", "o", "o", "O", "O", "O", "O", "o", "o", "o", "o", "o", "o", "O", "O", "O'", "O", "O", "O", "u", "u", "u", "u", "u", "u", "u", "U", "U", "U", "U", "U", "U", "U", "u", "u", "u", "u", "U", "U", "U", "U", "i", "i", "i", "i", "i", "I", "I", "I", "I", "I", "y", "y", "y", "y", "y", "Y", "Y", "Y", "Y", "Y", "d", "D");
        $arrCharnonAllowed = array("©", "®", "Æ", "Ç", "Å", "Ç", "๏", "๏̯͡๏", "Î", "Ø", "Û", "Þ", "ß", "å", "", "¼", "æ", "ð", "ñ", "ø", "û", "!", "ñ", "[", "\"", "$", "%", "'", "(", ")", "♥", "(", "+", "*", "/", "\\", ",", ":", "¯", "", "+", ";", "<", ">", "=", "?", "@", "`", "¶", "[", "]", "^", "~", "`", "|", "", "_", "?", "*", "{", "}", "€", "�", "ƒ", "„", "", "…", "‚", "†", "‡", "ˆ", "‰", "ø", "´", "Š", "‹", "Œ", "�", "Ž", "�", "ॐ", "۩", "�", "‘", "’", "“", "”", "•", "۞", "๏", "—", "˜", "™", "š", "›", "œ", "�", "ž", "Ÿ", "¡", "¢", "£", "¤", "¥", "¦", "§", "¨", "«", "¬", "¯", "°", "±", "²", "³", "´", "µ", "¶", "¸", "¹", "º", "»", "¼", "¾", "¿", "Å", "Æ", "Ç", "?", "×", "Ø", "Û", "Þ", "ß", "å", "æ", "ç", "ï", "ð", "ñ", "÷", "ø", "ÿ", "þ", "û", "½", "☺", "☻", "♥", "♦", "♣", "♠", "•", "░", "◘", "○", "◙", "♂", "♀", "♪", "♫", "☼", "►", "◄", "↕", "‼", "¶", "§", "▬", "↨", "↑", "↓", "←", "←", "↔", "▲", "▼", "⌂", "¢", "→", "¥", "ƒ", "ª", "º", "▒", "▓", "│", "┤", "╡", "╢", "╖", "╕", "╣", "║", "╗", "╝", "╜", "╛", "┐", "└", "┴", "┬", "├", "─", "┼", "╞", "╟", "╚", "╔", "╩", "╦", "╠", "═", "╬", "╧", "╨", "╤", "╥", "╙", "╘", "╒", "╓", "╫", "╪", "┘", "┌", "█", "▄", "▌", "▐", "▀", "α", "Γ", "π", "Σ", "σ", "µ", "τ", "Φ", "Θ", "Ω", "δ", "∞", "φ", "ε", "∩", "≡", "±", "≥", "≤", "⌠", "⌡", "≈", "°", "∙", "·", "√", "ⁿ", "²", "■", "¾", "×", "Ø", "Þ", "ð", "ღ", "ஐ", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "•", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "•", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "Ƹ", 'Ӝ', 'Ʒ', "★", "●", "♡", "ஜ", "ܨ");
        $string = str_replace($arrCharFrom, $arrCharEnd, $string);
        $finalString = str_replace($arrCharnonAllowed, '', $string);
        $url = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $finalString);
        $url = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $url);
        $url = trim(substr(strtolower($url), 0, $maxLength));
        $url = preg_replace("/[\/_|+ -]+/", $separator, $url);
        return $url;
    }

    public static function getStatusOrder() {
        return array(
            self::ORDER_STATUS_BENDING => 'Chờ kiểm duyệt ',
            self::ORDER_STATUS_APPROVED => 'Đã kiểm duyệt ',
            self::ORDER_STATUS_WAITING => 'Chờ hàng về',
            self::ORDER_STATUS_CENCAL => 'Đã hủy đơn hàng',
            self::ORDER_STATUS_DELIVERY => 'Đang giao hàng',
            self::ORDER_STATUS_RECIEVED => 'Đã nhận hàng',
            self::ORDER_STATUS_PAID => 'Đã thu tiền',
            self::ORDER_STATUS_RETURN => 'Khách trả lại hàng',
        );
    }

    public static function getStatusStore() {
        return array(
            '1' => 'Còn hàng',
            '0' => 'Hết hàng',
        );
    }

    public static function getlistStatus() {
        return array(
            self::DISABLED => 'Không hiện thị',
            self::ENABLED => 'Hiện thị',
        );
    }

    public static function getBannerLocation() {
        return array(
            self::LOCATION_CATE_1 => 'Danh mục => vị trí 1',
            self::LOCATION_CATE_2 => 'Danh mục => vị trí 2',
            self::LOCATION_CATE_3 => 'Danh mục => vị trí 3',
            self::LOCATION_CONT_1 => 'Nội dung bài viết => vị trí 1',
            self::LOCATION_CONT_2 => 'Nội dung bài viết => vị trí 2',
            self::LOCATION_CONT_3 => 'Nội dung bài viết => vị trí 3',
            self::LOCATION_MOBI_HOME => 'Trang chủ mobile => Vị trí 1',
        );
    }

    public static function getPaymentMethod() {
        return array(
            self::PAYMENT_TRANSFER => 'Chuyển khoản',
            self::PAYMENT_BAOKIM => 'Thanh toán qua Bảo Kim',
            self::PAYMENT_RECEIVED => 'Thanh toán khi nhận hàng',
        );
    }

    public static function listCatalogType() {
        return array(
            self::CAT_TINTUC => 'Tin tức',
            self::CAT_ADS => 'Rao vặt',
            self::CAT_PR => 'Giới thiệu sp',
        );
    }

    public static function getMenuLocation() {
        return array(
            self::MENU_LOCALTION_TOP => 'Trên cùng',
            self::MENU_LOCALTION_BUTTON_1 => 'footer cột 1',
            self::MENU_LOCALTION_BUTTON_2 => 'footer cột 2',
            self::MENU_LOCALTION_BUTTON_3 => 'footer cột 3',
            self::MENU_LOCALTION_BUTTON_4 => 'footer cột 4',
            self::MENU_LOCALTION_BUTTON_5 => 'footer cột 5',
        );
    }

    //permission
    public static function getRole($roleID) {
        $arrRole = self::listRole();
        if (isset($arrRole[$roleID])) {
            return $arrRole[$roleID];
        }

        return false;
    }

    public static function getGender() {
        return array(
            self::MR => 'Nam',
            self::MS => 'Nữ',
            self::MRS => 'Không xác định',
        );
    }

    public static function listRole() {
        return array(
            self::ADMINISTRATOR => 'Administrator',
            self::MODERATOR => 'Moderator',
            self::EDITOR => 'Editor',
            self::SEO => 'SEO',
            self::SUPPORTER => 'Supporter',
            self::MEMBER => 'Người dùng thường',
        );
    }

    // Returns colored string
    public static function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .= trim($string) . "\033[0m\n\n";

        return $colored_string;
    }

    // Returns all foreground color names
    public static function getForegroundColors() {
        return array_keys(self::$foreground_colors);
    }

    // Returns all background color names
    public static function getBackgroundColors() {
        return array_keys(self::$background_colors);
    }

    public static function randomDigits($length = 6) {
        $characters = '0123456789';
        $string = '';
        $strLeng = strlen($characters) - 1;
        for ($p = 0; $p < $length - 1; $p ++) {
            $string .= $characters [mt_rand(0, $strLeng)];
        }
        return rand(1, 9) . $string;
    }

    /**
     * Upload images
     *
     * @param array $arrFile array images to upload
     * @param string $strControllerName controller name
     * @return array
     */
    public static function ImageUpload($arrFile = array(), $strControllerName = '', $resize = '') {
        $arrResult = array();
        if (empty($arrFile)) {
            return $arrResult;
        }
        $tmp = 1;
        $arrResult = array();
        $strTime = date('Y') . '/' . date('m') . '/' . date('d') . '/';
        $strFolderType = UPLOAD_PATH . $strControllerName;
        if (!is_dir($strFolderType)) {
            mkdir($strFolderType, 0777, true);
        }

        if (!is_writable($strFolderType) || !is_readable($strFolderType)) {
            chmod($strFolderType, 0777);
        }
        $strFolderByDate = $strFolderType . '/' . $strTime;
        $strFolderThumb = $strFolderByDate . 'thumbs/';
        if (!is_dir($strFolderByDate)) {
            mkdir($strFolderByDate, 0777, true);
            chmod($strFolderByDate, 0777);
            mkdir($strFolderThumb, 0777, true);
            chmod($strFolderThumb, 0777);
        }

        $arrFile = $arrFile[0] ? $arrFile : array($arrFile);
//        p($arrFile);die;
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $is_image = new \Zend\Validator\File\IsImage();
        $size = new \Zend\Validator\File\Size(array('max' => 2097152)); //2MB
        $total = new \Zend\Validator\File\Count(array('min' => 0, 'max' => 6));
        foreach ($arrFile as $k => $file) {
            $adapter->setValidators(array($size, $is_image, $total), $file['name']);
            $strExtension = pathinfo($strFolderByDate . $file['name'], PATHINFO_EXTENSION);
            if ($adapter->isValid($file['name'])) {
                $adapter->setDestination($strFolderByDate);
                $newFileName = microtime(true) . '.' . $strExtension;
                $adapter->addFilter('File\Rename', array(
                    'target' => $strFolderByDate . $newFileName,
                    'overwrite' => true,
                ));
                if ($adapter->receive($file['name'])) {
                    $arrSourceImage = UPLOAD_URL . $strControllerName . '/' . $strTime . $newFileName;
                    if ($resize != 1) {
                        $arrSourceImagePath = UPLOAD_PATH . $strControllerName . '/' . $strTime . $newFileName;
                        $img = new \My\SimpleImage\SimpleImage();
                        $img->load($arrSourceImagePath)->overlay(STATIC_PATH . '/logo_insert.png', 'bottom right', .30)->save($arrSourceImagePath);
                    }
                }
//                p('abc');die;
                $options = array('resizeUp' => true, 'jpegQuality' => 60);
                $arrThumb = self::getThumbSize($strControllerName);
                $arrThumbUploaded = array();
                if ($arrThumb) {
                    require_once VENDOR_DIR . 'phpThumb/ThumbLib.inc.php';
                    foreach ($arrThumb as $i => $thumbSize) {
                        list($width, $height) = explode('x', $thumbSize);
                        $thumbFileDir = $strFolderThumb . $thumbSize . '_' . $newFileName;
                        $tmp = \PhpThumbFactory::create($strFolderByDate . $newFileName, $options);
                        $tmp->fixedresize((int) $width, (int) $height)->save($thumbFileDir);
                        if (is_file($thumbFileDir)) {
                            $thumbFileURL = UPLOAD_URL . $strControllerName . '/' . $strTime . 'thumbs/' . $thumbSize . '_' . $newFileName;
                            $arrThumbUploaded[$thumbSize] = $thumbFileURL;
                        }
                    }
                }
                array_push($arrResult, array('sourceImage' => $arrSourceImage, 'thumbImage' => $arrThumbUploaded));
            }
        }
        return $arrResult;
    }

    /**
     * Send Email to users
     * @param string|array $email email list
     * @param String $strTitle email title
     * @param String $strMessage email message
     * @return Boolean
     */
    public static function sendMail($email, $strTitle, $strMessage) {

        try {
            if (empty($email) || empty($strTitle) || empty($strMessage)) {
                return false;
            }
            $arrEmail = is_array($email) ? $email : [$email];

            $arrEmail = array_filter($arrEmail);
            if (APPLICATION_ENV == 'dev') {
                $arrEmail = [self::EMAIL_SYS];
            }



            $html = new \Zend\Mime\Part($strMessage);
            $html->type = \Zend\Mime\Mime::TYPE_HTML;
            $html->charset = 'utf-8';

            $body = new \Zend\Mime\Message();
            $body->setParts(array($html));
            $mail = new \Zend\Mail\Message();
            $mail->setSubject($strTitle)
                    ->addFrom(self::EMAIL_SYS, 'Megavita.Vn')
                    ->setBody($body);
            if ($replyTo) {
                $mail->addReplyTo(self::EMAIL_SYS);
                $mail->addBcc(self::EMAIL_BCC);
            }

            foreach ($arrEmail as $strEmail) {
                $mail->addTo($strEmail);
            }

            if ($mail->isValid()) {
                $smtpOptions = new \Zend\Mail\Transport\SmtpOptions();
                $smtpOptions->setHost(self::HOST_MAIL)
                        ->setPort(465)
                        ->setConnectionClass('plain')
                        ->setConnectionConfig(
                                array(
                                    'username' => self::EMAIL_SYS,
                                    'password' => self::EMAIL_SYS_PASSWORD,
                                    'ssl' => 'ssl'
                                )
                );
                $transport = new \Zend\Mail\Transport\Smtp($smtpOptions);
                $transport->send($mail);
                return true;
            }
        } catch (\Zend\Http\Exception $exc) {
            if (APPLICATION_ENV !== 'production') {
                throw new \Zend\Http\Exception($exc->getMessage());
            }
            return false;
        }
    }

    /**
     * Get thumb image size for user, topic, category, banner controller
     *
     * @param String $strControllerName
     * @return Array
     * @throws \Exception
     */
    public static function getThumbSize($strControllerName = '') {
        if ($strControllerName) {
            switch ($strControllerName) {
                case 'product':
                    return array('83x83', '120x120', '50x50', '150x100', '170x170', '600x300', '224x224', '116x116', '100X100', '337x335');
                case 'cate':
                    return array('120x120', '50x50', '150x100', '170x170', '600x300', '224x224', '116x116');
                case 'content':
                    return array('150x100', '170x170', '600x300', '224x224', '116x116', '200x200');
                case 'banners':
                    return array('150x100', '50x40', '250x250', '200x200');
                case 'catalog':
                    return array(
                        '150x100',
                        '175x125',
                        '600x300',
                        '220x220',
                        '330x330'
                    );
                case 'profile':
                    return array(
                        '83x83',
                        '150x100',
                        '175x125',
                        '600x300',
                        '224x224',
                        '330x330',
                        '220x220',
                        '50x50',
                    );
                default:
                    return array('80x80', '330x330');
            }
        } else {
            throw new \Zend\Http\Exception('Controller name cannot be empty');
        }
    }

    /**
     * Get Elasticsearch Config
     * @return \Elastica\Client
     */
    public static function getSearchConfig() {

//        $port = (APPLICATION_ENV === 'production') ? 9210 : 9200;
//        if (in_array('isdev', $_SERVER['argv'])) {
//            $port = 9200;
//        }

        $port = '9200';

        $client = new \Elastica\Client(
                array('servers' => array(
                array('host' => 'localhost', 'port' => $port),
            )
        ));
        return $client;
    }

    /**
     * Get Client config
     * @return \GearmanClient
     */
    public static function getClientConfig() {
        $client = new \GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        return $client;
    }

    /**
     * Get worker config
     * @return \GearmanWorker
     */
    public static function getWorkerConfig() {
        $worker = new \GearmanWorker();
        $worker->addServer('127.0.0.1', 4730);
        return $worker;
    }

    /**
     * Get Redis config for pageview, comment, notification, banned user ... etc
     * @param String $strType
     */
    public static function getRedisConfig($strType) {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 15);

        switch ($strType) {
            case 'cart':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:cart:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'order':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:order:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'quotation':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:quotation:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'captcha':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:captcha:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'lost_password':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:lost_password:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'general':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:general:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(6);
                break;
            case 'support':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:support:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(7);
                break;
            case 'order-notication':
                $redis->setOption(\Redis::OPT_PREFIX, 'Amazon247:order-notification:');
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
                $redis->select(7);
                break;
            default:
                break;
        }
        return $redis;
    }

    /**
     * Split long String
     *
     * @param string $strText
     * @param int $totalSplit
     * @return array
     */
    public static function splitWords($strText, $totalSplit = 2) {
        $arrWords = explode(' ', $strText);
        $result = array();
        $icnt = count($arrWords) - ($totalSplit - 1);
        for ($i = 0; $i < $icnt; $i++) {
            $str = '';
            for ($o = 0; $o < $totalSplit; $o++) {
                $str .= $arrWords[$i + $o] . ' ';
            }
            array_push($result, trim($str));
        }
        return $result;
    }

    public static function toPrettyTime($seconds) {
        $day = 24 * 60 * 60;
        $hour = 60 * 60;
        $minute = 60;

        $d = floor($seconds / $day);
        $h = floor(($seconds % $day) / $hour);
        $m = floor(($seconds % ($day * $hour) ) / $minute);

        if ($d > 0) {
            return $d . ' ngày';
        } elseif ($h > 0) {
            return $h . ' giờ';
        } else {
            return $m . ' phút';
        }
    }

    public static function formatPrice($price) {
        list($priceEven, $priceOdd) = explode('.', $price);
        if (empty($priceOdd)) {
            return number_format($price);
        }
        return number_format($priceEven) . '.' . $priceOdd;
    }

    public static function dateToTimestamp($day, $month, $year, $hour = 0, $minute = 0, $second = 0) {
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    public static function dateRange($first, $last, $step = '+1 day', $format = 'Y/m/d') {
        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }

    public static function generateCaptcha($maxWordLength = 4, $width = 100, $height = 70) {

        if (!is_writable(CAPTCHA_PATH) || !is_dir(CAPTCHA_PATH)) {
            throw new \Exception(CAPTCHA_PATH . ' does not exist or cannot writable');
        }
        $captcha = new \Zend\Captcha\Image();
        $captcha->setWordlen($maxWordLength);
        $captcha->setWidth($width)->setHeight($height);
        $captcha->setFont(FRONTEND_FONT_PATH . 'arial.ttf');
        $captcha->setImgDir(CAPTCHA_PATH)->setImgUrl(CAPTCHA_URL);
        $captcha->setDotNoiseLevel(0)->setLineNoiseLevel(0);
        $captcha->setFontSize(15);
        $captcha->setExpiration(60 * 15); //session captcha expire in 15ms

        $captcha->generate();
        $id = $captcha->getId();

        $ses = new \Zend\Session\Container('Zend_Form_Captcha_' . $id);
        $captchaIterator = $ses->getIterator();
        return array(
            'id' => $id,
            'word' => $captchaIterator['word'],
            'url' => $captcha->getImgUrl() . $id . '.png'
        );
    }

    public static function getPaymentStatus($status, $separator = '<br />') {
        $strStatus = '';
        switch ($status) {
            case 0 :
                $strStatus = '<font color="red">Đã bị hủy</font>';
                break;
            case 1 :
                $strStatus = '<font color="red">Chờ kiểm duyệt</font>';
                break;
            case 2 :
                $strStatus = '<font color="blue">Đã kiểm duyệt</font> ' . $separator . ' <font color="red">Chờ thanh toán</font>';
                break;
            case 3 :
                $strStatus = '<font color="blue">Đã thanh toán</font> ' . $separator . ' <font color="red">Đang mua hàng</font>';
                break;
            case 4 :
                $strStatus = '<font color="blue">Đã mua hàng</font> ' . $separator . ' <font color="red">Đợi tracking</font>';
                break;
            case 5 :
                $strStatus = '<font color="blue">Đã có tracking</font> ' . $separator . ' <font color="red">Đợi về kho</font>';
                break;
            case 6 :
                $strStatus = '<font color="blue">Hàng đã về</font>';
                break;
            default :
                break;
        }
        return $strStatus;
    }

    public static function getDeliveryMethod($status) {
        switch ($status) {
            case 1 :
                $strStatus = '<font color="red">Giao trước - thu tiền</font>';
                break;
            case 2 :
                $strStatus = '<font color="red">Giao trước - KHÔNG thu tiền</font>';
                break;
            default :
                break;
        }
        return $strStatus;
    }

    public static function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", "'");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", "\\'");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    public static function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {  //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public static function crawler($strURL = '', $strCookiePath = '', $arrHeader = array(), $arrData = array()) {
        try {
            if (empty($strURL)) {
                throw new \Zend\Http\Exception('URL cannot be empty');
            }
            $crawler = curl_init($strURL);

            if ($arrHeader) {
                curl_setopt($crawler, CURLOPT_HTTPHEADER, $arrHeader);
            }

            if ($strCookiePath) {
                curl_setopt($crawler, CURLOPT_COOKIEJAR, $strCookiePath);
                curl_setopt($crawler, CURLOPT_COOKIEFILE, $strCookiePath);
                curl_setopt($crawler, CURLOPT_COOKIE, session_name() . '=' . session_id());
            }
            curl_setopt($crawler, CURLOPT_COOKIESESSION, TRUE);
            curl_setopt($crawler, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($crawler, CURLOPT_DNS_CACHE_TIMEOUT, 0);
            curl_setopt($crawler, CURLOPT_MAXREDIRS, 5);
            curl_setopt($crawler, CURLOPT_FRESH_CONNECT, TRUE);
            curl_setopt($crawler, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:32.0) Gecko/20100101 Firefox/32.0');
            curl_setopt($crawler, CURLOPT_RETURNTRANSFER, TRUE);
            $arrData ? curl_setopt($crawler, CURLOPT_POSTFIELDS, $arrData) : '';
            $data = curl_exec($crawler);
            curl_close($crawler);
            return $data;
        } catch (\Zend\Http\Exception $exc) {
            die('<b>' . $exc->getMessage() . '</b>' . '<p></p>' . $exc->getTraceAsString());
        }
    }

    public static function sortArray($records, $field, $reverse = false) {
        $hash = array();

        foreach ($records as $record) {
            $hash[$record[$field]] = $record;
        }

        ($reverse) ? krsort($hash) : ksort($hash);

        $records = array();

        foreach ($hash as $record) {
            $records[] = $record;
        }

        return $records;
    }

    /**
     * Detect time of the day
     * @return string
     */
    public static function detectTimeOfDay() {
        $time = date("H");
        if ($time < "12") {
            echo "buổi sáng vui vẻ và hạnh phúc.";
        } else
        if ($time >= "12" && $time < "18") {
            echo "buổi chiều vui vẻ và hạnh phúc.";
        } else
        if ($time >= "18" && $time < "22") {
            echo "buổi tối vui vẻ và hạnh phúc.";
        } else
        if ($time >= "22") {
            echo "buổi tối ngon giấc.";
        }
    }

    /**
     * Set FlashMessenger
     * @param string $namespace
     * @param string $message
     */
    public static function setFlashMessenger($namespace = '', $message = '') {
        $messenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
        $messenger->setNamespace($namespace)->addMessage($message);
    }

    public static function formatDateString($date) {
        $current = time() - $date;

        if ($current < 60) {
            return $current . " giây trước";
        } else {
            $minute = round($current / 60);
            if ($minute < 60) {
                return $minute . " phút trước";
            } else {
                $house = round($minute / 60);
                if ($house < 24) {
                    return $house . " giờ trước";
                } else {
                    $day = round($house / 24);
                    if ($day < 8) {
                        return $day . " Ngày trước";
                    } else {
                        return date("d/m/Y", $date);
                    }
                }
            }
        }
    }

    public static function clean($str = "") {
        $vowels = array('/', '\\', ':', ';', '!', '#', '$', '%', '^', '*', '(', ')', '=', '{', '}', '[', ']', '"', "'", '<', '>', '?', '~', '`', '&');
        return str_replace($vowels, "", $str);
    }

    public static function cleanArray($array = "") {
        $arrReturn = [];
        foreach ($array as $key => $value) {
            $arrReturn[$key] = $this->clean($value);
        }
        return $arrReturn;
    }

    public static function createLogs($arrParamsRoute, $arrParams, $intId) {
        $arrData = array(
            'user_id' => UID,
            'logs_module' => $arrParamsRoute['module'],
            'logs_controller' => $arrParamsRoute['__CONTROLLER__'],
            'logs_action' => $arrParamsRoute['action'],
            'logs_time' => time(),
            'logs_detail' => json_encode($arrParams),
            'logs_id_number' => $intId
        );

        return $arrData;
    }

}
