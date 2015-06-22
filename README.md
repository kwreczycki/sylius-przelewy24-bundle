# sylius-przelewy24-bundle
Sylius przelewy24 payments integration

# Instalation
## 1. Download bundle
```
"require": {
    "kwreczycki/sylius-przelewy24-bundle": "*@dev"
}
```

## 2. Register Bundle
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new \KW\Bundle\SyliusPrzelewy24Bundle\SyliusPrzelewy24Bundle()
    );
}
```

## 3. Configure application

```yml
// app/config/parameters.yml

przelewy24.api.sandbox: true # false on production
przelewy24.api.gateway_id: EDITME
przelewy24.api.crc_key: EDITME
przelewy24.api.return_url_domain: http://localhost:8000 # your service domain
```

```yml
payum:
    payments:
        przelewy24:
            przelewy24:
              api:
                sandbox: %przelewy24.api.sandbox%
                gateway_id: %przelewy24.api.gateway_id%
                crc_key: %przelewy24.api.crc_key%
                return_url_domain: %przelewy24.api.return_url_domain%
              apis:
                - sylius.payment.payum.przelewy24.api
              actions:
                - sylius.payment.payum.przelewy24.notify
                - sylius.payment.payum.przelewy24.capture
                - sylius.payment.payum.przelewy24.capture_offsite
                - sylius.payment.payum.przelewy24.status
```

## 4. Add payment method to database

Execute `php app/console doctrine:fixtures:load`
