Installation
------------
Install the package through [Composer](http://getcomposer.org/).

Run the Composer require command from the Terminal:

```bash
composer require prihod/promt-translate
```

Usage example
-------------

```php
<?php
require_once(dirname(__FILE__) . '/vendor/autoload.php');

use Prihod\Translate\Translator;
use Prihod\Translate\Exception;

try {
  $translator = new Translator($key);
  $translation = $translator->translate('Hello world', 'en','ru');

  echo $translation; // Привет мир
  echo $translation->getSource(); // Hello world;
  echo $translation->getSourceLanguage(); // en
  echo $translation->getResultLanguage(); // ru
  
} catch (Exception $e) {
  // handle exception
}
```