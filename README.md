# Use whereHas for morphed relationships in Laravel

Usually, you cant say `whereHas('contact')` if `contact` is a morphTo relationship. This package aims to fix that.

<p align="center"> 
<a href="https://packagist.org/packages/rackbeat/laravel-morph-where-has"><img src="https://img.shields.io/packagist/dt/rackbeat/laravel-morph-where-has.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/rackbeat/laravel-morph-where-has"><img src="https://img.shields.io/packagist/v/rackbeat/laravel-morph-where-has.svg?style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/rackbeat/laravel-morph-where-has"><img src="https://img.shields.io/packagist/l/rackbeat/laravel-morph-where-has.svg?style=flat-square" alt="License"></a>
</p>

## Installation

You just require using composer and you're good to go!

```bash
composer require rackbeat/laravel-morph-where-has
```

The Service Provider is automatically registered.

## Usage

### 1. Add possible variations in your model

The problem, is that the morph relationship can have a hard time determining how to handle the `whereHas` call.

Our solution, is that you define every possible morphed class. Like so:

```php
<?php

class Invoice extends Model {
    // Old morph relation
    public function owner() {
        return $this->morphTo('owner'); 
    }
    
    // New solution
    public function customer() {
        return $this->morphTo('owner')->forClass(App\Customer::class);
    }
    
    public function supplier() {
        return $this->morphTo('owner')->forClass(App\Supplier::class);
    }
}
```

### 2. Use whereHas

``` php 
Invoice::whereHas('supplier', function($query) {
    $query->whereName('John Doe');
})->get();
```

This will correctly query a relation with the type and any queries you've added.

## Requirements
* PHP >= 7.1

## Inspiration

Solution based upon work by github@thisdotvoid - modified to fix some common issues.
