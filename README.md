# Service auto registration and configuration for Nette DI

[![Build Status](https://travis-ci.org/fmasa/auto-di.svg?branch=2.x)](https://travis-ci.org/fmasa/auto-di)
[![Coverage Status](https://coveralls.io/repos/github/fmasa/auto-di/badge.svg?branch=2.x)](https://coveralls.io/github/fmasa/auto-di?branch=2.x)

`fmasa\autoDI` is package intended to make registration and configuration
of services easier.

## Future of extension
In Nette 3.0, [SearchExtension](https://doc.nette.org/en/3.0/di-builtin-extensions#toc-searchextension) was introduced.
It builds on the same concept as this extension (in which I stole the concept from Symfony DependencyInjection).
Unfortunately there are still a few problems that need to be fixed in order to fully replace `fmasa/auto-di` (i.e. [nette/di#215](https://github.com/nette/di/issues/215)), but it's on the other hand part of Nette DI core and supports several features, that `fmasa/auto-di` doesn't (yet).
These are ways to register services implementing specific interface or extending specific class.

So long-term goal is to deprecate this extension in favor of SearchExtension, when (and if) SearchExtension reaches feature parity.
Until then I plan to maintain and improve this package since I use it myself in many projects.    

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

- `*` matches class name, one namespace level or part of it (without \\)
- `**` matches any part of namespace or classname (including \\)
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
        # these services will have tag my.auto.service
        - class: App\Model\Repositories\**
        
        # these services will have only tag eventBus.subscriber 
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

## Register services on configuration

Compiler extensions such as AutoDIExtension manipulates the DI container
in two phases (configuration loading and before compilation).
By default this extension registers all services before compilation.
This may not be optimal if you wan't to use this extension with other extensions
such as decorator.

You can enforce registration in configuration phase
by setting `registerOnConfiguration` option to true.
