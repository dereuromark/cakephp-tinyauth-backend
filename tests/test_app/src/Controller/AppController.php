<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * Fake AppController for Integration tests.
 */
class AppController extends Controller {

	public function initialize()
	{
		parent::initialize();

		$this->loadComponent('Flash');
	}
}
