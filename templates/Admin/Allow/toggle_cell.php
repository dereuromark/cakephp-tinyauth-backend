<?php
/**
 * @var \Cake\View\View $this
 * @var \TinyAuthBackend\Model\Entity\Action $action
 */
?>
<?php if (isset($error)) { ?>
<div class="text-red-600 text-sm p-2"><?= h($error) ?></div>
<?php } else { ?>
<div id="allow-<?= $action->id ?>"
     class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm cursor-pointer
            <?= $action->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300' ?>"
     hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
     hx-vals='{"action_id": <?= $action->id ?>, "is_public": <?= $action->is_public ? 'false' : 'true' ?>}'
     hx-target="#allow-<?= $action->id ?>"
     hx-swap="outerHTML">
    <span><?= $action->is_public ? '&#9679;' : '&#9675;' ?></span>
    <span><?= h($action->name) ?></span>
</div>
<?php }
