<?php

namespace TinyAuthBackend\Test\TestCase\Utility;

use Cake\TestSuite\TestCase;
use TinyAuthBackend\Utility\RulePath;

class RulePathTest extends TestCase {

	/**
	 * @return void
	 */
	public function testParse() {
		$path = 'MyController::myAction';
		$result = RulePath::parse($path);

		$expected = [
			'plugin' => null,
			'prefix' => null,
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$this->assertEquals($expected, $result);

		$path = 'MyVendor/MyPlugin.MyPrefix/MySubPrefix/MyController::myAction';
		$result = RulePath::parse($path);

		$expected = [
			'plugin' => 'MyVendor/MyPlugin',
			'prefix' => 'my_prefix/my_sub_prefix',
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testBuild() {
		$array = [
			'plugin' => null,
			'prefix' => null,
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$result = RulePath::build($array);
		$expected = 'MyController::myAction';
		$this->assertSame($expected, $result);

		$array = [
			'plugin' => 'MyVendor/MyPlugin',
			'prefix' => 'MyPrefix/MySubPrefix',
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$result = RulePath::build($array);
		$expected = 'MyVendor/MyPlugin.MyPrefix/MySubPrefix/MyController::myAction';
		$this->assertSame($expected, $result);
	}

	/**
	 * @return void
	 */
	public function testKey() {
		$array = [
			'plugin' => null,
			'prefix' => null,
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$result = RulePath::key($array);
		$expected = 'MyController';
		$this->assertSame($expected, $result);

		$array = [
			'plugin' => 'MyVendor/MyPlugin',
			'prefix' => 'MyPrefix/MySubPrefix',
			'controller' => 'MyController',
			'action' => 'myAction',
		];
		$result = RulePath::key($array);
		$expected = 'MyVendor/MyPlugin.my_prefix/my_sub_prefix/MyController';
		$this->assertSame($expected, $result);
	}

}
