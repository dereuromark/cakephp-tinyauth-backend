<?php
/**
 * @var \Cake\View\View $this
 * @var int $abilityId
 * @var int $roleId
 * @var string $type
 * @var \TinyAuthBackend\Model\Entity\Scope|null $scope
 * @var string|null $error
 */
if (isset($error)) { ?>
<td id="rcell-<?= $abilityId ?>-<?= $roleId ?>" class="matrix-cell text-red-600">
	<span title="<?= h($error) ?>">!</span>
</td>
<?php } else {
$scopeName = $scope?->name;
$display = match ($type) {
	'allow' => $scopeName ? "●({$scopeName})" : '●',
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
	x-data="{ showMenu: false }"
	@click="showMenu = !showMenu">
	<span><?= h($display) ?></span>
</td>
<?php } ?>
