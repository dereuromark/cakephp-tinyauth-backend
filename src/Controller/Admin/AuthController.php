<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;

/**
 * @property \TinyAuthBackend\Model\Table\AclRulesTable $AclRules
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class AuthController extends AppController {

	/**
	 * @var string|false
	 */
	public $modelClass = false;

	/**
	 * @return void
	 */
	public function index() {
	}

}
