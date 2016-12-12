<?php namespace DragoshMocrii\ModelMeta\Tests;

use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase {
	public function testMigrationCreatedModelMetaTable() {
		$this->assertEquals( true, Schema::hasTable( 'model_meta' ) );
	}
}