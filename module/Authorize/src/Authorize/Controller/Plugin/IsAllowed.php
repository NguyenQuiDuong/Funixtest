<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Authorize\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use BjyAuthorize\Service\Authorize;

/**
 * IsAllowed Controller plugin. Allows checking access to a resource/privilege in controllers.
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 */
class IsAllowed extends AbstractPlugin {

    /**
     * @var Authorize
     */
    protected $authorizeService;

    /**
     * @param Authorize $authorizeService
     */
    public function __construct(Authorize $authorizeService) {
        $this->authorizeService = $authorizeService;
    }

    /**
     * @param mixed      $resource
     * @param mixed|null $privilege
     * @return bool
     */
    public function __invoke($resource, $privilege = null) {
        return $this->authorizeService->isAllowed($resource, $privilege);
    }

    /**
     * @return Authorize
     */
    public function getAuthorizeService() {
        return $this->authorizeService;
    }

    /**
     * @param Authorize $authorize
     */
    public function setAuthorizeService(Authorize $authorize) {
        $this->authorizeService = $authorize;
        return $this;
    }
}