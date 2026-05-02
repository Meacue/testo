<p align="center">
    <a href="https://github.com/php-testo/testo"><img alt="TESTO"
         src="https://github.com/php-testo/.github/blob/1.x/resources/logo-full.svg?raw=true"
         style="width: 2in; display: block"
    /></a>
</p>

<p align="center">Repeat policy plugin</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://php-testo.github.io)
[![Support on Boosty](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsorship&logo=Boosty&logoColor=white&color=%23F15F2C)](https://boosty.to/roxblnfk)

</div>

<br />

> [!IMPORTANT]
> ## 🪞 This is a read-only mirror.
>
> Active development of the Testo project lives in [**php-testo/testo**](https://github.com/php-testo/testo) under `plugin/repeat/`. This repository is **automatically synchronized** from there on every release.
>
> File issues and pull requests in the [main monorepo](https://github.com/php-testo/testo/issues), not here.

## About

Repeats the execution of a test multiple times within a single run. Useful for surfacing flaky behaviour, catching intermittent regressions, and verifying that an operation produces consistent results across repeated invocations.

The repeat policy is opt-in per test or per test class, and composes naturally with the rest of the Testo plugin ecosystem.

## Install

```bash
composer require --dev testo/repeat
```

[![PHP](https://img.shields.io/packagist/php-v/testo/repeat.svg?style=flat-square&logo=php)](https://packagist.org/packages/testo/repeat)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/testo/repeat.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/testo/repeat)
[![License](https://img.shields.io/packagist/l/testo/repeat.svg?style=flat-square)](https://github.com/php-testo/testo/blob/1.x/LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/testo/repeat.svg?style=flat-square)](https://packagist.org/packages/testo/repeat/stats)
