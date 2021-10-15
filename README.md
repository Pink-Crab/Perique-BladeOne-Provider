# BladeOne_Provider
A BladeOne Provider for the PinkCrab Renderable Interface.



![alt text](https://img.shields.io/badge/Current_Version-1.2.0-green.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)](https://github.com/ellerbrock/open-source-badge/)
![](https://github.com/Pink-Crab/Loader/workflows/GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/Pink-Crab/BladeOne_Provider/branch/master/graph/badge.svg)](https://codecov.io/gh/Pink-Crab/BladeOne_Provider)

For more details please visit our docs.
https://app.gitbook.com/@glynn-quelch/s/pinkcrab/


## Version ##
**Release 1.2.0**

> Supports and tested with the PinkCrab Perique Framework versions 0.5.* -> 1.*

*For support of the initial PinkCrab Plugin Frameworks (version 0.2.\*, 0.3.\* and 0.4.\*) please use BladeOne_Provider 1.0.3*


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
	'*' => array(
		'substitutions' => array(
			Renderable::class => BladeOne_Provider::init( 
				'path/to/templates',
				'path/to/cache'
			),
		),
	),
    ....
````
> If the cache directory doesnt exist, BladeOne will create it for you. It is however best to do this yourself to be sure of permissions etc.

## Dependencies ##
* [BladeOne](https://github.com/EFTEC/BladeOne)

## Requires ##
* [PinkCrab Perique Framework V0.5.0 and above.](https://github.com/Pink-Crab/Perqiue-Framework)


## License ##

### MIT License ###
http://www.opensource.org/licenses/mit-license.html  

## Change Log ##
* 1.2.0 - Comes with boatloader and ability to configure internal blade instance and use custom implementations to add directives, components and config in general
* 1.1.1 - Updated composer.json to support Perique 1.* and set github to run actions on PR & Merge to Dev
* 1.1.0 - Moved to the new Perique naming.
* 1.0.3 - Included the HTML extension by default.
* 1.0.2 - Bumped internal support for version 0.4.* of the Plugin Framework

