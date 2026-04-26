<?php
/**
 * @var \Cake\View\View $this
 * @var array $tree
 * @var int|null $selectedId
 */

// Find which nodes should be expanded based on selectedId
$expandedNodes = [];
if ($selectedId !== null) {
	$selectedId = (int)$selectedId;
	foreach ($tree as $plugin => $pluginData) {
		foreach ($pluginData['prefixes'] as $prefix => $prefixData) {
			foreach ($prefixData['controllers'] as $controller) {
				if ($controller->id === $selectedId) {
					$expandedNodes[$plugin] = true;
					if ($prefix !== '_root') {
						$expandedNodes[$plugin . '_' . $prefix] = true;
					}

					break 3;
				}
			}
		}
	}
}
?>
<div class="tree">
	<?php foreach ($tree as $plugin => $pluginData) { ?>
	<details class="tree-node mb-2"<?= isset($expandedNodes[$plugin]) ? ' open' : '' ?>>
		<summary class="tree-item flex items-center gap-1 cursor-pointer">
			<span class="tree-marker text-xs text-gray-400" aria-hidden="true"></span>
			<span class="font-medium"><?= h($plugin) ?></span>
		</summary>

		<div class="ml-4">
			<?php foreach ($pluginData['prefixes'] as $prefix => $prefixData) { ?>
				<?php if ($prefix !== '_root') { ?>
					<?php $key = $plugin . '_' . $prefix; ?>
			<details class="tree-node mb-1"<?= isset($expandedNodes[$key]) ? ' open' : '' ?>>
				<summary class="tree-item flex items-center gap-1 text-sm cursor-pointer">
					<span class="tree-marker text-xs text-gray-400" aria-hidden="true"></span>
					<span><?= h($prefix) ?>/</span>
				</summary>
				<div class="ml-4">
					<?php foreach ($prefixData['controllers'] as $controller) { ?>
					<a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
					   class="tree-item block text-sm <?= (int)$controller->id === $selectedId ? 'active' : '' ?>">
						<?= h($controller->name) ?>
					</a>
					<?php } ?>
				</div>
			</details>
				<?php } else { ?>
					<?php foreach ($prefixData['controllers'] as $controller) { ?>
				<a href="<?= $this->Url->build(['action' => 'index', '?' => ['controller_id' => $controller->id]]) ?>"
				   class="tree-item block text-sm <?= (int)$controller->id === $selectedId ? 'active' : '' ?>">
						<?= h($controller->name) ?>
				</a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</div>
	</details>
	<?php } ?>
</div>
