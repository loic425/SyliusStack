# Conflicts

This document explains why certain conflicts were added to `composer.json` and references related issues.

## twig/twig 3.29.*

**Added:** 2026-09-21

**Reason:** Twig 3.29.0 changed `TemplateWrapper::unwrap()` to require an
`Environment $env` argument (twigphp/Twig#4935). `symfony/twig-bridge`'s
`TwigRendererEngine` still calls it with no arguments when loading form
themes, so every form render throws `ArgumentCountError`. `sylius/mailer-bundle`
had the same call in `EmailTwigAdapter`. 

**Remove when:** releases of `symfony/twig-bridge` and `sylius/mailer-bundle`
shipping the fix are available (see Sylius/SyliusMailerBundle#292,
symfony/symfony#66062).
