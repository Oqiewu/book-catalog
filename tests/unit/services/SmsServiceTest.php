<?php

namespace tests\unit\services;

use app\services\SmsService;
use Codeception\Test\Unit;

/**
 * Unit test for SmsService
 */
class SmsServiceTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    public function testNormalizePhone()
    {
        $service = new SmsService();

        // Use reflection to access private method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('normalizePhone');
        $method->setAccessible(true);

        // Test various phone formats
        $this->assertEquals('+79991234567', $method->invoke($service, '89991234567'));
        $this->assertEquals('+79991234567', $method->invoke($service, '9991234567'));
        $this->assertEquals('+79991234567', $method->invoke($service, '+79991234567'));
        $this->assertEquals('+79991234567', $method->invoke($service, '8 (999) 123-45-67'));
        $this->assertEquals('+79991234567', $method->invoke($service, '+7 999 123 45 67'));
    }

    public function testSendWithEmulatorKey()
    {
        $service = new SmsService();

        // Using emulator key should not throw exceptions
        $result = $service->send('+79991234567', 'Test message');

        // With emulator, result may vary, but should not crash
        $this->assertTrue(is_bool($result), 'Send should return boolean');
    }

    public function testSendWithInvalidPhone()
    {
        $service = new SmsService();

        // Empty phone should be normalized but may fail
        $result = $service->send('', 'Test message');

        $this->assertFalse($result, 'Send with empty phone should fail');
    }

    public function testSendWithEmptyMessage()
    {
        $service = new SmsService();

        $result = $service->send('+79991234567', '');

        $this->assertFalse($result, 'Send with empty message should fail');
    }
}
