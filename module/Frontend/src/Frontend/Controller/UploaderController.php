<?php

namespace Frontend\Controller;

use My\General;
use Zend\Filter\File\RenameUpload;
use Zend\InputFilter\FileInput;
use Zend\Validator\File\Size;
use My\Controller\MyController;

class UploaderController extends MyController {

    const FILE = 'file';

    public function __construct() {
        $input = new FileInput(self::FILE);
        $input->getValidatorChain()->attach(new Size(['max' => "1024"]));
        $input->getFilterChain()->attach(new RenameUpload([
            'overwrite' => false,
            'use_upload_name' => true,
            'target' => '/'
        ]));
    }

    public function uploadImageAction() {
        $result = array();
        if ($this->getRequest()->isPost()) {
            $files = $this->params()->fromFiles();
            $folder = $this->params()->fromQuery('folder');
            $filename = $this->params()->fromQuery('filename');
            if ((empty($files) || !is_array($files)) && empty($folder) && empty($filename)) {
                echo 0;
                return false;
            }
            $result = General::ImageUpload($files[$filename], $folder);
        }
        return $this->getResponse()->setContent(json_encode($result));
    }

}

?>
