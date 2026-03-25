<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\Resource> $resources
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 * @var array<\TinyAuthBackend\Model\Entity\Scope> $scopes
 * @var \TinyAuthBackend\Model\Entity\Resource|null $selectedResource
 * @var array<\TinyAuthBackend\Model\Entity\ResourceAbility> $abilities
 * @var array $permissions
 */
$this->assign('title', 'Resource Permissions');
?>

<div class="flex gap-6">
	<!-- Resources sidebar -->
	<div class="w-64 flex-shrink-0">
		<div class="card p-4">
			<div class="flex items-center justify-between mb-4">
				<h3 class="font-semibold text-sm uppercase text-gray-500 dark:text-gray-400">Resources</h3>
				<a href="<?= $this->Url->build(['controller' => 'Sync', 'action' => 'resources']) ?>"
				   class="btn btn-secondary text-xs">↻ Sync</a>
			</div>

			<div class="space-y-1">
				<?php foreach ($resources as $resource) { ?>
				<a href="<?= $this->Url->build(['action' => 'index', '?' => ['resource_id' => $resource->id]]) ?>"
				   class="tree-item block <?= $selectedResource && $selectedResource->id === $resource->id ? 'active' : '' ?>">
					<?= h($resource->name) ?>
					<span class="text-xs text-gray-400">(<?= count($resource->resource_abilities) ?>)</span>
				</a>
				<?php } ?>
			</div>
		</div>
	</div>

	<!-- Main content -->
	<div class="flex-1">
		<?php if ($selectedResource) { ?>
		<div class="card">
			<div class="p-4 border-b border-gray-200 dark:border-slate-700">
				<h2 class="text-lg font-semibold"><?= h($selectedResource->name) ?></h2>
				<p class="text-sm text-gray-500">
					Entity: <?= h($selectedResource->entity_class) ?>
				</p>
			</div>

			<div class="p-4 overflow-x-auto">
				<?= $this->element('TinyAuthBackend.resource_matrix', [
					'abilities' => $abilities,
					'roles' => $roles,
					'scopes' => $scopes,
					'permissions' => $permissions,
				]) ?>
			</div>

			<!-- Add ability form -->
			<div class="p-4 border-t border-gray-200 dark:border-slate-700">
				<?= $this->Form->create(null, ['url' => ['action' => 'addAbility']]) ?>
				<?= $this->Form->hidden('resource_id', ['value' => $selectedResource->id]) ?>
				<div class="flex gap-2">
					<?= $this->Form->text('name', [
						'class' => 'form-input flex-1',
						'placeholder' => 'New ability name (e.g., publish, archive)',
					]) ?>
					<?= $this->Form->button(__('+ Add Ability'), ['class' => 'btn btn-secondary']) ?>
				</div>
				<?= $this->Form->end() ?>
			</div>

			<!-- Scopes reference -->
			<div class="p-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
				<h4 class="text-sm font-medium mb-2">Available Scopes</h4>
				<div class="flex flex-wrap gap-2">
					<?php foreach ($scopes as $scope) { ?>
					<span class="px-2 py-1 text-xs bg-white dark:bg-slate-700 rounded border">
						<?= h($scope->name) ?>
					</span>
					<?php } ?>
					<a href="<?= $this->Url->build(['controller' => 'Scopes', 'action' => 'index']) ?>"
					   class="text-xs text-blue-600">Manage Scopes →</a>
				</div>
			</div>
		</div>
		<?php } else { ?>
		<div class="card p-8 text-center text-gray-500">
			<p>Select a resource to manage entity-level permissions.</p>
		</div>
		<?php } ?>
	</div>
</div>
