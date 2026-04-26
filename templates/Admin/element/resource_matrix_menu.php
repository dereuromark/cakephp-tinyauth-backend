<?php
/**
 * Scope-selector menu rendered inside a resource-matrix cell. Shared between
 * the initial matrix render and the HTMX-swap fragment so the cell stays
 * fully functional after a swap.
 *
 * @var \Cake\View\View $this
 * @var int $abilityId
 * @var int $roleId
 * @var array<\TinyAuthBackend\Model\Entity\Scope> $scopes
 */
?>
<div data-menu
	 class="menu-popover absolute z-10 mt-1 bg-white dark:bg-slate-800 border rounded-md shadow-lg p-2 text-left min-w-[150px]"
	 style="left: 50%; transform: translateX(-50%);">
	<div class="text-xs font-medium text-gray-500 mb-1"><?= __('Permission') ?></div>
	<button type="button"
			class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
			hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
			hx-vals='<?= json_encode(['ability_id' => $abilityId, 'role_id' => $roleId, 'type' => 'allow', 'scope_id' => '']) ?>'
			hx-target="#rcell-<?= $abilityId ?>-<?= $roleId ?>"
			hx-swap="outerHTML">
		<span class="text-green-500">●</span> <?= __('Full access') ?>
	</button>
	<?php foreach ($scopes as $scope) { ?>
	<button type="button"
			class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
			hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
			hx-vals='<?= json_encode(['ability_id' => $abilityId, 'role_id' => $roleId, 'type' => 'allow', 'scope_id' => $scope->id]) ?>'
			hx-target="#rcell-<?= $abilityId ?>-<?= $roleId ?>"
			hx-swap="outerHTML">
		<span class="text-green-500">●</span> <?= h($scope->name) ?>
	</button>
	<?php } ?>
	<button type="button"
			class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
			hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
			hx-vals='<?= json_encode(['ability_id' => $abilityId, 'role_id' => $roleId, 'type' => 'deny', 'scope_id' => '']) ?>'
			hx-target="#rcell-<?= $abilityId ?>-<?= $roleId ?>"
			hx-swap="outerHTML">
		<span class="text-red-500">✕</span> <?= __('Deny') ?>
	</button>
	<button type="button"
			class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
			hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
			hx-vals='<?= json_encode(['ability_id' => $abilityId, 'role_id' => $roleId, 'type' => 'none', 'scope_id' => '']) ?>'
			hx-target="#rcell-<?= $abilityId ?>-<?= $roleId ?>"
			hx-swap="outerHTML">
		<span class="text-gray-400">○</span> <?= __('Remove') ?>
	</button>
</div>
