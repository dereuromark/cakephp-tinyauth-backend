<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;

/**
 * Base controller for TinyAuthBackend Admin controllers.
 */
class AppController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->viewBuilder()->setLayout('TinyAuthBackend.tinyauth');
		$this->loadComponent('Flash');
	}

	/**
	 * Defense-in-depth authorization hook.
	 *
	 * The plugin delegates authentication to the host app — the
	 * `/admin/auth` prefix is expected to be gated at the middleware or
	 * routing layer. This hook adds an optional *authorization* gate so
	 * the plugin can reject authenticated-but-not-privileged users (e.g.
	 * a helpdesk identity that can see the admin area but must not be
	 * able to edit ACL rules).
	 *
	 * Configure a callable under `TinyAuthBackend.editorCheck` that
	 * receives the current identity (may be `null`) and the request, and
	 * returns `true` if the caller is permitted to manage TinyAuth rules.
	 * Returning `false` — or any non-true value — raises a 403.
	 *
	 * ```php
	 * Configure::write('TinyAuthBackend.editorCheck', function ($identity, $request) {
	 *     return $identity !== null && in_array('admin', (array)$identity->roles, true);
	 * });
	 * ```
	 *
	 * When the key is unset the hook is a no-op — existing installs keep
	 * working and continue to rely on the host app's gating.
	 *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
     * @throws \Cake\Http\Exception\ForbiddenException When the configured check rejects the caller.
     * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		$check = Configure::read('TinyAuthBackend.editorCheck');
		if ($check === null) {
			return;
		}
		if (!is_callable($check)) {
			throw new ForbiddenException('TinyAuthBackend.editorCheck must be callable');
		}

		$identity = $this->request->getAttribute('identity');
		if ($check($identity, $this->request) !== true) {
			throw new ForbiddenException('Not authorized to manage TinyAuth rules');
		}
	}

}
