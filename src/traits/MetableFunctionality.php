<?php
namespace DragoshMocrii\ModelMeta\Traits;

use DragoshMocrii\ModelMeta\Models\ModelMeta;
use Illuminate\Support\Facades\DB;

trait MetableFunctionality {

	/**
	 * Meta currently set or cached for the current model
	 * @var array
	 */
	protected $_active_meta = [];

	/**
	 * If the metaAll() method was once called, the flag below helps to use the cache
	 * @var bool
	 */
	protected $_all_meta_fetched_flag = false;

	/**
	 * Connect to ModelMeta model
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function meta() {
		return $this->morphMany( ModelMeta::class, 'metable' );
	}

	/**
	 * Booting functionality of the trait
	 */
	public static function bootMetableFunctionality() {
		static::saved( function ( $model ) {
			$model->metaCommit( $model->_active_meta );
		} );
	}

	/**
	 * Set one meta value for the current model.
	 * Set force to false to insert the value to DB when the parent model is saved.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param bool $force
	 *
	 * @throws \Exception
	 * @return bool
	 */
	public function metaSet( $key, $value, $force = true ) {
		if ( ! is_string( $key ) ) {
			throw new \Exception( 'Key parameter should be of type string.' );
		}

		$this->_active_meta[ $key ] = $value;

		if ( true === $force && $this->getKey() !== null ) {
			return $this->metaCommit( [ $key => $value ] );
		} else {
			return true;
		}
	}

	/**
	 * Set multiple meta values for the current model.
	 * Set force to false to insert the values to DB when the parent model is saved.
	 *
	 * @param $values
	 * @param bool $force
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function metaSetMany( $values, $force = true ) {
		if ( ! $this->isValidKeyValArray( $values ) ) {
			throw new \Exception( 'The values parameter is not formatted properly.' );
		}

		foreach ( $values as $key => $val ) {
			$this->_active_meta[ $key ] = $val;
		}

		if ( true === $force && $this->getKey() !== null ) {
			return $this->metaCommit( $values );
		} else {
			return true;
		}
	}

	/**
	 * Gets the meta value(s) by the key(s) for the current model.
	 *
	 * @param string|array $keys
	 * @param null|mixed $default
	 *
	 * @throws \Exception
	 *
	 * @return mixed
	 */
	public function metaGet( $keys, $default = null ) {
		if ( true === config( 'model_meta.preload_on_get', true ) ) {
			$this->metaAll(); //preload meta
		}

		if ( is_string( $keys ) ) {
			if ( isset( $this->_active_meta[ $keys ] ) ) {
				return $this->_active_meta[ $keys ];
			} else {
				$result = $this->meta()->where( 'key', $keys )->first();

				if ( ! $result ) {
					return $default;
				} else {
					$value                       = $this->restoreValueByType( $result->value, $result->value_type );
					$this->_active_meta[ $keys ] = $value;

					return $value;
				}
			}
		} elseif ( is_array( $keys ) ) {
			$values             = [];
			$cached_meta_keys   = array_intersect( $keys, array_keys( $this->_active_meta ) );
			$uncached_meta_keys = array_diff( $keys, array_keys( $this->_active_meta ) );
			$result             = $this->meta()
			                           ->select( 'key', 'value', 'value_type' )
			                           ->whereIn( 'key', $uncached_meta_keys )
			                           ->get()
			                           ->toArray();
			foreach ( $result as $meta ) {
				$values[ $meta[ 'key' ] ]             = $this->restoreValueByType( $meta[ 'value' ], $meta[ 'value_type' ] );
				$this->_active_meta[ $meta[ 'key' ] ] = $values[ $meta[ 'key' ] ];
			}
			unset( $result );
			$not_found_meta_keys = array_diff( $uncached_meta_keys, array_keys( $values ) );
			foreach ( $not_found_meta_keys as $nfkey ) {
				$values[ $nfkey ] = null;
			}
			unset( $not_found_meta_keys, $uncached_meta_keys );
			foreach ( $cached_meta_keys as $ckey ) {
				$values[ $ckey ] = $this->_active_meta[ $ckey ];
			}
			unset( $cached_meta_keys );

			return $values;
		} else {
			throw new \Exception( 'Keys parameter should be either string or array.' );
		}
	}

