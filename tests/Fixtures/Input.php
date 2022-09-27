<?php

declare(strict_types=1);

/**
 * Input Component model for template and data.
 *
 * @since 1.3.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne
 */

namespace PinkCrab\BladeOne\Tests\Fixtures;

use PinkCrab\Perique\Services\View\Component\Component;

class Input extends Component {

	public $name;

	private $id;

	protected $value;

	private $type;


	// constructor
	public function __construct( string $name, string $id, string $value, string $type = 'text' ) {
		$this->name  = $name;
		$this->id    = $id;
		$this->value = $value;
		$this->type  = $type;
	}
}
