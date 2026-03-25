<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\Action> $actions
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 * @var array $permissions
 */
?>
<table class="w-full text-sm">
	<thead>
		<tr class="border-b-2 border-gray-200 dark:border-slate-600">
			<th class="text-left p-2 font-semibold">Action</th>
			<?php foreach ($roles as $role) { ?>
			<th class="text-center p-2 font-medium min-w-[80px]">
				<div><?= h($role->name) ?></div>
				<div class="text-xs text-gray-400 font-normal">
					<?= $role->parent_id ? '&larr; ' . h($role->parent?->alias ?? 'parent') : 'root' ?>
				</div>
			</th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($actions as $action) { ?>
		<tr class="border-b border-gray-100 dark:border-slate-700">
			<td class="p-2"><?= h($action->name) ?></td>
			<?php foreach ($roles as $role) { ?>
				<?php
				$type = $permissions[$action->id][$role->id] ?? 'none';
				$symbol = match ($type) {
					'allow' => '&#9679;',
					'deny' => '&#10005;',
					default => '&#9675;',
				};
	?>
			<td id="cell-<?= $action->id ?>-<?= $role->id ?>"
				class="matrix-cell <?= $type ?>"
				onclick="window.TinyAuth.togglePermission(<?= $action->id ?>, <?= $role->id ?>, '<?= $type ?>')">
				<?= $symbol ?>
			</td>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
