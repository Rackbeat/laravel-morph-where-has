<?php

namespace Rackbeat;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BelongsToMorph extends BelongsTo
{
	/**
	 * The name of the polymorphic relation.
	 *
	 * @var string
	 */
	protected $morphName;

	/**
	 * The type of the polymorphic relation.
	 *
	 * @var string
	 */
	protected $morphType;

	/**
	 * The id of the polymorphic relation.
	 *
	 * @var string
	 */
	protected $morphId;

	public function __construct( Builder $query, Model $parent, $name, $type, $id, $otherKey, $relation ) {
		$this->morphName = $name;
		$this->morphType = $type;
		$this->morphId   = $id;

		parent::__construct( $query, $parent, $id, $otherKey, $relation );
	}

	/**
	 * Define an inverse morph relationship.
	 *
	 * @param Model  $parent
	 * @param string $related
	 * @param string $name
	 * @param string $type
	 * @param string $id
	 * @param string $otherKey
	 * @param string $relation
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public static function build( Model $parent, $related, $name, $type = null, $id = null, $otherKey = null, $relation = null ) {
		// If no relation name was given, we will use this debug backtrace to extract
		// the calling method's name and use that as the relationship name as most
		// of the time this will be what we desire to use for the relationships.
		if ( $relation === null ) {
			[ $current, $caller ] = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 2 );
			$relation = $caller['function'];
		}

		$morphName = Arr::get( array_flip( Relation::morphMap() ), $related, $related );

		[ $type, $id ] = self::getMorphs( Str::snake( $name ), $type, $id );
		$instance = new $related;

		// Once we have the foreign key names, we'll just create a new Eloquent query
		// for the related models and returns the relationship instance which will
		// actually be responsible for retrieving and hydrating every relations.
		$query    = $instance->newQuery();
		$otherKey = $otherKey ?: $instance->getKeyName();

		return new BelongsToMorph( $query, $parent, $morphName, $type, $id, $otherKey, $relation );
	}

	/**
	 * Get the polymorphic relationship columns.
	 *
	 * @param string $name
	 * @param string $type
	 * @param string $id
	 *
	 * @return array
	 */
	protected static function getMorphs( $name, $type, $id ) {
		$type = $type ?: $name . '_type';
		$id   = $id ?: $name . '_id';

		return [ $type, $id ];
	}

	/**
	 * Add the constraints for a relationship query.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getRelationQuery() {
		$table = $this->getParent()->getTable();
		$query = parent::getRelationQuery( $query, $parent, $columns );

		return $query->where( "{$table}.{$this->morphType}", '=', $this->morphName );
	}

	/**
	 * Get the results of the relationship.
	 *
	 * @return mixed
	 */
	public function getResults() {
		if ( $this->getParent()->{$this->morphType} === $this->morphName ) {
			return $this->getQuery()->first();
		}

		return null;
	}

	/**
	 * Add the constraints for an internal relationship existence query.
	 *
	 * Essentially, these queries compare on column names like whereColumn.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param \Illuminate\Database\Eloquent\Builder $parentQuery
	 * @param array|mixed                           $columns
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function getRelationExistenceQuery( Builder $query, Builder $parentQuery, $columns = [ '*' ] ) {
		$parentTable = $this->getParent()->getTable();
		$modelTable  = $this->getModel()->getTable();

		if ( method_exists( $this, 'getOwnerKeyName' ) ) {
			// for Laravel 5.8
			$relationKey = $this->getOwnerKeyName();
		} else {
			$relationKey = $this->getOwnerKey();
		}

		return $query->select( $columns )->whereColumn(
			"{$parentTable}.{$this->morphId}", '=', "{$modelTable}.{$relationKey}"
		)->where(
			"{$parentTable}.{$this->morphType}", '=', \get_class( $this->getModel() )
		);
	}
}
