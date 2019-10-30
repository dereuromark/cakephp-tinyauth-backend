<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use TinyAuthBackend\Utility\Config;

class AclController extends AppController {

	/**
	 * @param \Cake\Event\Event $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeFilter(Event $event) {
		if (!Config::isAclEnabled()) {
			$this->Flash->error('Not enabled');
			return $this->redirect(['controller' => 'Auth']);
		}
	}

	/**
	 * @return void
	 */
	public function index() {
	}

}
