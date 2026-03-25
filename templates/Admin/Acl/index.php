<?php
/**
 * @var \Cake\View\View $this
 * @var array $tree
 * @var array<\TinyAuthBackend\Model\Entity\Role> $roles
 * @var \TinyAuthBackend\Model\Entity\TinyauthController|null $selectedController
 * @var array<\TinyAuthBackend\Model\Entity\Action> $actions
 * @var array $permissions
 */
$this->assign('title', 'ACL Permissions');
?>

<div class="flex gap-6">
    <!-- Tree sidebar -->
    <div class="w-64 flex-shrink-0">
        <div class="card p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-sm uppercase text-gray-500 dark:text-gray-400">Controllers</h3>
                <a href="<?= $this->Url->build(['controller' => 'Sync', 'action' => 'controllers']) ?>"
                   class="btn btn-secondary text-xs">Sync</a>
            </div>
            <?= $this->element('TinyAuthBackend.tree', ['tree' => $tree, 'selectedId' => $selectedController?->id]) ?>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-1">
        <?php if ($selectedController) { ?>
        <div class="card">
            <div class="p-4 border-b border-gray-200 dark:border-slate-700">
                <h2 class="text-lg font-semibold"><?= h($selectedController->full_path) ?></h2>
                <p class="text-sm text-gray-500"><?= count($actions) ?> actions - <?= count($roles) ?> roles</p>
            </div>

            <div class="p-4 overflow-x-auto">
                <?= $this->element('TinyAuthBackend.matrix', [
                    'actions' => $actions,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ]) ?>
            </div>

            <!-- Legend -->
            <div class="p-4 border-t border-gray-200 dark:border-slate-700">
                <div class="legend">
                    <span><span class="text-green-500">&#9679;</span> Allowed</span>
                    <span><span class="text-green-500 opacity-50">&#9679;</span> Inherited</span>
                    <span><span class="text-gray-400">&#9675;</span> No permission</span>
                    <span><span class="text-red-500">&#10005;</span> Denied</span>
                </div>
            </div>
        </div>
        <?php } else { ?>
        <div class="card p-8 text-center text-gray-500">
            <p>Select a controller from the tree to manage permissions.</p>
        </div>
        <?php } ?>
    </div>
</div>
