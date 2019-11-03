<?php
/**
 * @var \App\View\AppView $this
 * @var \TinyAuthBackend\Model\Entity\AclRule $aclRule
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Tiny Auth Acl Rules'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="aclRules form large-9 medium-8 columns content">
    <?= $this->Form->create($aclRule) ?>
    <fieldset>
        <legend><?= __('Add Tiny Auth Acl Rule') ?></legend>
        <?php
            echo $this->Form->control('type', ['options' => $aclRule::types()]);
            echo $this->Form->control('role');
            echo $this->Form->control('path');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
