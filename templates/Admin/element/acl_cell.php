<?php
/**
 * @var \Cake\View\View $this
 * @var array<string, mixed> $cell
 */
?>
<td id="cell-<?= $cell['action_id'] ?>-<?= $cell['role_id'] ?>"
	class="matrix-cell <?= h($cell['class']) ?>"
	data-state="<?= h($cell['state']) ?>"
	title="<?= h($cell['title']) ?>"
	hx-post="<?= $this->Url->build(['action' => 'toggle']) ?>"
	hx-vals='<?= json_encode(['action_id' => $cell['action_id'], 'role_id' => $cell['role_id'], 'type' => $cell['next_type']]) ?>'
	hx-swap="outerHTML">
	<?= $cell['symbol'] ?>
</td>
