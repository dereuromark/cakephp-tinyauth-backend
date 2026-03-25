<?php
/**
 * @var \Cake\View\View $this
 * @var int $actionId
 * @var int $roleId
 * @var string $type
 */
$symbol = match ($type) {
	'allow' => '&#9679;',
	'deny' => '&#10005;',
	default => '&#9675;',
};
?>
<td id="cell-<?= $actionId ?>-<?= $roleId ?>"
	class="matrix-cell <?= $type ?>"
	onclick="window.TinyAuth.togglePermission(<?= $actionId ?>, <?= $roleId ?>, '<?= h($type) ?>')">
	<?= $symbol ?>
</td>
