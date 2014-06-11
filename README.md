# Lithium Fake Model

*For those times when you don't want a real one*

[![Build Status](https://travis-ci.org/blainesch/li3_fake_model.svg?branch=master)](https://travis-ci.org/blainesch/li3_fake_model)

This library is for the PHP Lithium Web Framework and provides an alternative to its built-in data models.

It doesn't do much, but that's kinda the point. Here's a comparison:

|                           | Lithium Models | Fake Models |
| ------------------------- | -------------- | ----------- |
| Dirty Attributes          | yes            | no          |
| Filterable (AOP)          | yes            | no          |
| Schema Definition         | yes            | no          |
| Uses LI3 Data Sources     | yes            | yes [1]     |
| Works with SQL and Mongo  | yes            | maybe [2]   |
| Model-level Relationships | no             | yes         |

[1] Sort of. We wrap the native LI3 data source in our own to avoid the Document/DocumentSet madness.

[2] I've only tested with MongoDB, but Fake Models could be adjusted to work with a SQL data source if someone wants to make the effort.

## Then why would I use this?

You probably shouldn't. I mean, Fake Models do basically nothing! No one in their right mind would make the switch.

OK, to be honest, Lithium Models are bloated and they're slow. They store lots of redundant data, and all the filterable methods (I suspect) contribute to their slowness.

Here are some benchmarks...

|             | Count | Lithium Models | Fake Models |
| ----------- | -----:| --------------:| -----------:|
| first()     | 1     | 76ms           | 32ms        |
| all()       | 100   | 2863ms         | 78ms        |

Each test was with 100 iterations (divide each by 100 to get true timing).

## Relationships

Lithium models support relationships at the data source layer, which works fine for relational databases, but not for MongoDB.

Fake models support relationships at the model level and efficiently eager-loads related records by issuing extra queries. Here's how you use it:

*1. Define your model relationships.*

```php
class Posts extends Model {

  public $hasMany = array(
    'Comments' => array(
      'to'        => 'Comments',  // The model
      'key'       => array('_id' => 'comment_id'), // or, you can use an array of foreign keys, e.g. array('comment_ids' => '_id')
      'fieldName' => 'comments',  // The key it's on
      // `order`, `limit`, `conditions` can also be used
    ),
  );

}
```

*2. Query your model and tell it which relationships to fetch.*

```php
$posts = Posts::all(array(
  'with' => array('Comments')
));
```

The above query will:

1. query and fetch all the posts
2. issue a second query to get all the comments
3. connect the child comments onto the appropriate parent posts

Relationships can also provide deep query logic:
```php
$posts = Posts::all(array(
  'with' => array(
    'comments' => array(
      'order' => array('date' => 'asc'),
      'limit' => 10,
      'conditions' => array(
        'flagged' => false,
      ),
    ),
));
```

Relationships can also be nested further, e.g.:

```php
$posts = Posts::all(array(
  'with' => array(
    'Comments' => array('Author')
  )
));
```

This method call still only issues 3 total queries to the Mongo database, yipee!

## Copyright and License
Fake model is licensed under the MIT License - see the `LICENSE` file for details
