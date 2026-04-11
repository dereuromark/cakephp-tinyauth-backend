<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class DropLegacyRuleTables extends BaseMigration {

	/**
	 * @return void
	 */
	public function change(): void {
		if ($this->hasTable('tiny_auth_acl_rules')) {
			$this->table('tiny_auth_acl_rules')->drop()->save();
		}
		if ($this->hasTable('tiny_auth_allow_rules')) {
			$this->table('tiny_auth_allow_rules')->drop()->save();
		}
	}

}
