<?php namespace DragoshMocrii\ModelMeta\Tests\Stubs\Models;

use DragoshMocrii\ModelMeta\Traits\MetableFunctionality;
use Illuminate\Database\Eloquent\Model;

class MetableModel extends Model {
	use MetableFunctionality;

	protected $table = 'metable_models';
}