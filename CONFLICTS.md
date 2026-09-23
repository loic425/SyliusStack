# Conflicts

This document explains why certain conflicts were added to `composer.json` and references related issues.

## twig/twig 3.29.0

**Added:** 2026-09-21

**Reason:** Twig 3.29.0 changed `TemplateWrapper::unwrap()` to require an
`Environment $env` argument (twigphp/Twig#4935). `symfony/twig-bridge`'s
`TwigRendererEngine` still calls it with no arguments when loading form
themes, so every form render throws `ArgumentCountError`. `sylius/mailer-bundle`
had the same call in `EmailTwigAdapter`. The BC break is reverted in the next
Twig release (twigphp/Twig#4936), so only 3.29.0 is affected.

**Remove when:** Twig 3.29.0 is no longer worth guarding against (the fix ships
in the next Twig release, see twigphp/Twig#4936).
