<?php

namespace My\Permission;

use Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;

class MyAcl {

    protected $acl;

    public function __construct($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
        $this->acl = new Acl();
        $this->buildPermission();
    }

    private function buildPermission() {
        $servicePermission = $this->serviceLocator->get('My\Models\Permission');
        $arrPermissionList = $servicePermission->getListjoinRole(array("grou_id" => GROU_ID, "perm_status" => 1));

        if (is_array($arrPermissionList) && count($arrPermissionList) > 0) {
            foreach ($arrPermissionList as $arrPermission) {
                $strResource = strtolower($arrPermission['module'] . ':' . $arrPermission['controller'] . ':' . $arrPermission['action']);
                if (!$this->acl->hasResource($strResource)) {
                    $this->acl->addResource(new Resource($strResource));
                }
            }
        }
    }

    public function checkPermission($roleID, $strModuleName, $strControllerName, $strActionName = null) {

        if ($strActionName != null) {
            $strActionName = trim(strtolower($strActionName));
        }
        $roleID = $this->formatRole($roleID);
        $strResource = trim(strtolower($strModuleName . ':' . $strControllerName . ':' . $strActionName));

        if (!$this->acl->hasResource($strResource)) {
            return false;
        }
        return true;
    }

    private function formatRole($roleID) {
        return 'role_' . $roleID;
    }

}
