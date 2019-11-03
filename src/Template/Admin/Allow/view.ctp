<?php
/**
 * @var \App\View\AppView $this
 * @var \TinyAuthBackend\Model\Entity\AllowRule $allowRule
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Tiny Auth Allow Rule'), ['action' => 'edit', $allowRule->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Tiny Auth Allow Rule'), ['action' => 'delete', $allowRule->id], ['confirm' => __('Are you sure you want to delete # {0}?', $allowRule->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Tiny Auth Allow Rules'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Tiny Auth Allow Rule'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="allowRules view large-9 medium-8 columns content">
    <h3><?= h($allowRule->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Path') ?></th>
            <td><?= h($allowRule->path) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($allowRule->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Type') ?></th>
            <td><?= $this->Number->format($allowRule->type) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($allowRule->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($allowRule->modified) ?></td>
        </tr>
    </table>
</div>
