# Laravel Model Meta

## Introduction

The Model Meta package for Laravel allows you to easily store and retrieve meta data for any models. This package is an implementation of the Property Bag pattern, which is supposed to help you deal with the situations when you need to store various model properties (meta), but adding the properties to the model is not a viable option. 

## Installation

### Composer

```bash
composer require dragosmocrii/model-meta
```

### Laravel Provider

Next, you need to add the ModelMetaServiceProvider to your `providers` array in config/app.php :

```php
/*
* Package Service Providers...
*/

DragoshMocrii\ModelMeta\ModelMetaServiceProvider::class,
```

### Migration

The Model Meta needs to set up its table. To do so, run `php artisan migrate`.

## Configuration

### Model Setup

To add the Model Meta functionality, you need to add the `MetableFunctionality` trait to your model like so:

```php
use DragoshMocrii\ModelMeta\Traits\MetableFunctionality;

class Client extends Model {
	use MetableFunctionality;

}
```

## Usage

### Setting meta

``bool metaSet(string $key, mixed $value, bool $force = true)``

> If **$force** is set to **false**, the meta will be saved to DB when the parent model is saved. Otherwise, the meta will be saved instantly.

> **Note**: The **$key** needs to be a **string**, otherwise an Exception will be thrown.

#### Setting meta on a new model

```php
$model = new MetableModel;
$model->metaSet( 'key', 'value' ); //at this time, the meta is not saved to the DB yet, because the model does not have a foreign key set yet
$model->save(); // meta will be saved when the model is saved
```

#### Setting meta on an existing model

```php
$model = MetableModel::findOrFail( 1 );
$model->metaSet( 'key', 'value' ); //this meta will be saved to DB instantly
```

### Setting multiple meta at once

``bool metaSetMany(array $values, bool $force = true)``

> If **$force** is set to **false**, the meta will be saved to DB when the parent model is saved. Otherwise, the meta will be saved instantly.

> **Note**: The **$values** parameter needs to be an associative array, where the key is a **string**. If these conditions are not met, an Exception will be thrown.

```php
$model = new MetableModel;
$model->metaSetMany( [
	'key' => 'value',
	'foo' => 'bar'
] ); //at this time, the meta is not saved to the DB yet, because the model does not have a foreign key set yet
$model->save(); // meta will be saved when the model is saved
```

### Retrieving meta

``mixed metaGet(string|array $keys, null|mixed $default = null)``

> **Note**: The **$default** parameter will have effect when retrieving single meta only.

#### Getting single meta

```php
$model      = MetableModel::findOrFail( 1 );
$meta_value = $model->metaGet( 'foo', 'bar' ); //returns the value of meta[foo] or 'bar' if meta[foo] does not exist
```

#### Getting multiple meta

```php
$model = MetableModel::findOrFail( 1 );
$metas = $model->metaGet( [
	'key',
	'foo'
] ); //will return an associative array containing the values for the respective meta keys. if meta does not exist, it will be assigned with a null value
```

#### Getting all meta

``mixed metaAll()``

```php
$model    = MetableModel::findOrFail( 1 );
$all_meta = $model->metaAll(); //returns an array containing all meta for the current model
```

### Removing meta

``metaRemove(string|array $keys)``

> **Note**: Meta will be removed from the DB instantly, unless the model is missing the foreign key.

#### Removing single meta

```php
$model = MetableModel::findOrFail( 1 );
$model->metaRemove( 'key' );
```

#### Removing multiple meta

```php
$model = MetableModel::findOrFail( 1 );
$model->metaRemove( [ 'key', 'foo' ] );
```

### Check for meta

``bool metaExists(string|array $keys, bool $return_missing = false)``

> If **$return_missing** is **true**, this will return an array containing the keys of missing meta. An empty array will be returned if no meta is missing.

#### Check for single meta

```php
$model       = MetableModel::findOrFail( 1 );
$meta_exists = $model->metaExists( 'key' );
```

#### Check for multiple meta

```php
$model       = MetableModel::findOrFail( 1 );
$meta_exists = $model->metaExists( [
	'key',
	'foo'
] ); //return true/false if $return_missing is false. Otherwise, returns an array containing the keys of the missing meta
```

## Model Meta & Laravel

The model meta uses polymorphic relationships to achieve its functionality. You can keep this in mind if you need to build complex queries.

For example, you can fetch meta for a model, using default Laravel methods:

```php
$model       = MetableModel::findOrFail( 1 );
$metas       = $model->meta()->get()->toArray();
$single_meta = $model->meta()->where( 'key', 'keyname' )->get()->toArray();
```

## Known issues

- Setting metas will do 2 queries for each meta. This is because Model Meta uses the ``updateOrCreate`` Laravel method, which executes 2 queries against the DB.
- At each meta retrieval, a DB query will be executed. Will fix this by eager loading the meta on the model.
- Model Meta uses the class name of the model that uses the MetableFunctionality trait to save the meta to DB. If the class name changes, the metas won't be accessible anymore. This can be fixed by using Laravel's ``Relation::morphMap``. Will implement this in a future update.