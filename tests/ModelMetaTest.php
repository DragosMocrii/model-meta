<?php namespace DragoshMocrii\ModelMeta\Tests;

use DragoshMocrii\ModelMeta\Tests\Stubs\Models\MetableModel;

class ModelMetaTest extends TestCase {
	public function testCanSetSingleMeta() {
		$metable = new MetableModel;
		$result  = $metable->metaSet( 'foo', 'bar' );

		$this->assertSame( true, $result );

		$result = $metable->meta()->where( [
			[ 'key', 'foo' ],
			[ 'value', 'bar' ]
		] )->count();
		//meta should not be saved to DB if the parent model does not have the ID key set
		$this->assertEquals( 0, $result );

		//meta should be accessed from cache
		$this->assertEquals( 'bar', $metable->metaGet( 'foo' ) );

		//if no meta but default set, should return default
		$this->assertEquals( 'far', $metable->metaGet( 'boo', 'far' ) );

		$metable->save();

		$result = $metable->meta()->where( [
			[ 'key', 'foo' ],
			[ 'value', 'bar' ]
		] )->count();
		$this->assertEquals( 1, $result );
	}

	public function testCanSetSingleMetaForced() {
		$metable = new MetableModel;
		$metable->save();
		//if force is false, meta is not saved instantaneously, but delayed until parent model is saved
		$metable->metaSet( 'foo', 'bar', false );
		$result = $metable->meta()->where( [
			[ 'key', 'foo' ],
			[ 'value', 'bar' ]
		] )->count();
		$this->assertEquals( 0, $result );

		//if force is true, meta is set instantly
		$metable->metaSet( 'boo', 'far', true );
		$result = $metable->meta()->where( [
			[ 'key', 'boo' ],
			[ 'value', 'far' ]
		] )->count();
		$this->assertEquals( 1, $result );
	}

	public function testCanSetManyMeta() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSetMany( [ 'foo' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3' ], true );
		$result = $metable->meta()->count();
		$this->assertEquals( 3, $result );

		$metable = new MetableModel;
		$metable->metaSetMany( [ 'foo' => 'bar1', 'foo2' => 'bar2', 'foo3' => 'bar3' ], false );
		$metable->save();
		$result = $metable->meta()->count();
		$this->assertEquals( 3, $result );
	}

	public function testCanGetMetaSingle() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSet( 'foo', 'bar' );
		$metable->save();
		$this->assertEquals( 'bar', $metable->metaGet( 'foo' ) );

		//test default value on nonexistent meta
		$this->assertEquals( 'bar', $metable->metaGet( 'boo', 'bar' ) );

		//test cached meta
		$metable->metaSet( 'foo1', 'bar1', false );
		$this->assertEquals( 'bar1', $metable->metaGet( 'foo1' ) );

		//test value type
		$metable->metaSet( 'foo2', [ 'foo' => 'bar', 'foo1' => 'bar1' ] );
		$this->assertEquals( [ 'foo' => 'bar', 'foo1' => 'bar1' ], $metable->metaGet( 'foo2' ) );
	}

	public function testCanGetMetaMany() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSetMany( [ 'foo' => 'bar1', 'foo2' => [ 'foo', 'bar' ], 'foo3' => 'bar3' ] );

		//getting all existent from database
		$this->assertEquals( [ 'foo' => 'bar1', 'foo2' => [ 'foo', 'bar' ], 'foo3' => 'bar3' ], $metable->metaGet( [
			'foo',
			'foo2',
			'foo3'
		] ) );

		//getting some existent from cache, some nonexistent
		$this->assertEquals( [ 'foo' => 'bar1', 'foo2' => [ 'foo', 'bar' ], 'foo4' => null ], $metable->metaGet( [
			'foo',
			'foo2',
			'foo4'
		] ) );
	}

	public function testCanGetAllMeta() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSetMany( [ 'foo' => 'bar', 'foo1' => 1, 'foo3' => [ 'foo' ] ] );
		$metable->metaSet( 'foo4', 'bar4' );
		$metable->metaSet( 'foo5', 'bar5', false );
		$all_meta = $metable->metaAll();

		$this->assertEquals( [
			'foo'  => 'bar',
			'foo1' => 1,
			'foo3' => [ 'foo' ],
			'foo4' => 'bar4',
			'foo5' => 'bar5'
		], $all_meta );
	}

	public function testCanRemoveMeta() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSetMany( [ 'foo' => 'bar', 'foo1' => 1, 'foo3' => [ 'foo' ], 'foo4' => 'bar4' ] );

		$metable->metaRemove( 'foo' );
		$all_meta = $metable->metaAll();
		$this->assertEquals( [ 'foo1' => 1, 'foo3' => [ 'foo' ], 'foo4' => 'bar4' ], $all_meta );
		$result = $metable->meta()->count();
		$this->assertEquals( 3, $result );

		$metable->metaRemove( [ 'foo3', 'foo1' ] );
		$all_meta = $metable->metaAll();
		$this->assertEquals( [ 'foo4' => 'bar4' ], $all_meta );
		$result = $metable->meta()->count();
		$this->assertEquals( 1, $result );

		$metable->metaRemove( 'foobar' );
		$all_meta = $metable->metaAll();
		$this->assertEquals( [ 'foo4' => 'bar4' ], $all_meta );
	}

	public function testCanCheckIfMetaExists() {
		$metable = new MetableModel;
		$metable->save();
		$metable->metaSetMany( [ 'foo' => 'bar', 'foo2' => 'bar2', 'foo3' => 'bar3' ] );

		$this->assertEquals( true, $metable->metaExists( 'foo2' ) );
		$this->assertEquals( false, $metable->metaExists( 'foo5' ) );
		$this->assertEquals( true, $metable->metaExists( [ 'foo', 'foo3' ] ) );

		//check if meta exists when one meta exists, but some don't.
		$this->assertEquals( false, $metable->metaExists( [ 'foo', 'foo2', 'foo5' ] ) );

		//check when it should return missing keys
		$this->assertEquals( [ 'foo5' ], $metable->metaExists( [ 'foo', 'foo2', 'foo5' ], true ) );
		$this->assertEquals( [ 'foo5', 'foo6' ], $metable->metaExists( [ 'foo5', 'foo6' ], true ) );
	}
}