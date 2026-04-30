<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Log\Log;
use Closure;
use Throwable;

/**
 * Base controller for TinyAuthBackend Admin controllers.
 *
 * The admin UI manages authorization rules — accidental exposure is
 * RCE-equivalent (an attacker can grant themselves access to anything),
 * so the default policy is **deny**. The host application MUST set
 * `TinyAuthBackend.adminAccess` to a `Closure` that receives the current
 * request and returns literal `true` to grant access; anything else
 * (unset, non-Closure, returns false, returns a truthy non-bool, or
 * throws) yields a 403.
 *
 * ```php
 * Configure::write('TinyAuthBackend.adminAccess', function (\Cake\Http\ServerRequest $request): bool {
 *     $identity = $request->getAttribute('identity');
 *     return $identity !== null && in_array('admin', (array)$identity->roles, true);
 * });
 * ```
 *
 * The legacy `TinyAuthBackend.editorCheck` callable (signature
 * `function ($identity, $request): bool`) is still honored for
 * backward compatibility but is **deprecated** and emits a
 * deprecation warning when used. Migrate to `adminAccess`.
 *
 * Precedence: `adminAccess` is checked first; if unset the legacy
 * `editorCheck` is consulted; if neither is set the request is
 * denied.
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
	 * Default-deny access gate.
	 *
	 * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event
	 * @throws \Cake\Http\Exception\ForbiddenException When access is denied or unconfigured.
	 * @return void
	 */
	public function beforeFilter(EventInterface $event): void {
		parent::beforeFilter($event);

		// Coexist with cakephp/authorization: this gate IS the authorization
		// decision for the TinyAuthBackend admin, so silence the policy check.
		if ($this->components()->has('Authorization') && method_exists($this->components()->get('Authorization'), 'skipAuthorization')) {
			$this->components()->get('Authorization')->skipAuthorization();
		}

		$adminAccess = Configure::read('TinyAuthBackend.adminAccess');
		if ($adminAccess instanceof Closure) {
			$request = $this->request;
			$this->runGate(static fn () => $adminAccess($request));

			return;
		}
		if ($adminAccess !== null) {
			throw new ForbiddenException('TinyAuthBackend.adminAccess must be a Closure');
		}

		$editorCheck = Configure::read('TinyAuthBackend.editorCheck');
		if ($editorCheck !== null) {
			if (!is_callable($editorCheck)) {
				throw new ForbiddenException('TinyAuthBackend.editorCheck must be callable');
			}
			deprecationWarning(
				'3.2.0',
				'TinyAuthBackend.editorCheck is deprecated, use TinyAuthBackend.adminAccess (Closure receiving only the request) instead.',
			);
			$identity = $this->request->getAttribute('identity');
			$request = $this->request;
			$this->runGate(static fn () => $editorCheck($identity, $request));

			return;
		}

		throw new ForbiddenException(__d(
			'tinyauth_backend',
			'TinyAuthBackend admin backend is not configured. Set TinyAuthBackend.adminAccess to a Closure that returns true for permitted callers.',
		));
	}

	/**
	 * Run the gate Closure, normalising every non-true outcome to a 403 and
	 * logging unexpected exceptions instead of leaking them to the client.
	 *
	 * @param \Closure $gate
	 * @throws \Cake\Http\Exception\ForbiddenException
	 * @return void
	 */
	private function runGate(Closure $gate): void {
		try {
			$allowed = $gate() === true;
		} catch (ForbiddenException $e) {
			// Caller explicitly chose the 403 path — respect it.
			throw $e;
		} catch (Throwable $e) {
			// Convert any other failure (broken callable, transient DB
			// error in a role lookup, etc.) to a generic 403. Logging
			// the concrete exception class + message lets operators
			// diagnose it without leaking a stack trace to the client.
			Log::warning(sprintf(
				'TinyAuthBackend admin gate threw %s: %s',
				$e::class,
				$e->getMessage(),
			));

			throw new ForbiddenException('Not authorized to manage TinyAuth rules');
		}

		if (!$allowed) {
			throw new ForbiddenException('Not authorized to manage TinyAuth rules');
		}
	}

}
