# Lithium Fake Model

*For those times when you don't want a real one*

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
      'to'        => 'Comments',
      'key'       => array('_id' => 'comment_id'),
      // or, you can use an array of foreign keys, e.g. array('comment_ids' => '_id')
      'fieldName' => 'comments',
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

Copyright (c) Blaine Schmeisser

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rightsto use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
