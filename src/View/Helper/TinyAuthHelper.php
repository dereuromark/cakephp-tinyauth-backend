<?php
declare(strict_types=1);

namespace TinyAuthBackend\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use TinyAuthBackend\Service\FeatureService;

/**
 * Helper for TinyAuth views.
 *
 * Wraps FeatureService for view layer access without direct service instantiation.
 */
class TinyAuthHelper extends Helper {

	/**
	 * @var \TinyAuthBackend\Service\FeatureService
	 */
	protected FeatureService $featureService;

	/**
	 * @param \Cake\View\View $View The View this helper is being attached to.
	 * @param array<string, mixed> $config Configuration settings for the helper.
	 */
	public function __construct(View $View, array $config = []) {
		parent::__construct($View, $config);

		$featureService = $config['featureService'] ?? null;
		$this->featureService = $featureService ?? new FeatureService();
	}

	/**
	 * Get navigation items for enabled features.
	 *
	 * @return array<array{name: string, label: string, enabled: bool, route: array<string, string>}>
	 */
	public function getNavigationItems(): array {
		return $this->featureService->getNavigationItems();
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * @param string $feature The feature name.
	 * @return bool
	 */
	public function isFeatureEnabled(string $feature): bool {
		return $this->featureService->isEnabled($feature);
	}

}
