# dentalink

Symfony project for bidirectional data synchronization between Dentalink and Simla (RetailCRM).

## Overview

- Dentalink -> Simla synchronization:
  - patients -> CRM customers
  - appointments (citas) -> CRM orders
  - payments -> CRM payments
- Simla -> Dentalink reverse synchronization:
  - customer and order changes from CRM history
  - saving `externalId` after entities are created in Dentalink
- `GET /` web page to view Dentalink reference data (statuses, doctors, treatments, clinics, etc.).

## Main Commands

```bash
php bin/console sync
php bin/console backsync
```

Both commands use a lock (`LockableTrait`) to prevent parallel execution.

## Core Logic

- `sync`:
  - reads changes from Dentalink since the last saved timestamp (`SinceDateTime`);
  - transforms data (`Transformer`) and creates/updates customers and orders in Simla.
- `backsync`:
  - reads Simla change history from the last processed ID (`SinceId`);
  - aggregates final changes by entity;
  - creates/updates patients and citas in Dentalink;
  - writes `externalId` back to Simla.

## Configuration

The project uses `.env`/`.env.local`. Keep real credentials in `.env.local`.

Main variables:

- `APP_ENV`, `APP_SECRET`
- `CRM_API_URL`, `CRM_API_KEY`, `CRM_SITE_CODE`, `CRM_PAYMENT_TYPE`
- `CRM_DENTALINK_ID_FIELD`
- `DENTALINK_API_TOKEN`
- `STATUS_MAPPING`, `CUSTOM_FIELDS`
- `LOCK_DSN`

## Sync Cursor Storage

Synchronization state is stored in files:

- `var/citasSinceDateTime`
- `var/paymentsSinceDateTime`
- `var/customersSinceId`
- `var/ordersSinceId`

## Logs

- Monolog `rotating_file` (up to 30 files)
- path: `var/log/<env>.log`

## Dependencies

Key packages:

- `symfony/framework-bundle` `5.4.*`
- `retailcrm/api-client-php` `~6.0`
- `guzzlehttp/guzzle` `^7.0`
- `symfony/console`, `symfony/lock`, `symfony/monolog-bundle`, `symfony/rate-limiter`, `symfony/twig-bundle`
- `symfony/http-client` + `nyholm/psr7` (for HTTP clients)

## Quick Start

```bash
composer install
php bin/console cache:clear
php -S 127.0.0.1:8000 -t public
```
