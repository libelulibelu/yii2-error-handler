<?php

namespace Libelula\ErrorHandler\Tests;

use Libelula\ErrorHandler\ErrorHandler;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionMethod;
use ReflectionProperty;
use Yii;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\web\Application;

/**
 * Base test case providing the shared scaffolding reused across the suite:
 * booting/destroying a mock Yii web application, the error-handler component
 * config and a reflection helper for private methods.
 */
abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        $this->destroyApplication();
        parent::tearDown();
    }

    /**
     * Boots a minimal Yii web application, merging per-test overrides on top of
     * sensible defaults.
     *
     * @param array $config Configuration merged over the defaults.
     * @return void
     */
    protected function mockWebApplication(array $config = []): void
    {
        new Application(ArrayHelper::merge([
            'id' => 'test-app',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'test-cookie-validation-key',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ], $config));
    }

    /**
     * Tears down the current application and resets the DI container so tests
     * stay isolated.
     *
     * @return void
     */
    protected function destroyApplication(): void
    {
        if (Yii::$app !== null && Yii::$app->has('errorHandler')) {
            Yii::$app->getErrorHandler()->unregister();
        }
        Yii::$app = null;
        Yii::$container = new Container();
    }

    /**
     * Builds the configuration for the `errorHandler` component backed by this
     * library's handler so {@see \Libelula\ErrorHandler\utils\Handler::loggerInfo()}
     * can read a valid component.
     *
     * @param array $extra Extra properties merged into the component config.
     * @return array
     */
    protected function errorHandlerConfig(array $extra = []): array
    {
        return ArrayHelper::merge([
            'class' => ErrorHandler::class,
            'company' => 'TEST',
            'loggerComponent' => '',
        ], $extra);
    }

    /**
     * Invokes a private/protected method via reflection.
     *
     * @param object $object Instance owning the method.
     * @param string $method Method name.
     * @param array $args Positional arguments.
     * @return mixed The method return value.
     */
    protected function invokePrivate(object $object, string $method, array $args = []): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        return $reflection->invokeArgs($object, $args);
    }

    /**
     * Reads a private/protected property via reflection.
     *
     * @param object $object Instance owning the property.
     * @param string $property Property name.
     * @return mixed The property value.
     */
    protected function getPrivateProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionProperty($object, $property);
        return $reflection->getValue($object);
    }
}
