<?php namespace DragoshMocrii\ModelMeta\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	use DatabaseMigrations;

	public function setUp() {
		parent::setUp();

		$this->migrateUp();
	}

	public function tearDown() {
		$this->migrateDown();

		parent::tearDown();
	}

	protected function getPackageProviders( $app ) {
		return [
			\DragoshMocrii\ModelMeta\ModelMetaServiceProvider::class,
		];
	}

	private function migrateUp() {
		$this->artisan( 'migrate', [
			'--realpath' => realpath( __DIR__ . '/fixtures/migrations' ),
		] );
	}

	private function migrateDown() {
		$this->artisan( 'migrate:rollback', [
			'--realpath' => realpath( __DIR__ . '/fixtures/migrations' ),
		] );
	}
}

