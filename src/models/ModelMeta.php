<?php
namespace DragoshMocrii\ModelMeta\Models;

use Illuminate\Database\Eloquent\Model;

class ModelMeta extends Model {

	/**
	 * @var string
	 */
	protected $table = 'model_meta';

	/**
	 * @var array
	 */
	protected $fillable = [ 'key', 'value', 'value_type' ];


	/**
	 * Get all the models that use the ModelMeta
	 */
	public function metable() {
		return $this->morphTo();
	}

}