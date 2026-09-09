# Copy to clipboard

The `copy_to_clipboard` component renders a button that copies an explicit value to the clipboard. The value itself is not displayed by the component, so render it separately when it should also be visible on the page.

## Installation

Install Bootstrap Admin UI before using the component:

```bash
composer require sylius/bootstrap-admin-ui
```

## Usage

Pass a literal value with the `value` prop:

```twig
<span>ABC-123</span>

<twig:sylius_bootstrap_admin_ui:copy_to_clipboard
    value="ABC-123"
/>
```

For a Twig expression, prefix the prop with `:`:

```twig
<span>{{ item.code }}</span>

<twig:sylius_bootstrap_admin_ui:copy_to_clipboard
    :value="item.code"
/>
```

### Twig Hooks

The component can also be rendered as a Twig Hook component. Pass the value explicitly through its props:

```yaml
sylius_twig_hooks:
    hooks:
        'app.example':
            copy:
                component: 'sylius_bootstrap_admin_ui:copy_to_clipboard'
                props:
                    value: '@=_context.item.getCode()'
```

### Button attributes

Additional component attributes are forwarded to the button. Default button classes are preserved when adding custom classes:

```twig
<twig:sylius_bootstrap_admin_ui:copy_to_clipboard
    :value="item.code"
    class="btn-sm"
    data-test-copy-code
/>
```

### Translations

The component uses these translation keys by default:

- `sylius.ui.copy_to_clipboard`
- `sylius.ui.copied_to_clipboard`
- `sylius.ui.unable_to_copy_to_clipboard`

Override those keys in your application's translation files, or pass alternative translation keys with the label props:

```twig
<twig:sylius_bootstrap_admin_ui:copy_to_clipboard
    :value="item.code"
    copy_label="app.ui.copy_code"
    copied_label="app.ui.code_copied"
    error_label="app.ui.unable_to_copy_code"
/>
```

## Browser requirements

Copying uses the browser's Clipboard API. This API requires a secure context, which normally means HTTPS in production or localhost during development.
