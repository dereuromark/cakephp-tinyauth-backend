<?php
/**
 * @var \App\View\AppView $this
 * @var \TinyAuthBackend\Model\Entity\AllowRule $allowRule
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Tiny Auth Allow Rules'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="allowRules form large-9 medium-8 columns content">
    <?= $this->Form->create($allowRule) ?>
    <fieldset>
        <legend><?= __('Add Tiny Auth Allow Rule') ?></legend>
        <?php
            echo $this->Form->control('type', ['options' => $allowRule::types()]);
            echo $this->Form->control('path');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
