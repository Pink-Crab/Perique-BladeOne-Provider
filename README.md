# BladeOne_Provider
A BladeOne Provider for the PinkCrab Renderable Interface.



![alt text](https://img.shields.io/badge/Current_Version-1.0.0-green.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)](https://github.com/ellerbrock/open-source-badge/)

![alt text](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat " ") 
![alt text](https://img.shields.io/badge/PHPUnit-PASSING-brightgreen.svg?style=flat " ") 
![alt text](https://img.shields.io/badge/PHCBF-WP_Extra-brightgreen.svg?style=flat " ") 

For more details please visit our docs.
https://app.gitbook.com/@glynn-quelch/s/pinkcrab/


## Version ##
**Release 1.0.0**


## Why? ##
The BladeOne implementation of the Renderable interface, allows the use of Blade within the PinkCrab Framework. 

## Setup ##

````bash 
$ composer require pinkcrab/bladeone-provider
````

You will need to either replace the Renderable rules in ````config/dependencies.php```` or define BladeOne as the implenentation on a class by class basis.

````php
// file config/dependencies.php
use PinkCrab\BladeOne\BladeOne_Provider;

return array(
	// Update the Renderable Global Rule.
	'*'               => array(
		'substitutions' => array(
			App::class        => App::get_instance(),
			Renderable::class => BladeOne_Provider::class,
		),
	),

    // If you do not plan on using the PHP_Engine you can remove this. 
	/*PHP_Engine::class => array(
		'shared'          => true,
		'constructParams' => array( App::config( 'path', 'view' ) ),
	), */
    // We define our view and file paths for the instance of BladeOne
    BladeOne_Provider::class => array(
		'shared'          => true,
		'constructParams' => array( App::config( 'path', 'view' ), App::config( 'path', 'upload_root/blade_cache' ) ),
	)
    ....
````
> If the cache directory doesnt exist, BladeOne will create it for you. It is however best to do this yourself to be sure of permissions etc.

## Dependencies ##
* [BladeOne](https://github.com/EFTEC/BladeOne)

## Requires ##
* [PinkCrab Framework V0.2.0 and above.](https://github.com/Pink-Crab/Framework__core)


## License ##

### MIT License ###
http://www.opensource.org/licenses/mit-license.html  

## Change Log ##

