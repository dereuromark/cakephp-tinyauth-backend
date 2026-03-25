<?php
/**
 * @var \Cake\View\View $this
 * @var \TinyAuthBackend\Model\Entity\Role $role
 * @var array $parents
 */
$isEdit = $role->id !== null;
$this->assign('title', $isEdit ? 'Edit Role' : 'Add Role');
?>

<div class="max-w-lg mx-auto">
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold"><?= $isEdit ? 'Edit Role' : 'Add Role' ?></h2>
        </div>

        <div class="p-4">
            <?= $this->Form->create($role) ?>
            <?php if ($isEdit) { ?>
                <?= $this->Form->hidden('id') ?>
            <?php } ?>

            <div class="mb-4">
                <?= $this->Form->label('name', 'Display Name', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('name', ['class' => 'form-input', 'placeholder' => 'Administrator']) ?>
                <?php if ($role->hasErrors() && $role->getError('name')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $role->getError('name'))) ?></p>
                <?php } ?>
            </div>

            <div class="mb-4">
                <?= $this->Form->label('alias', 'Alias (slug)', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('alias', ['class' => 'form-input', 'placeholder' => 'admin']) ?>
                <p class="text-xs text-gray-500 mt-1">Used in code and configuration.</p>
                <?php if ($role->hasErrors() && $role->getError('alias')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $role->getError('alias'))) ?></p>
                <?php } ?>
            </div>

			<div class="mb-4">
				<?= $this->Form->label('parent_id', 'Parent Role', ['class' => 'block text-sm font-medium mb-1']) ?>
				<?= $this->Form->select('parent_id', $parents, ['class' => 'form-select', 'empty' => '(No parent - root level)']) ?>
				<p class="text-xs text-gray-500 mt-1">Higher roles inherit permissions from lower roles.</p>
				<?php if ($role->hasErrors() && $role->getError('parent_id')) { ?>
					<p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $role->getError('parent_id'))) ?></p>
				<?php } ?>
			</div>

            <div class="mb-4">
                <?= $this->Form->label('sort_order', 'Sort Order', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->number('sort_order', ['class' => 'form-input', 'default' => 0]) ?>
            </div>

            <div class="flex gap-3 pt-4">
                <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
                <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary">Cancel</a>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
