# Lithium Fake Model

*For those times when you don't want a real one*

This library is for the PHP Lithium Web Framework and provides an alternative to its built-in data models.

It doesn't do much, but that's kinda the point. Here's a comparison:

|                          | Lithium Models | Fake Models |
| ------------------------ | -------------- | ----------- |
| Dirty Attributes         | yes            | no          |
| Filterable (AOP)         | yes            | no          |
| Schema Definition        | yes            | no          |
| Uses LI3 Data Sources    | yes            | yes         |
| Works with SQL and Mongo | yes            | maybe [1]   |

[1] I've only tested with MongoDB, but Fake Models could be adjusted to work with a SQL data source if someone wants to make the effort.

## Then why would I use this?

You probably shouldn't. I mean, Fake Models do basically nothing! No one in their right mind would make the switch.

OK, to be honest, Lithium Models are bloated and they're slow. They store lots of redundant data, and all the filterable methods (I suspect) contribute to their slowness.

Here are some benchmarks:

TODO