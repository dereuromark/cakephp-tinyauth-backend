<?php
/**
 * @var \Cake\View\View $this
 * @var array $results
 */
$hasResults = !empty($results['controllers']) || !empty($results['actions']) || !empty($results['roles']);
?>
<?php if (!$hasResults) { ?>
<div class="p-4 text-gray-500 text-sm">No results found.</div>
<?php } else { ?>
<div class="divide-y divide-gray-100 dark:divide-slate-700">
    <?php if (!empty($results['controllers'])) { ?>
    <div class="p-2">
        <div class="text-xs font-semibold text-gray-400 uppercase px-2 py-1">Controllers</div>
        <?php foreach ($results['controllers'] as $controller) { ?>
        <a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
           class="block px-2 py-1.5 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm">
            <?= h($controller->full_path) ?>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($results['actions'])) { ?>
    <div class="p-2">
        <div class="text-xs font-semibold text-gray-400 uppercase px-2 py-1">Actions</div>
        <?php foreach ($results['actions'] as $action) { ?>
        <a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $action->controller_id]]) ?>"
           class="block px-2 py-1.5 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm">
            <?= h($action->name) ?>
            <span class="text-gray-400 text-xs"><?= h($action->tinyauth_controller->full_path ?? '') ?></span>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php if (!empty($results['roles'])) { ?>
    <div class="p-2">
        <div class="text-xs font-semibold text-gray-400 uppercase px-2 py-1">Roles</div>
        <?php foreach ($results['roles'] as $role) { ?>
        <a href="<?= $this->Url->build(['controller' => 'Roles', 'action' => 'view', $role->id]) ?>"
           class="block px-2 py-1.5 rounded hover:bg-gray-100 dark:hover:bg-slate-700 text-sm">
            <?= h($role->name) ?>
            <span class="text-gray-400 text-xs"><?= h($role->alias) ?></span>
        </a>
        <?php } ?>
    </div>
    <?php } ?>
</div>
<?php } ?>
