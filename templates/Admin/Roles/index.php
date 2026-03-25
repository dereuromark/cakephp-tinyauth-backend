<?php
/**
 * @var \Cake\View\View $this
 * @var bool $isManaged
 * @var array $hierarchy
 * @var array $roles
 */
$this->assign('title', 'Roles');
?>

<?php if (!$isManaged) { ?>
<!-- External role source - read-only view -->
<div class="card mb-4">
    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
        <p class="text-sm text-blue-800 dark:text-blue-200">
            <strong>External Role Source</strong> - Roles are loaded from your application's configuration.
            To edit roles, update your app's role source.
        </p>
    </div>
</div>

<div class="card">
    <div class="p-4 border-b border-gray-200 dark:border-slate-700">
        <h2 class="text-lg font-semibold">Available Roles</h2>
    </div>

    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 dark:border-slate-700 text-left">
                <th class="p-3">Name</th>
                <th class="p-3">Alias</th>
                <th class="p-3">ID</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role) { ?>
            <tr class="border-b border-gray-100 dark:border-slate-700">
                <td class="p-3"><?= h($role->name) ?></td>
                <td class="p-3"><code class="text-sm"><?= h($role->alias) ?></code></td>
                <td class="p-3 text-gray-500"><?= h($role->id) ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php } else { ?>
<!-- Managed roles - full CRUD UI -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Role hierarchy tree -->
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Role Hierarchy</h2>
            <a href="<?= $this->Url->build(['action' => 'add']) ?>" class="btn btn-primary">+ Add Role</a>
        </div>

        <div class="p-4" x-data="{ dragging: null }">
            <?php
            $renderTree = function ($roles, $level = 0) use (&$renderTree) {
                foreach ($roles as $role) {
            ?>
            <div class="role-item mb-1" style="margin-left: <?= $level * 1.5 ?>rem;"
                 draggable="true"
                 @dragstart="dragging = <?= $role->id ?>"
                 @dragend="dragging = null"
                 @dragover.prevent
                 @drop="reorderRole(<?= $role->id ?>, dragging)">
                <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-100 dark:hover:bg-slate-700 group">
                    <span class="cursor-move text-gray-400">&#8942;&#8942;</span>
                    <span class="flex-1">
                        <strong><?= h($role->name) ?></strong>
                        <span class="text-sm text-gray-500">(<?= h($role->alias) ?>)</span>
                    </span>
                    <span class="opacity-0 group-hover:opacity-100 flex gap-1">
                        <a href="<?= $this->Url->build(['action' => 'edit', $role->id]) ?>"
                           class="text-blue-600 text-sm">Edit</a>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $role->id], [
                            'confirm' => __('Delete this role?'),
                            'class' => 'text-red-600 text-sm',
                            'block' => true,
                        ]) ?>
                    </span>
                </div>
                <?php if (!empty($role->children)) { ?>
                    <?php $renderTree($role->children, $level + 1); ?>
                <?php } ?>
            </div>
            <?php
                }
            };
            $renderTree($hierarchy);
            ?>
        </div>
    </div>

    <!-- Role list table -->
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold">All Roles</h2>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-slate-700 text-left">
                    <th class="p-3">Name</th>
                    <th class="p-3">Alias</th>
                    <th class="p-3">Parent</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role) { ?>
                <tr class="border-b border-gray-100 dark:border-slate-700">
                    <td class="p-3"><?= h($role->name) ?></td>
                    <td class="p-3"><code class="text-sm"><?= h($role->alias) ?></code></td>
                    <td class="p-3"><?= $role->parent_id ? h($role->parent->name ?? '-') : '-' ?></td>
                    <td class="p-3">
                        <a href="<?= $this->Url->build(['action' => 'edit', $role->id]) ?>"
                           class="text-blue-600 text-sm">Edit</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function reorderRole(targetId, sourceId) {
    if (targetId === sourceId) return;

    fetch('<?= $this->Url->build(['action' => 'reorder']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
        },
        body: new URLSearchParams({
            role_id: sourceId,
            parent_id: targetId,
            sort_order: 0
        })
    }).then(() => location.reload());
}
</script>
<?php } ?>
