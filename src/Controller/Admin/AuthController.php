<?php
declare(strict_types=1);

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
	protected ?string $defaultTable = '';

	/**
	 * @return void
	 */
	public function index(): void {
		$availableRoles = (new TinyAuth())->getAvailableRoles();

		$this->set(compact('availableRoles'));
	}

}
