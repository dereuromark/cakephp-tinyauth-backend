<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\ResourceAbility> $abilities
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 * @var array<\TinyAuthBackend\Model\Entity\Scope> $scopes
 * @var array $permissions
 */
?>
<table class="w-full text-sm">
	<thead>
		<tr class="border-b-2 border-gray-200 dark:border-slate-600">
			<th class="text-left p-2 font-semibold">Ability</th>
			<?php foreach ($roles as $role) { ?>
			<th class="text-center p-2 font-medium min-w-[100px]">
				<?= h($role->name) ?>
			</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($abilities as $ability) { ?>
		<tr class="border-b border-gray-100 dark:border-slate-700">
			<td class="p-2">
				<?= h($ability->name) ?>
			</td>
			<?php foreach ($roles as $role) { ?>
			<?php
				$perm = $permissions[$ability->id][$role->id] ?? null;
				$type = $perm['type'] ?? 'none';
				$scopeName = $perm['scope_name'] ?? null;
				$scopeId = $perm['scope_id'] ?? null;

				$display = match ($type) {
					'allow' => $scopeName ? "●({$scopeName})" : '●',
					'deny' => '✕',
					default => '○',
				};
				$class = match ($type) {
					'allow' => 'text-green-500',
					'deny' => 'text-red-500',
					default => 'text-gray-400',
				};
			?>
			<td id="rcell-<?= $ability->id ?>-<?= $role->id ?>"
				class="matrix-cell <?= h($class) ?>"
				x-data="{ showMenu: false }"
				@click="showMenu = !showMenu">
				<div class="relative">
					<span><?= h($display) ?></span>

					<!-- Scope selector dropdown -->
					<div x-show="showMenu" @click.away="showMenu = false"
						 class="absolute z-10 mt-1 bg-white dark:bg-slate-800 border rounded-md shadow-lg p-2 text-left min-w-[150px]"
						 style="left: 50%; transform: translateX(-50%);">
						<div class="text-xs font-medium text-gray-500 mb-1">Permission</div>
						<button class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
								hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
								hx-vals='{"ability_id": <?= $ability->id ?>, "role_id": <?= $role->id ?>, "type": "allow", "scope_id": ""}'
								hx-target="#rcell-<?= $ability->id ?>-<?= $role->id ?>"
								hx-swap="outerHTML">
							<span class="text-green-500">●</span> Full access
						</button>
						<?php foreach ($scopes as $scope) { ?>
						<button class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
								hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
								hx-vals='{"ability_id": <?= $ability->id ?>, "role_id": <?= $role->id ?>, "type": "allow", "scope_id": "<?= $scope->id ?>"}'
								hx-target="#rcell-<?= $ability->id ?>-<?= $role->id ?>"
								hx-swap="outerHTML">
							<span class="text-green-500">●</span> <?= h($scope->name) ?>
						</button>
						<?php } ?>
						<button class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
								hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
								hx-vals='{"ability_id": <?= $ability->id ?>, "role_id": <?= $role->id ?>, "type": "deny", "scope_id": ""}'
								hx-target="#rcell-<?= $ability->id ?>-<?= $role->id ?>"
								hx-swap="outerHTML">
							<span class="text-red-500">✕</span> Deny
						</button>
						<button class="block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm"
								hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
								hx-vals='{"ability_id": <?= $ability->id ?>, "role_id": <?= $role->id ?>, "type": "none", "scope_id": ""}'
								hx-target="#rcell-<?= $ability->id ?>-<?= $role->id ?>"
								hx-swap="outerHTML">
							<span class="text-gray-400">○</span> Remove
						</button>
					</div>
				</div>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
