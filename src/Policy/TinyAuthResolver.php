<?php
declare(strict_types=1);

namespace TinyAuthBackend\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Authorization\Policy\ResolverInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/**
 * Resolver that returns a single shared `TinyAuthPolicy` instance
 * for any known resource routed through the Authorization plugin.
 *
 * ## Why this exists
 *
 * Cake's built-in resolvers are each slightly wrong for the TinyAuth
 * flow:
 *
 * - `MapResolver` matches exact class names, which works for entities
 *   passed to `$this->Authorization->authorize($article)` but not for
 *   query objects passed to `$this->Authorization->applyScope($query)`
 *   — the Authorization plugin unwraps the query to its repository
 *   (the table) before calling `getPolicy()`, and `MapResolver` won't
 *   match unless you map the table class too.
 * - `OrmResolver` does convention-based `App\Policy\FooPolicy` lookup,
 *   which doesn't let you point at the plugin's `TinyAuthPolicy`
 *   directly without a thin `src/Policy/` wrapper in every adopting
 *   app.
 *
 * `TinyAuthResolver` short-circuits both: it unwraps `SelectQuery`
 * resources to their repository (and then to the repository's entity
 * class), and returns a single cached `TinyAuthPolicy` instance for
 * every resource it recognizes.
 *
 * ## Modes
 *
 * The resolver supports two opt-in modes:
 *
 * - **Allowlist mode** (default, pass an array of known class names):
 *   returns the shared policy only for listed classes. Unknown
 *   resources raise `MissingPolicyException`, which lets a composite
 *   setup fall through to another resolver.
 * - **Match-all mode** (pass an empty array or omit `$allowedClasses`):
 *   returns the shared policy for every resource. Use this when
 *   every entity in your app should be governed by TinyAuth.
 *
 * Both entity classes and table classes can appear in the allowlist —
 * `Article::class`, `ArticlesTable::class`, or both. Passing a
 * `SelectQuery` is handled transparently: the resolver unwraps it to
 * the repository, checks both the table class and the configured
 * entity class against the allowlist, and uses whichever matches.
 *
 * ## Usage
 *
 * ```php
 * // Application::getAuthorizationService()
 * use TinyAuthBackend\Policy\TinyAuthResolver;
 *
 * $resolver = new TinyAuthResolver([
 *     \App\Model\Entity\Article::class,
 *     \App\Model\Entity\Project::class,
 * ]);
 *
 * return new \Authorization\AuthorizationService($resolver);
 * ```
 *
 * Or, for apps where every entity should go through TinyAuth:
 *
 * ```php
 * $resolver = new TinyAuthResolver(); // match-all
 * ```
 */
class TinyAuthResolver implements ResolverInterface {

	/**
	 * @var array<class-string, true>
	 */
	protected array $allowedClasses;

	/**
	 * @var \TinyAuthBackend\Policy\TinyAuthPolicy|null
	 */
	protected ?TinyAuthPolicy $policy;

	/**
	 * @param array<class-string> $allowedClasses Entity or table class names governed by TinyAuth. Empty array = match all.
	 * @param \TinyAuthBackend\Policy\TinyAuthPolicy|null $policy Optional pre-built policy instance (useful for tests).
	 */
	public function __construct(array $allowedClasses = [], ?TinyAuthPolicy $policy = null) {
		$this->allowedClasses = array_fill_keys($allowedClasses, true);
		$this->policy = $policy;
	}

	/**
	 * @param mixed $resource
	 * @throws \Authorization\Policy\Exception\MissingPolicyException
	 * @return object
	 */
	public function getPolicy(mixed $resource): object {
		$classes = $this->resolveCandidateClasses($resource);
		if ($classes === []) {
			throw new MissingPolicyException([is_object($resource) ? $resource::class : gettype($resource)]);
		}

		if (!$this->isAllowed($classes)) {
			throw new MissingPolicyException($classes);
		}

		return $this->policy ??= new TinyAuthPolicy();
	}

	/**
	 * Extract the candidate class names the allowlist should be
	 * checked against. Entities and plain objects contribute their
	 * own class. Tables contribute both their own class and their
	 * configured entity class. Select queries are unwrapped to their
	 * repository first and then handled as tables.
	 *
	 * @param mixed $resource
	 * @return array<class-string>
	 */
	protected function resolveCandidateClasses(mixed $resource): array {
		if ($resource instanceof SelectQuery) {
			$resource = $resource->getRepository();
		}

		if ($resource instanceof Table) {
			$classes = [$resource::class];
			$entityClass = $resource->getEntityClass();
			if ($entityClass !== $resource::class) {
				$classes[] = $entityClass;
			}

			return $classes;
		}

		if (is_object($resource)) {
			return [$resource::class];
		}

		return [];
	}

	/**
	 * @param array<class-string> $classes
	 * @return bool
	 */
	protected function isAllowed(array $classes): bool {
		if ($this->allowedClasses === []) {
			return true; // Match-all mode.
		}

		foreach ($classes as $class) {
			if (isset($this->allowedClasses[$class])) {
				return true;
			}
		}

		return false;
	}

}
