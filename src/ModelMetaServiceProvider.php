<?php namespace DragoshMocrii\ModelMeta;

use Illuminate\Support\ServiceProvider;

class ModelMetaServiceProvider extends ServiceProvider {
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot() {
		//load migrations
		$this->loadMigrationsFrom( __DIR__ . '/database/migrations' );
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
