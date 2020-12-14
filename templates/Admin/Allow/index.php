<?php
/**
 * @var \App\View\AppView $this
 * @var \TinyAuthBackend\Model\Entity\AllowRule[]|\Cake\Collection\CollectionInterface $allowRules
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Tiny Auth Allow Rule'), ['action' => 'add']) ?></li>
    </ul>
</nav>
<div class="allowRules index large-9 medium-8 columns content">
	<h1>Authentication Backend</h1>

    <h2><?= __('Tiny Auth Allow Rules') ?></h2>
    <table class="table list">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                <th scope="col"><?= $this->Paginator->sort('path') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allowRules as $allowRule): ?>
            <tr>
                <td><?= $this->Number->format($allowRule->id) ?></td>
                <td><?= $this->Number->format($allowRule->type) ?></td>
                <td><?= h($allowRule->path) ?></td>
                <td><?= h($allowRule->created) ?></td>
                <td><?= h($allowRule->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $allowRule->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $allowRule->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $allowRule->id], ['confirm' => __('Are you sure you want to delete # {0}?', $allowRule->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