	/**
	 * Gets all meta values for the current model.
	 *
	 * @return array
	 */
	public function metaAll() {
		if ( true === $this->_all_meta_fetched_flag ) {
			return $this->_active_meta; //use cache instead
		}

		$result = $this->meta()->select( 'key', 'value', 'value_type' )->get()->toArray();

		$return = [];

		foreach ( $result as $meta ) {
			$return[ $meta[ 'key' ] ] = $this->restoreValueByType( $meta[ 'value' ], $meta[ 'value_type' ] );
		}

		$return = array_merge( $return, $this->_active_meta );

		//cache meta
		$this->_active_meta = $return;

		//set flag to use cache next time
		$this->_all_meta_fetched_flag = true;

		return $return;
	}

	/**
	 * Removes existing meta value(s) for the current model. This writes changes to DB instantaneously.
	 *
	 * @param string|array $keys
	 *
	 * @throws \Exception
	 *
	 * @return boolean
	 */
	public function metaRemove( $keys ) {
		switch ( gettype( $keys ) ) {
			case 'string':
				$keys = [ $keys ];
			case 'array':
				$this->meta()->whereIn( 'key', $keys )->delete();
				foreach ( $keys as $key ) {
					if ( isset( $this->_active_meta[ $key ] ) ) {
						unset( $this->_active_meta[ $key ] );
					}
				}
				break;
			default:
				throw new \Exception( 'Keys parameter type must be either string or array.' );
		}

		return true;
	}

	/**
	 * Checks if the meta key(s) are set for the current model. If $return_missing is true, it will return an array of the missing meta keys.
	 *
	 * @param string|array $keys
	 * @param bool $return_missing
	 *
	 * @throws \Exception
	 *
	 * @return bool|array
	 */
	public function metaExists( $keys, $return_missing = false ) {
		if ( is_string( $keys ) ) {
			$result       = $this->meta()->where( 'key', $keys )->exists();
			$exists       = $result;
			$missing_keys = $exists ? [] : [ $keys ];
		} elseif ( is_array( $keys ) ) {
			$result       = $this->meta()->select( 'key' )->distinct()->whereIn( 'key', $keys )->pluck( 'key' );
			$missing_keys = array_diff( $keys, $result->toArray() );
			$exists       = count( $missing_keys ) == 0 ? true : false;
		} else {
			throw new \Exception( 'Keys parameter should be either string or array.' );
		}

		if ( true === $return_missing ) {
			return array_values( $missing_keys );
		} else {
			return $exists;
		}
	}

	/**
	 * Check whether the values parameter is a valid array of key/value pairs.
	 *
	 * @param array $values
	 *
	 * @return bool
	 */
	protected function isValidKeyValArray( $values ) {
		if ( is_array( $values ) ) {
			foreach ( $values as $key => $val ) {
				if ( ! is_string( $key ) ) {
					return false;
				}
			}

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns storable value
	 *
	 * @param $value
	 *
	 * @return array
	 */
	protected function getStorableValue( $value ) {
		$value_type = gettype( $value );
		switch ( $value_type ) {
			case 'integer':
			case 'double':
			case 'string':
				return [ 'value' => strval( $value ), 'value_type' => $value_type ];
			case 'boolean':
				return [ 'value' => strval( intval( $value ) ), 'value_type' => $value_type ];
			case 'array':
				return [ 'value' => json_encode( $value ), 'value_type' => $value_type ];
			case 'object':
				return [ 'value' => serialize( $value ), 'value_type' => $value_type ];
			default:
				return [ 'value' => null, 'value_type' => 'null' ];
		}
	}

	/**
	 * Restores a value by the value type
	 *
	 * @param string $value
	 * @param string $value_type
	 *
	 * @return mixed
	 */
	protected function restoreValueByType( $value, $value_type ) {
		switch ( $value_type ) {
			case 'integer':
			case 'double':
			case 'string':
			case 'boolean':
				settype( $value, $value_type );
				break;
			case 'array':
				$value = json_decode( $value );
				break;
			case 'object':
				$value = unserialize( $value );
				break;
			default:
				$value = null;
		}

		return $value;
	}

	/**
	 * Commits meta that is set on the model to DB.
	 *
	 * @param array $values
	 *
	 * @return bool
	 */
	protected function metaCommit( $values ) {
		$committed = true;
		DB::beginTransaction();
		foreach ( $values as $key => $value ) {
			$storable_value = $this->getStorableValue( $value );
			$result         = $this->meta()->updateOrCreate( [ 'key' => $key ], [
				'key'        => $key,
				'value'      => $storable_value[ 'value' ],
				'value_type' => $storable_value[ 'value_type' ]
			] );
			if ( is_null( $result ) ) {
				DB::rollBack();
				$committed = false;
			}
		}
		DB::commit();

		return $committed;
	}

}