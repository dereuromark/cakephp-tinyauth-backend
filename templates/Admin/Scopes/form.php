<?php
/**
 * @var \Cake\View\View $this
 * @var \TinyAuthBackend\Model\Entity\Scope $scope
 */
$isEdit = $scope->id !== null;
$this->assign('title', $isEdit ? 'Edit Scope' : 'Add Scope');
?>

<div class="max-w-lg mx-auto">
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold"><?= $isEdit ? 'Edit Scope' : 'Add Scope' ?></h2>
        </div>

        <div class="p-4">
            <?= $this->Form->create($scope) ?>

            <div class="mb-4">
                <?= $this->Form->label('name', 'Name', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('name', ['class' => 'form-input', 'placeholder' => 'own']) ?>
                <p class="text-xs text-gray-500 mt-1">Short identifier shown in permission matrix.</p>
                <?php if ($scope->hasErrors() && $scope->getError('name')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $scope->getError('name'))) ?></p>
                <?php } ?>
            </div>

            <div class="mb-4">
                <?= $this->Form->label('description', 'Description', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('description', ['class' => 'form-input', 'placeholder' => "User's own records"]) ?>
                <?php if ($scope->hasErrors() && $scope->getError('description')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $scope->getError('description'))) ?></p>
                <?php } ?>
            </div>

            <div class="mb-4">
                <?= $this->Form->label('entity_field', 'Entity Field', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('entity_field', ['class' => 'form-input', 'placeholder' => 'user_id']) ?>
                <p class="text-xs text-gray-500 mt-1">Field on the entity to match (e.g., user_id, department_id).</p>
                <?php if ($scope->hasErrors() && $scope->getError('entity_field')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $scope->getError('entity_field'))) ?></p>
                <?php } ?>
            </div>

            <div class="mb-4">
                <?= $this->Form->label('user_field', 'User Field', ['class' => 'block text-sm font-medium mb-1']) ?>
                <?= $this->Form->text('user_field', ['class' => 'form-input', 'placeholder' => 'id']) ?>
                <p class="text-xs text-gray-500 mt-1">Field on the user to compare (e.g., id, department_id).</p>
                <?php if ($scope->hasErrors() && $scope->getError('user_field')) { ?>
                    <p class="text-xs text-red-500 mt-1"><?= h(implode(', ', $scope->getError('user_field'))) ?></p>
                <?php } ?>
            </div>

            <!-- Preview -->
            <div class="mb-4 p-3 bg-gray-50 dark:bg-slate-800 rounded">
                <label class="block text-sm font-medium mb-1">Condition Preview</label>
                <code class="text-sm">
                    entity.<?= h($scope->entity_field ?: 'user_id') ?> = user.<?= h($scope->user_field ?: 'id') ?>
                </code>
            </div>

            <div class="flex gap-3 pt-4">
                <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
                <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary">Cancel</a>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
