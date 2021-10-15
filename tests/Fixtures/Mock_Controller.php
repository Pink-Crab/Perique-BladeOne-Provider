<?php

declare(strict_types=1);

/**
 * Blade Config Mock
 *
 * @since 1.1.2
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\BladeOne
 */

namespace PinkCrab\BladeOne\Tests\Fixtures;

use eftec\bladeone\BladeOne;
use PinkCrab\Perique\Services\View\View;

class Mock_Controller {

	public $view;

	public function __construct( View $view ) {
		$this->view = $view;
	}

	public function get_blade(): BladeOne {
		return $this->view->engine()->get_blade();
	}

}
