# Custom Objects plugin for Mautic

Allows Mautic users to define custom object types with custom fields. Then create multiple custom objects of each type and associate them to the Contact or Company entities.

You'll be able to 
- filter segments by values in the custom objects
- create campaign conditions based on the values in the custom objects

## Requirements

- Plugin supports PHP 7.1+.

## Terms

### Custom Fields

Defines type of a field value like text, date, number, ... and properties like default value, is required, options, ...

### Custom Object

Defines a type of objects with same properties (fields). Imagine it as a blueprint for physical object. It has singular and plural name to make the UI smoother. But the most important Custom Object definition is list of Custom Fields. Examples: Product, Order, House.

### Custom Item

Custom Item is an instance of a Custom Object and defines 1 concrete thing. A Custom Item holds values of Custom Fields defined in Custom Object. Examples: Toaster, Order 1234, White House.

## Configuration

### `custom_objects_enabled`

If you need to turn off the plugin without removing the plugin completelly, place `custom_objects_enabled => false` to your `app/config/local.php`.

If the plugin is disabled before the plugin is installed then the row to the `plugins` table will be created, but the tables that the plugin normally creates on install won't be created. When you enable the plugin again you have to delete the row in the `plugins` table and then hit the install button/command again. The tables will be created.

## Commands

### `mautic:customobjects:generatesampledata --object-id=X --limit=Y`

The main purpose of this command is to generate big enough sample data to perform performance tests. Will generate Y randomly-named custom items for an existing custom object X. It will put some random value to all its cusom fields and it will generate 0 to 10 randomly-named contacts and connects them to the custom item.

## Unit tests

With this command you can run the plugin unit tests:

`$ bin/phpunit --bootstrap vendor/autoload.php --configuration app/phpunit.xml.dist --filter CustomObjectsBundle`

*Run it from Mautic root folder*
