<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\Scope> $scopes
 */
$this->assign('title', 'Scopes');
?>

<div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">Scopes</h2>
                <p class="text-sm text-gray-500">Reusable conditions for entity-level permissions</p>
            </div>
            <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn btn-primary">+ Add Scope</a>
        </div>

        <?php if (empty($scopes)) { ?>
        <div class="p-8 text-center text-gray-500">
            <p>No scopes defined yet. Add common ones like "own" or "department".</p>
        </div>
        <?php } else { ?>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-slate-700 text-left">
                    <th class="p-3">Name</th>
                    <th class="p-3">Description</th>
                    <th class="p-3">Condition</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scopes as $scope) { ?>
                <tr class="border-b border-gray-100 dark:border-slate-700">
                    <td class="p-3">
                        <code class="text-sm bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded">
                            <?= h($scope->name) ?>
                        </code>
                    </td>
                    <td class="p-3 text-gray-600"><?= h($scope->description) ?></td>
                    <td class="p-3">
                        <code class="text-xs text-gray-500">
                            entity.<?= h($scope->entity_field) ?> = user.<?= h($scope->user_field) ?>
                        </code>
                    </td>
                    <td class="p-3">
                        <div class="flex gap-2">
                            <a href="<?= $this->Url->build(['action' => 'edit', $scope->id]) ?>"
                               class="text-blue-600 text-sm">Edit</a>
                            <?= $this->Form->postButton(__('Delete'), ['action' => 'delete', $scope->id], [
                                'class' => 'text-red-600 text-sm bg-transparent border-0 p-0',
                                'form' => [
                                    'class' => 'inline',
                                    'data-confirm-message' => __('Delete this scope? Permissions using it will be updated.'),
                                ],
                            ]) ?>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>

    <!-- Quick reference -->
    <div class="mt-6 card p-4">
        <h3 class="font-medium mb-3">Common Scope Patterns</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <strong>own</strong> - User's own records<br>
                <code class="text-xs text-gray-500">entity.user_id = user.id</code>
            </div>
            <div>
                <strong>department</strong> - Same department<br>
                <code class="text-xs text-gray-500">entity.department_id = user.department_id</code>
            </div>
            <div>
                <strong>team</strong> - Same team<br>
                <code class="text-xs text-gray-500">entity.team_id = user.team_id</code>
            </div>
            <div>
                <strong>organization</strong> - Same organization<br>
                <code class="text-xs text-gray-500">entity.org_id = user.organization_id</code>
            </div>
        </div>
    </div>
</div>
