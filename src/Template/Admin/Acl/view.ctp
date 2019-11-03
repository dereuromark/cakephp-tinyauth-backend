<?php
/**
 * @var \App\View\AppView $this
 * @var \TinyAuthBackend\Model\Entity\AclRule $aclRule
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Tiny Auth Acl Rule'), ['action' => 'edit', $aclRule->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Tiny Auth Acl Rule'), ['action' => 'delete', $aclRule->id], ['confirm' => __('Are you sure you want to delete # {0}?', $aclRule->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Tiny Auth Acl Rules'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Tiny Auth Acl Rule'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="aclRules view large-9 medium-8 columns content">
    <h3><?= h($aclRule->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Role') ?></th>
            <td><?= h($aclRule->role) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Path') ?></th>
            <td><?= h($aclRule->path) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($aclRule->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Type') ?></th>
            <td><?= $this->Number->format($aclRule->type) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($aclRule->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($aclRule->modified) ?></td>
        </tr>
    </table>
</div>
