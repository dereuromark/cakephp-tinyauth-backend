<?php
/**
 * @var \Cake\View\View $this
 * @var int $abilityId
 * @var int $roleId
 * @var string $type
 * @var \TinyAuthBackend\Model\Entity\Scope|null $scope
 * @var array<\TinyAuthBackend\Model\Entity\Scope> $scopes
 * @var string|null $error
 */
if (isset($error)) { ?>
<td id="rcell-<?= $abilityId ?>-<?= $roleId ?>" class="matrix-cell text-red-600">
	<span title="<?= h($error) ?>">!</span>
</td>
<?php } else {
	$scopeName = $scope?->name;
	$display = match ($type) {
		'allow' => $scopeName ? '●(' . h($scopeName) . ')' : '●',
		'deny' => '✕',
		default => '○',
	};
	$class = match ($type) {
		'allow' => 'text-green-500',
		'deny' => 'text-red-500',
		default => 'text-gray-400',
	};
	?>
<td id="rcell-<?= $abilityId ?>-<?= $roleId ?>"
	class="matrix-cell <?= h($class) ?>"
	data-menu-scope>
	<div class="relative">
		<button type="button"
				class="block w-full text-center"
				data-menu-toggle
				aria-haspopup="true">
			<span><?= h($display) ?></span>
		</button>
		<?= $this->element('TinyAuthBackend.resource_matrix_menu', [
			'abilityId' => $abilityId,
			'roleId' => $roleId,
			'scopes' => $scopes,
		]) ?>
	</div>
</td>
<?php }
