<?php

namespace Flyo\Test;

use Flyo\Model\ConfigResponseContainersValue;
use PHPUnit\Framework\TestCase;

class DeprecationTest extends TestCase
{
    /**
     * Test that ConfigResponseContainersValue constructor doesn't trigger deprecation warnings
     * about implicitly marking parameter as nullable.
     *
     * This test ensures PHP 8.4+ compatibility where implicit nullable parameters are deprecated.
     */
    public function testConfigResponseContainersValueConstructorNoDeprecation()
    {
        // Capture any errors/warnings triggered during object instantiation
        $errorTriggered = false;
        $errorMessage = '';
        
        set_error_handler(function($errno, $errstr) use (&$errorTriggered, &$errorMessage) {
            if ($errno === E_DEPRECATED && strpos($errstr, 'Implicitly marking parameter') !== false) {
                $errorTriggered = true;
                $errorMessage = $errstr;
            }
            return true; // Don't execute PHP internal error handler
        });

        try {
            // Test with null
            new ConfigResponseContainersValue(null);
            
            // Test with empty array
            new ConfigResponseContainersValue([]);
            
            // Test with data
            new ConfigResponseContainersValue([
                'items' => [],
                'uid' => 'test-uid',
                'identifier' => 'test-identifier',
                'label' => 'Test Label'
            ]);
        } finally {
            restore_error_handler();
        }

        $this->assertFalse(
            $errorTriggered,
            "Deprecation warning triggered: {$errorMessage}\n" .
            "Fix: Change 'array \$data = null' to 'array|null \$data = null' in the constructor"
        );
    }

    /**
     * Test that the model works correctly with null data
     */
    public function testConfigResponseContainersValueWithNullData()
    {
        $model = new ConfigResponseContainersValue(null);
        $this->assertInstanceOf(ConfigResponseContainersValue::class, $model);
    }

    /**
     * Test that the model works correctly with valid data
     */
    public function testConfigResponseContainersValueWithValidData()
    {
        $data = [
            'items' => [],
            'uid' => 'test-uid',
            'identifier' => 'test-identifier',
            'label' => 'Test Label'
        ];
        
        $model = new ConfigResponseContainersValue($data);
        $this->assertInstanceOf(ConfigResponseContainersValue::class, $model);
        $this->assertEquals('test-uid', $model->getUid());
        $this->assertEquals('test-identifier', $model->getIdentifier());
        $this->assertEquals('Test Label', $model->getLabel());
    }
}
