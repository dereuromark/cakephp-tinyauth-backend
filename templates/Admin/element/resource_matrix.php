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

				$display = match ($type) {
					'allow' => $scopeName ? '●(' . h($scopeName) . ')' : '●',
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
				data-menu-scope>
				<div class="relative">
					<button type="button"
							class="block w-full text-center"
							data-menu-toggle
							aria-haspopup="true">
						<span><?= h($display) ?></span>
					</button>
					<?= $this->element('TinyAuthBackend.resource_matrix_menu', [
						'abilityId' => $ability->id,
						'roleId' => $role->id,
						'scopes' => $scopes,
					]) ?>
				</div>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
