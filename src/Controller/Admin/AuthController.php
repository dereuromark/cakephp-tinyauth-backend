<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;
use TinyAuth\Utility\TinyAuth;

/**
 * @property \TinyAuthBackend\Model\Table\AclRulesTable $AclRules
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class AuthController extends AppController {

	/**
	 * @var string|null
	 */
	protected $modelClass = '';

	/**
	 * @return void
	 */
	public function index() {
		$availableRoles = (new TinyAuth())->getAvailableRoles();

		$this->set(compact('availableRoles'));
	}

}
