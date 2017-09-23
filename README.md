# Auto registration and configuration of services for Nette DI

[![Build Status](https://travis-ci.org/fmasa/auto-di.svg?branch=master)](https://travis-ci.org/fmasa/auto-di)
[![Coverage Status](https://coveralls.io/repos/github/fmasa/auto-di/badge.svg?branch=master)](https://coveralls.io/github/fmasa/auto-di?branch=master)

`fmasa\autoDI` is package intended to make registration and configuration
of services easier.

## Installation
The best way to install fmasa/auto-di is using [Composer](https://getcomposer.org/):

    $ composer require fmasa/auto-di


To enable auto registration register extension in your `config.neon`:

```yaml
extensions:
    autoDI: Fmasa\AutoDI\DI\AutoDIExtension
```

## Pattern based definition


`autoDI` registers services defined by regex:

```yaml
autoDI:
    services:
        - class: App\Model\**\*Repository
```
This registers every class under namespace `App\Model` which name ends with `Repository`:

- App\Model\Eshop\UserRepository
- App\Model\CMS\Comments\CommentsRepository

There are several simple operators that can be used in patterns:

- `*` matches class name, one namespace level or part of it (without \)
- `**` matches any part of namespace or classname (including \)
- `{Eshop,CMS}` options list, any item of list matches this pattern

Apart from these, any PCRE regular expression can be used.

## Classes and generated factories

Package supports both classes and [generated factories](https://doc.nette.org/en/2.4/di-usage#toc-component-factory).

Classes are matched agains `class` field, factories againts `implement` field,
which corresponds to way Nette use these fields.

When using `class` field, all matching interfaces are skipped and vice versa.

```yaml
autoDI:
    services:
        # Repositories
        - class: App\Model\**\*Repository 
        
        # Component factories
        - implement: App\Components\very**\I*Factory
```

## Tags, autowiring, ...

Every option supported in DI (tags, inject, autowiring, ...) is supported with same syntax
as normal service registration

```yaml
autoDI:
    services:
        # Repositories
        - class: App\Model\Subscribers\**
          tags: [eventBus.subscriber]
```

The snippet above registers all classes in `App\Model\Subscribers` namespace
with `eventBus.subscriber` tag.

## Exclude services

Sometimes we wan't to exlude certain services from registration. For that we can use `exclude` field,
that accepts pattern or list of patterns:

```yaml
autoDI:
    services:
        - class: App\Model\**
          exclude: App\Model\{Entities,DTO}**
```

which is same as

```yaml
autoDI:
    services:
        - class: App\Model\**
          exclude:
              - App\Model\Entities**
              - App\Model\DTO**
```

## Already registered services

When extension founds service, that is already registered
(by `services` section, different extension or previous `autoDI` definition), **it's skipped**.

This allows manual registration of specific services that need specific configuration.

## Defaults section

To specify base configuration for all services registered via `autoDI`, `defaults` section
can be used:

```yaml
autoDI:
    defaults:
        tags: [ my.auto.service ]

    services:
        # theese services will have tag my.auto.service
        - class: App\Model\Repositories\**
        
        # theese services will have only tag eventBus.subscriber 
        - class: app\Model\Subscribers\**
          tags: [ eventBus.subscriber ]
```

## Configuring directories

By default extension searches for services in `%appDir%`, but other directories can be specified:

```yaml
autoDI:
    directories:
        - %appDir%
        - %appDir%/../vendor
```
