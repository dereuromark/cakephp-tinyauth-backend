<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Adds an optional `description` column to tinyauth_acl_permissions so
 * rules can carry a short note explaining why they exist. Rendered in
 * the admin matrix for self-documenting permission setups.
 */
class AddDescriptionToAclPermissions extends BaseMigration {

	/**
	 * @return void
	 */
	public function change(): void {
		$this->table('tinyauth_acl_permissions')
			->addColumn('description', 'string', [
				'limit' => 255,
				'null' => true,
				'default' => null,
				'after' => 'type',
			])
			->update();
	}

}
