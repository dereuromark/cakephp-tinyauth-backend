<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\Action> $actions
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 * @var array<int, array<int, array<string, mixed>>> $permissions
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
				<?= $this->element('TinyAuthBackend.acl_cell', ['cell' => $permissions[$action->id][$role->id]]) ?>
			<?php } ?>
		</tr>
		<?php } ?>
	</tbody>
</table>
