# Middleware Filters

Built for [ORM package](https://github.com/frameworkwtf/orm)

## Table of Contents


## Installation

```bash
composer require wtf/middleware-filters
```

Add new middleware to your provider:

```php
<?php

$container['middleware_filters'] = function ($c) {
    return new \Wtf\Middleware\Filters($c);
};
```

Add it to your `suit.php` middleware list:

```php
<?php
//...
'middlewares' => [
//...
    'middleware_filters',
//...
],
```

## Usage

Use [medoo where conditions](https://medoo.in/api/where) in your query, eg:

`GET /?filter[name[~]]=Nich&limit=20&offset=20` => array:

```php
<?php
[
    'name[~]' => 'Nich',
    'LIMIT' => [20,20],
];
```

And inside your code:

```php
<?php

$collection = $this->entity('employee')->loadAll($this->container['filters']);
```
