<?php
/**
 * @var \Cake\View\View $this
 * @var array $tree
 * @var int|null $selectedId
 */
?>
<div x-data="{ expanded: {} }">
    <?php foreach ($tree as $plugin => $pluginData) { ?>
    <div class="mb-2">
        <div class="tree-item flex items-center gap-1 cursor-pointer"
             @click="expanded['<?= h($plugin) ?>'] = !expanded['<?= h($plugin) ?>']">
            <span x-text="expanded['<?= h($plugin) ?>'] ? '&#9660;' : '&#9654;'" class="text-xs text-gray-400"></span>
            <span class="font-medium"><?= h($plugin) ?></span>
        </div>

        <div x-show="expanded['<?= h($plugin) ?>']" x-collapse class="ml-4">
            <?php foreach ($pluginData['prefixes'] as $prefix => $prefixData) { ?>
				<?php if ($prefix !== '_root') { ?>
            <div class="mb-1">
                <div class="tree-item flex items-center gap-1 text-sm cursor-pointer"
                     @click="expanded['<?= h($plugin) ?>_<?= h($prefix) ?>'] = !expanded['<?= h($plugin) ?>_<?= h($prefix) ?>']">
                    <span x-text="expanded['<?= h($plugin) ?>_<?= h($prefix) ?>'] ? '&#9660;' : '&#9654;'" class="text-xs text-gray-400"></span>
                    <span><?= h($prefix) ?>/</span>
                </div>

                <div x-show="expanded['<?= h($plugin) ?>_<?= h($prefix) ?>']" x-collapse class="ml-4">
                    <?php foreach ($prefixData['controllers'] as $controller) { ?>
                    <a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
                       class="tree-item block text-sm <?= $controller->id === $selectedId ? 'active' : '' ?>">
                        <?= h($controller->name) ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } else { ?>
					<?php foreach ($prefixData['controllers'] as $controller) { ?>
                <a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
                   class="tree-item block text-sm <?= $controller->id === $selectedId ? 'active' : '' ?>">
						<?= h($controller->name) ?>
                </a>
                <?php } ?>
            <?php } ?>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
