<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModelMetaTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create( 'model_meta', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'key' );
			$table->text( 'value' )->nullable();
			$table->string( 'value_type' )->default( 'null' );
			$table->integer( 'metable_id' );
			$table->string( 'metable_type' );
			$table->timestamps();
			//add indexes
			$table->unique( [ 'metable_id', 'metable_type', 'key' ], 'metable_index' );
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists( 'model_meta' );
	}
}
