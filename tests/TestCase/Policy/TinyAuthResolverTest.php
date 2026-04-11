<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Policy;

use Authorization\Policy\Exception\MissingPolicyException;
use Cake\ORM\Entity;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;
use stdClass;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Policy\TinyAuthPolicy;
use TinyAuthBackend\Policy\TinyAuthResolver;

class TinyAuthResolverTest extends TestCase {

	/**
	 * Build a SelectQuery test double bound to the given Table without
	 * touching the database schema layer. We only need `getRepository()`
	 * to return the table for the resolver to do its unwrapping.
	 *
	 * @param \Cake\ORM\Table $table
	 * @return \Cake\ORM\Query\SelectQuery
	 */
	protected function mockSelectQueryOn(Table $table): SelectQuery {
		$query = $this->createStub(SelectQuery::class);
		$query->method('getRepository')->willReturn($table);

		return $query;
	}

	public function testMatchAllModeReturnsPolicyForAnyEntity(): void {
		$resolver = new TinyAuthResolver();

		$policy = $resolver->getPolicy(new Article());
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testMatchAllModeReturnsSharedInstance(): void {
		$resolver = new TinyAuthResolver();

		$policy1 = $resolver->getPolicy(new Article());
		$policy2 = $resolver->getPolicy(new Article());

		$this->assertSame($policy1, $policy2);
	}

	public function testAllowlistModeReturnsPolicyForListedEntity(): void {
		$resolver = new TinyAuthResolver([Article::class]);

		$policy = $resolver->getPolicy(new Article());
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testAllowlistModeThrowsForUnlistedEntity(): void {
		$resolver = new TinyAuthResolver([Article::class]);

		$this->expectException(MissingPolicyException::class);
		$resolver->getPolicy(new stdClass());
	}

	public function testResolvesTableByTableClass(): void {
		$table = new Table(['alias' => 'Articles']);
		$resolver = new TinyAuthResolver([$table::class]);

		$policy = $resolver->getPolicy($table);
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testResolvesTableByConfiguredEntityClass(): void {
		$table = new Table(['alias' => 'Articles']);
		$table->setEntityClass(Article::class);

		$resolver = new TinyAuthResolver([Article::class]);
		$policy = $resolver->getPolicy($table);
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testResolvesSelectQueryByUnwrappingRepository(): void {
		$table = new Table(['alias' => 'Articles']);
		$table->setEntityClass(Article::class);
		$query = $this->mockSelectQueryOn($table);

		$resolver = new TinyAuthResolver([Article::class]);
		$policy = $resolver->getPolicy($query);
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testResolvesSelectQueryInMatchAllMode(): void {
		$table = new Table(['alias' => 'Articles']);
		$query = $this->mockSelectQueryOn($table);

		$resolver = new TinyAuthResolver();
		$policy = $resolver->getPolicy($query);
		$this->assertInstanceOf(TinyAuthPolicy::class, $policy);
	}

	public function testThrowsForNonObjectResource(): void {
		$resolver = new TinyAuthResolver();

		$this->expectException(MissingPolicyException::class);
		$resolver->getPolicy('string-resource');
	}

	public function testInjectedPolicyIsReturned(): void {
		$injected = new TinyAuthPolicy();
		$resolver = new TinyAuthResolver([], $injected);

		$this->assertSame($injected, $resolver->getPolicy(new Article()));
	}

	public function testAllowlistRejectsEntityWhenOnlyTableClassListed(): void {
		$table = new Table(['alias' => 'Articles']);
		// Only the table class is listed, not the entity class.
		$resolver = new TinyAuthResolver([$table::class]);

		$this->expectException(MissingPolicyException::class);
		$resolver->getPolicy(new Entity());
	}

}
