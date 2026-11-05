---
title: AI assistants
sidebar_position: 1.35
---

The docs are plain Markdown, written for people and AI coding assistants alike. The package ships them, so they always match the version you have installed.

| File | What |
| --- | --- |
| `vendor/javaabu/locations/llms.txt` | The rules for using the package, and every page |
| `vendor/javaabu/locations/docs/` | The pages |
| `vendor/javaabu/locations/resources/boost/` | The Laravel Boost skill and guideline |

# Laravel Boost

[Laravel Boost](https://github.com/laravel/boost) finds the skill and guideline in the Composer package by itself. Run this after installing or updating:

```bash
php artisan boost:update
```

The guideline gives the assistant the rules: use the package's models and selects, never hard-code atolls or islands. The skill (`javaabu-locations`) has the full API, examples and common mistakes. It loads when the assistant works on addresses, countries, atolls, islands or the location pages.

# Other assistants

Point them at `vendor/javaabu/locations/llms.txt`.
