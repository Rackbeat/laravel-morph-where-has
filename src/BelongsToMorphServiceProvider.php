<?php

namespace Rackbeat;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\ServiceProvider;

class BelongsToMorphServiceProvider extends ServiceProvider
{
	/**
	 * @return void
	 */
	public function boot() {
		MorphTo::macro( 'forClass', function ($class) {
			return BelongsToMorph::build( $this->getParent(), $class, $this->getRelation() );
		} );
	}
}
