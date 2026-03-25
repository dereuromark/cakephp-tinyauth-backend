<?php
/**
 * @var \Cake\View\View $this
 * @var array $diff
 */
$this->assign('title', 'Sync Controllers');
$newCount = count(array_filter($diff, fn ($d) => $d['status'] === 'new'));
?>

<div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold">Sync Controllers</h2>
            <p class="text-sm text-gray-500">Found <?= $newCount ?> new controller(s) to add.</p>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white dark:bg-slate-800">
                    <tr class="border-b border-gray-200 dark:border-slate-700">
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Controller</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diff as $item) { ?>
                    <tr class="border-b border-gray-100 dark:border-slate-700">
                        <td class="p-3">
                            <?php if ($item['status'] === 'new') { ?>
                            <span class="text-green-600 text-xs font-medium">+ NEW</span>
                            <?php } else { ?>
                            <span class="text-gray-400 text-xs">existing</span>
                            <?php } ?>
                        </td>
                        <td class="p-3">
                            <?php
							$path = ($item['plugin'] ? $item['plugin'] . '.' : '')
									. ($item['prefix'] ? $item['prefix'] . '/' : '')
									. $item['name'];
							?>
                            <?= h($path) ?>
                        </td>
                        <td class="p-3 text-gray-500">
                            <?= h(implode(', ', $item['actions'])) ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800">
            <?= $this->Form->create(null) ?>
            <div class="flex items-center gap-4 mb-4">
                <label class="flex items-center gap-2">
                    <?= $this->Form->checkbox('add_new', ['checked' => true]) ?>
                    <span class="text-sm">Add new controllers</span>
                </label>
                <label class="flex items-center gap-2">
                    <?= $this->Form->checkbox('add_actions', ['checked' => true]) ?>
                    <span class="text-sm">Add new actions to existing</span>
                </label>
            </div>
            <div class="flex gap-2">
                <?= $this->Form->button(__('Sync Now'), ['class' => 'btn btn-primary']) ?>
                <a href="<?= $this->Url->build(['controller' => 'Acl', 'action' => 'index']) ?>"
                   class="btn btn-secondary">Cancel</a>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
