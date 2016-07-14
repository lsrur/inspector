<?php
namespace Lsrur\Inspector\Facade;

use Illuminate\Support\Facades\Facade;

class Inspector extends Facade {

	protected static function getFacadeAccessor() { return 'Inspector'; }
}