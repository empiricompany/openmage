<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Api2
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 roles controller
 *
 * @category   Mage
 * @package    Mage_Api2
 */
class Mage_Api2_Adminhtml_Api2_RoleController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Controller pre-dispatch method
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    public function preDispatch()
    {
        $this->_setForcedFormKeyActions(['delete', 'save']);
        return parent::preDispatch();
    }

    /**
     * Show grid
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('REST Roles'));

        $this->loadLayout()->_setActiveMenu('system/api/rest_roles');
        $this->_addBreadcrumb($this->__('Web services'), $this->__('Web services'));
        $this->_addBreadcrumb($this->__('REST Roles'), $this->__('REST Roles'));
        $this->_addBreadcrumb($this->__('Roles'), $this->__('Roles'));

        $this->renderLayout();
    }

    /**
     * Updating grid by ajax
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Updating users grid by ajax
     */
    public function usersGridAction()
    {
        $id = $this->getRequest()->getParam('id', false);

        $this->loadLayout();
        /** @var Mage_Api2_Block_Adminhtml_Roles_Tab_Users $grid */
        $grid = $this->getLayout()->getBlock('adminhtml.role.edit.tab.users');
        $grid->setUsers($this->_getUsers($id));

        $this->renderLayout();
    }

    /**
     * Create new role
     */
    public function newAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Rest Roles'));

        $this->loadLayout()->_setActiveMenu('system/api/rest_roles');
        $this->_addBreadcrumb($this->__('Web services'), $this->__('Web services'));
        $this->_addBreadcrumb($this->__('REST Roles'), $this->__('REST Roles'));
        $this->_addBreadcrumb($this->__('Roles'), $this->__('Roles'));

        $breadCrumb = $this->__('Add New Role');
        $breadCrumbTitle = $this->__('Add New Role');
        $this->_title($this->__('New Role'));

        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->renderLayout();
    }

    /**
     * Edit role
     */
    public function editAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        /** @var Mage_Api2_Model_Acl_Global_Role $role */
        $role = Mage::getModel('api2/acl_global_role')->load($id);

        if (!$role->getId()) {
            $this->_getSession()->addError($this->__('Role "%s" not found.', $id));
            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout()->_setActiveMenu('system/services/roles');

        $this->_title($this->__('System'))
             ->_title($this->__('Web Services'))
             ->_title($this->__('Rest Roles'));

        $breadCrumb = $this->__('Edit Role');
        $breadCrumbTitle = $this->__('Edit Role');
        $this->_title($this->__('Edit Role'));
        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        /** @var Mage_Api2_Block_Adminhtml_Roles_Tabs $tabs */
        $tabs = $this->getLayout()->getBlock('adminhtml.role.edit.tabs');
        $tabs->setRole($role);
        /** @var Mage_Adminhtml_Block_Template $child */
        foreach ($tabs->getChild() as $child) {
            $child->setData('role', $role);
        }

        /** @var Mage_Api2_Block_Adminhtml_Roles_Buttons $buttons */
        $buttons = $this->getLayout()->getBlock('adminhtml.roles.buttons');
        $buttons->setRole($role);

        /** @var Mage_Api2_Block_Adminhtml_Roles_Tab_Users $users */
        $users = $this->getLayout()->getBlock('adminhtml.role.edit.tab.users');
        $users->setUsers($this->_getUsers($id));

        //$this->getLayout()->getBlock('adminhtml.role.edit.tab.resources')->getResTreeJson();
        //exit;

        $this->renderLayout();
    }

    /**
     * Save role
     */
    public function saveAction()
    {
        $request = $this->getRequest();

        $id = $request->getParam('id', false);
        /** @var Mage_Api2_Model_Acl_Global_Role $role */
        $role = Mage::getModel('api2/acl_global_role')->load($id);

        if (!$role->getId() && $id) {
            $this->_getSession()->addError(
                $this->__('Role "%s" no longer exists', $role->getData('role_name')),
            );
            $this->_redirect('*/*/');
            return;
        }

        //Validate current admin password
        $currentPassword = $request->getParam('current_password', null);
        $request->setParam('current_password', null);
        $result = $this->_validateCurrentPassword($currentPassword);

        if (is_array($result)) {
            foreach ($result as $error) {
                $this->_getSession()->addError($error);
            }
            if ($id) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('*/*/new');
            }
            return;
        }

        $roleUsers  = $request->getParam('in_role_users', null);
        parse_str($roleUsers, $roleUsers);
        $roleUsers = array_keys($roleUsers);

        $oldRoleUsers = $this->getRequest()->getParam('in_role_users_old');
        parse_str($oldRoleUsers, $oldRoleUsers);
        $oldRoleUsers = array_keys($oldRoleUsers);

        $session = $this->_getSession();

        try {
            $role->setRoleName($this->getRequest()->getParam('role_name', false))
                    ->save();

            foreach ($oldRoleUsers as $oUid) {
                $this->_deleteUserFromRole($oUid, $role->getId());
            }

            foreach ($roleUsers as $nRuid) {
                $this->_addUserToRole($nRuid, $role->getId());
            }

            /**
             * Save rules with resources
             */
            /** @var Mage_Api2_Model_Acl_Global_Rule $rule */
            $rule = Mage::getModel('api2/acl_global_rule');
            if ($id) {
                $collection = $rule->getCollection();
                $collection->addFilterByRoleId($role->getId());

                /** @var Mage_Api2_Model_Acl_Global_Rule $model */
                foreach ($collection as $model) {
                    $model->delete();
                }
            }

            /** @var Mage_Api2_Model_Acl_Global_Rule_Tree $ruleTree */
            $ruleTree = Mage::getSingleton(
                'api2/acl_global_rule_tree',
                ['type' => Mage_Api2_Model_Acl_Global_Rule_Tree::TYPE_PRIVILEGE],
            );
            $resources = $ruleTree->getPostResources();
            $id = $role->getId();
            foreach ($resources as $resourceId => $privileges) {
                foreach ($privileges as $privilege => $allow) {
                    if (!$allow) {
                        continue;
                    }

                    $rule->setId(null)
                            ->isObjectNew(true);

                    $rule->setRoleId($id)
                            ->setResourceId($resourceId)
                            ->setPrivilege($privilege)
                            ->save();
                }
            }

            $session->addSuccess($this->__('The role has been saved.'));
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('An error occurred while saving role.'));
        }

        $this->_redirect('*/*/edit', ['id' => $id]);
    }

    /**
     * Delete role
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id', false);

        //Validate current admin password
        $currentPassword = $this->getRequest()->getParam('current_password', null);
        $this->getRequest()->setParam('current_password', null);
        $result = $this->_validateCurrentPassword($currentPassword);

        if (is_array($result)) {
            foreach ($result as $error) {
                $this->_getSession()->addError($error);
            }
            $this->_redirect('*/*/edit', ['id' => $id]);
            return;
        }

        try {
            /** @var Mage_Api2_Model_Acl_Global_Role $model */
            $model = Mage::getModel('api2/acl_global_role');
            $model->load($id)->delete();
            $this->_getSession()->addSuccess($this->__('Role has been deleted.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('An error occurred while deleting the role.'));
        }

        $this->_redirect('*/*/');
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        return $session->isAllowed('system/api/rest_roles');
    }

    /**
     * Get API2 roles ajax grid action
     */
    public function rolesGridAction()
    {
        /** @var Mage_Admin_Model_User $model */
        $model = Mage::getModel('admin/user');
        $model->load($this->getRequest()->getParam('user_id'));

        Mage::register('permissions_user', $model);
        $this->getResponse()
            ->setBody($this->getLayout()->createBlock('api2/adminhtml_permissions_user_edit_tab_roles')->toHtml());
    }

    /**
     * Get users possessing the role
     *
     * @param int $id
     * @return array|mixed
     */
    protected function _getUsers($id)
    {
        if ($this->getRequest()->getParam('in_role_users') != '') {
            return $this->getRequest()->getParam('in_role_users');
        }

        /** @var Mage_Api2_Model_Acl_Global_Role $role */
        $role = Mage::getModel('api2/acl_global_role');
        $role->setId($id);

        $resource = $role->getResource();
        $users = $resource->getRoleUsers($role);

        if (!count($users)) {
            $users = [];
        }

        return $users;
    }

    /**
     * Take away user role
     *
     * @param int $adminId
     * @param int $roleId
     * @return $this
     */
    protected function _deleteUserFromRole($adminId, $roleId)
    {
        /** @var Mage_Api2_Model_Resource_Acl_Global_Role $resourceModel */
        $resourceModel = Mage::getResourceModel('api2/acl_global_role');
        $resourceModel->deleteAdminToRoleRelation($adminId, $roleId);
        return $this;
    }

    /**
     * Give user a role
     *
     * @param int $adminId
     * @param int $roleId
     * @return $this
     */
    protected function _addUserToRole($adminId, $roleId)
    {
        /** @var Mage_Api2_Model_Resource_Acl_Global_Role $resourceModel */
        $resourceModel = Mage::getResourceModel('api2/acl_global_role');
        $resourceModel->saveAdminToRoleRelation($adminId, $roleId);
        return $this;
    }
}
