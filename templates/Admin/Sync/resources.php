<?php
/**
 * @var \Cake\View\View $this
 * @var array $diff
 */
$this->assign('title', 'Sync Resources');
$newCount = count(array_filter($diff, fn ($d) => $d['status'] === 'new'));
?>

<div class="max-w-3xl mx-auto">
    <div class="card">
        <div class="p-4 border-b border-gray-200 dark:border-slate-700">
            <h2 class="text-lg font-semibold">Sync Resources (Entities)</h2>
            <p class="text-sm text-gray-500">Found <?= $newCount ?> new entity class(es) to add.</p>
        </div>

        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white dark:bg-slate-800">
                    <tr class="border-b border-gray-200 dark:border-slate-700">
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Resource</th>
                        <th class="p-3 text-left">Entity Class</th>
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
                        <td class="p-3 font-medium"><?= h($item['name']) ?></td>
                        <td class="p-3 text-gray-500">
                            <code class="text-xs"><?= h($item['entity_class']) ?></code>
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
                    <span class="text-sm">Add new resources</span>
                </label>
                <label class="flex items-center gap-2">
                    <?= $this->Form->checkbox('add_abilities', ['checked' => true]) ?>
                    <span class="text-sm">Add default abilities (view, create, edit, delete)</span>
                </label>
            </div>
            <div class="flex gap-2">
                <?= $this->Form->button(__('Sync Now'), ['class' => 'btn btn-primary']) ?>
                <a href="<?= $this->Url->build(['controller' => 'Resources', 'action' => 'index']) ?>"
                   class="btn btn-secondary">Cancel</a>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
