<?php
/**
 * @var \Cake\View\View $this
 * @var array<\TinyAuthBackend\Model\Entity\TinyauthController> $controllers
 * @var string $filter
 */
$this->assign('title', 'Public Actions');
?>

<div class="card">
    <div class="p-4 border-b border-gray-200 dark:border-slate-700 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Public Actions (Allow)</h2>
        <div class="flex gap-2">
            <a href="<?= $this->Url->build(['action' => 'index', '?' => ['filter' => 'all']]) ?>"
               class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="<?= $this->Url->build(['action' => 'index', '?' => ['filter' => 'public']]) ?>"
               class="btn <?= $filter === 'public' ? 'btn-primary' : 'btn-secondary' ?>">Public Only</a>
            <a href="<?= $this->Url->build(['action' => 'index', '?' => ['filter' => 'protected']]) ?>"
               class="btn <?= $filter === 'protected' ? 'btn-primary' : 'btn-secondary' ?>">Protected Only</a>
        </div>
    </div>

    <div class="divide-y divide-gray-100 dark:divide-slate-700">
        <?php foreach ($controllers as $controller) { ?>
			<?php if (empty($controller->actions)) {
				continue;
			} ?>
        <div class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-medium"><?= h($controller->full_path) ?></h3>
                <div class="flex gap-2">
                    <?= $this->Form->postButton(__('Make All Public'), ['action' => 'bulkToggle'], [
						'data' => ['controller_id' => $controller->id, 'is_public' => true],
						'class' => 'text-xs text-blue-600 hover:underline bg-transparent border-0 p-0',
						'form' => [
							'class' => 'inline',
						],
					]) ?>
                    <?= $this->Form->postButton(__('Make All Protected'), ['action' => 'bulkToggle'], [
						'data' => ['controller_id' => $controller->id, 'is_public' => false],
						'class' => 'text-xs text-blue-600 hover:underline bg-transparent border-0 p-0',
						'form' => [
							'class' => 'inline',
						],
					]) ?>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <?php foreach ($controller->actions as $action) { ?>
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
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
