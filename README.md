# Error handler

Error handler for mongo database

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist libelulibelu/yii2-error-handler
```

or add

```
"libelulibelu/yii2-error-handler": "~1.0.0"
```

to the require section of your `composer.json` file.

## Migration

Si se quiere migrar de la version `taguz91/yii2-error-handler` a la nueva version `libelulibelu/yii2-error-handler` se debe seguir los siguientes pasos:

1. Seguir la guia de migracion para [yii2-common-helpers](https://github.com/libelulibelu/yii2-common-helpers).

2. Eliminar la version actual

```
composer remove taguz91/yii2-error-handler
```

3. Instalar la nueva version

```
composer require libelulibelu/yii2-error-handler
```

4. Se debe cambiar el namespace `taguz91\ErrorHandler` a `Libelula\ErrorHandler` en todo el proyecto.

5. Actualizar las configuraciones de la libreria, agregando las nuevas opciones:

- **bdConnection** nombre de la base de datos que se usara para guardar todas las excepciones.
- **saveError** booleano que nos indica si debemos guardar los errores en base de datos.
- **showTrace** booleano que nos indica si debemos mostrar le trace en la response, por defecto utiliza la constante YII_DEBUG
- **saveBody** booleano que nos indica si debemos guardar los datos enviados por _post_ en la excepcion, por defecto se utiliza la constante YII_DEBUG

6. Las opciones de configuracion `empresa` y `notificate` fueron renombradas a `company` y `notify` respectivamente. Se deben actualizar en la configuracion del componente `errorHandler`.

7. Probamos que todo funcione de forma correcta.

## Usage

Once the extension is installed, simply use it in your code by:

```php
// confing\main.php

'components' => [
  ...,
  'errorHandler' => [
    'errorAction' => 'site/error',
    'class' => \Libelula\ErrorHandler\ErrorHandler::class,
    'loggerComponent' => '', // empty when the logger handler not exists
    'emailConfig' => 'EMAIL_ERROR_NOTIFICATION', // configuration for email
    'configClass' => '/common/models/Configuration', // debe implementar interface config
    'company' => $_GET['empresa'] ?? 'undefined',
    'bdConnection' => 'mongodb',
    'saveError' => true,
    'notify' => true,
    'showTrace' => YII_DEBUG,
    'saveBody' => YII_DEBUG,
    // This exceptions not be save into database
    // And this exceptions not send via email
    'exceptionsNotSave' => [
      \Libelula\ErrorHandler\exceptions\MessageException::class
    ],
  ],
]
```
