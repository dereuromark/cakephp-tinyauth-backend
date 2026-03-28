<?php
/**
 * Standalone flash message element.
 *
 * Renders all flash messages directly from session to avoid
 * style leakage from app templates in standalone layouts.
 *
 * @var \Cake\View\View $this
 */

$flashMessages = $this->getRequest()->getSession()->consume('Flash.flash');
if (!$flashMessages) {
	return;
}

foreach ($flashMessages as $flash) {
	$element = $flash['element'] ?? 'flash/default';
	$classes = match ($element) {
		'flash/success' => 'bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800',
		'flash/error' => 'bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800',
		'flash/warning' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800',
		default => 'bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800',
	};
	$icon = match ($element) {
		'flash/success' => '✓',
		'flash/error' => '✕',
		'flash/warning' => '⚠',
		default => 'ℹ',
	};
	?>
	<div class="<?= $classes ?> border rounded-md px-4 py-3 mb-3 flex items-center gap-3" role="alert">
		<span class="text-lg"><?= $icon ?></span>
		<span class="flex-1"><?= h($flash['message']) ?></span>
	</div>
	<?php
}
