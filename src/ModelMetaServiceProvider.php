<?php namespace DragoshMocrii\ModelMeta;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelMetaServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		//loads migrations
		$this->loadMigrationsFrom( __DIR__ . '/database/migrations' );
		//publishes configuration files
		$this->publishes( [
			__DIR__ . '/config/model_meta.php' => config_path( 'model_meta.php' ),
		] );
		// define the morph map if set in configuration
		$morph_map = config( 'model_meta.morph_map', [] );
		if ( ! empty( $morph_map ) && is_array( $morph_map ) ) {
			Relation::morphMap( $morph_map );
		}
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register() {
		//
	}
}
