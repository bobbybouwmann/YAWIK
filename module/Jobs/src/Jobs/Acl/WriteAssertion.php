<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   AGPLv3
 */

/** JobAccessAssertion.php */ 
namespace Jobs\Acl;

use Zend\Permissions\Acl\Assertion\AssertionInterface;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\Role\RoleInterface;
use Jobs\Entity\JobInterface;
use Auth\Entity\UserInterface;

class WriteAssertion implements AssertionInterface
{
    /* (non-PHPdoc)
     * @see \Zend\Permissions\Acl\Assertion\AssertionInterface::assert()
    */
    public function assert(Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $resource = null,
        $privilege = null)
    {
        if (!$role instanceOf UserInterface || !$resource instanceOf JobInterface || 'edit' != $privilege) {
            return false;
        }

        return $resource->getUser()->getId() == $role->getId();
    }
}