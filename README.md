Phig
====

**phig** provides a convenient way to perform database migrations in PHP. It is  vendor and framework independent and can be used with any PDO driver or database abstraction framework like Doctrine. 

The aims of phig are:

* to be database framework independent: is not necessary to have background on  a particular framework or library to get started.
* provide solid migration mechanism, embeddable to any project

PHP 5.3+ is required.

Installation
------------
**phig** is available on packagist. All you need is to add the following lines in your project `composer.json`:

``` JSON
	{
    	"require": {
        	"softius/phig": "*@dev"
	    }
	}
```
and install via composer:

```
	php composer.phar install
```

An executable will be added under your project `vendor/bin` directory. If you would like to change this setup, please refer to this [guide](http://getcomposer.org/doc/articles/vendor-bins.md#can-vendor-bins-be-installed-somewhere-other-than-vendor-bin-).

Running migrations
------------------

**phig** provides a set of tasks which either execute pending migrations or reversed (rollback) already executed migration steps.

For instance, to execute all pending migrations run:

```
	phig migrate
```

If you would like to reach a particular migration you can specified it via arguments as specified below. If ommited, all pending migrations will be executed.

```
	phig migrate 20121230185208
```

This works for both upwards and downwards migrations. For instance, if target 20121230185208 is greater than the current version, this will trigger upwards migration (call up method on all migrations). On the contrary, if target 20121230185208 is less or equal than the current version, this will trigger downwards migration (call down method on all migrations). For further information on up and down methods, refer to **Writing a migration** section.

### Rolling back

To rollback the last migration step run:

```
	phig rollback
```

If you would like to undo multiple migration steps you can specify it via the `step` argument:

```
	phig rollback 5
```

The above will undo the last 5 migrations. 


### Dump execution

If you would like to view the results of any of the commands above without actually executing them, append the option `--dump`. This will return usefull information about the actions planned to be carried out by the corresponding command. The following examples are all valid:

```
	phig migrate --dump
	phig migrate 20121230185208 --dump
	phig rollback --dump
	phig rollback 3 --dump
```


Writing a migration
--------------------
Migrations are nothing more but classes in PHP, with the following constraints that you should be aware before starting writing code:

* All migrations must be placed in a single directory. 
* Any migration class must extend the `MigratableInterface` and thus the methods `up` and `down`. The `up` method is called when executing an upwards migrations while the `down` method is called on downwards migrations and rollbacks. In other words, the `up` method is called to create a change while `down` method of the same class is called to reverse that change.
* The filename of the migration is important since it is used as a reference hash for **phig** operations. While it is possible to use either datetime or a sequence (integer i.e. build number) or even a versioning scheme, I am suggesting to follow the datetime pattern. 

Here is an example

``` PHP
	// yourproject/migrations/20130104152443-MigrationExample.php
	class MigrationExample implements \Phig\MigratableInterface
	{
		public method up()
		{
			create_table('users');
		}
		
		public method down()
		{
			destroy_table('users');
		}
	}
```

### Using PDO Drivers

The following example illustrates how PDO Migratable abstract class can be used. As you can see no database helpers are provided.

``` PHP
	// yourproject/migrations/20130107193139-PdoMigrationExample.php
	class PdoMigrationExample extends \Phig\Migratable\Pdo
	{
		public method up()
		{
			$this->getConnection()->exec('CREATE TABLE users … ');
		}
		
		public method down()
		{
			$this->getConnection()->exec('DROP TABLE users');
		}
	}
```

TODO
----
* Provide abstract classes for ~~PDO Drivers~~ and Doctrine library
* Improve configuration / add section in documentation
* ~~Allow class names to be defined (and hence discovered) in migration filename i.e. `20121230185208-MigrationExample` (instead of `20121230185208.php`)~~
* Support more than one migration folder
* Add more examples in section *Writing a migration*



