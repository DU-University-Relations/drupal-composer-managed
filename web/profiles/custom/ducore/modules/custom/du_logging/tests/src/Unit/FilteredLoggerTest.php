<?php

namespace Drupal\Tests\du_logging\Unit;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;
use Drupal\du_logging\Logger\FilteredLogger;
use Drupal\Core\Config\ConfigFactory;

/**
 * @coversDefaultClass \Drupal\du_logging\Logger\FilteredLogger
 */
class FilteredLoggerTest extends UnitTestCase {

  /**
   * Helper to build a mock ConfigFactory with given du_logging.settings values.
   */
  private function createConfigFactory(array $settings): ConfigFactory {
    $config = new class($settings) {
      private array $settings;
      public function __construct(array $settings) { $this->settings = $settings; }
      public function get($key) { return $this->settings[$key] ?? NULL; }
    };

    $factory = $this->getMockBuilder(ConfigFactory::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get'])
      ->getMock();

    $factory->method('get')
      ->with('du_logging.settings')
      ->willReturn($config);

    return $factory;
  }

  /**
   * @covers ::log
   */
  public function testFilteringDisabledPassesThrough(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->once())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => FALSE,
      'log_levels' => [RfcLogLevel::ERROR],
      'message_types' => ['cron'],
      'patterns' => ['!/deprecated/i'],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::DEBUG, 'Any message', ['channel' => 'cron']);
  }

  /**
   * @covers ::log
   */
  public function testLevelFiltered(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->never())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [RfcLogLevel::ERROR], // Only ERROR allowed.
      'message_types' => [],
      'patterns' => [],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::INFO, 'Informational message');
  }

  /**
   * @covers ::log
   */
  public function testChannelFiltered(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->never())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [], // Empty means allow all levels.
      'message_types' => ['cron', 'php'],
      'patterns' => [],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::WARNING, 'Cron warning', ['channel' => 'cron']);
  }

  /**
   * @covers ::log
   */
  public function testPatternFiltered(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->never())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [],
      'message_types' => [],
      'patterns' => ['/deprecated function/i', '/^SkipMe:/'],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::NOTICE, 'Deprecated function call in module x');
  }

  /**
   * @covers ::log
   */
  public function testMessagePassesThroughWhenAllowed(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->once())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [RfcLogLevel::ERROR, RfcLogLevel::WARNING],
      'message_types' => ['skip_channel'],
      'patterns' => ['/noise/i'],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::WARNING, 'Clean message', ['channel' => 'allowed_channel']);
  }

  /**
   * @covers ::log
   */
  public function testNoFilteringCriteriaConfigured(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->once())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [],
      'message_types' => [],
      'patterns' => [],
    ]);

    $logger = new FilteredLogger($inner, $configFactory);
    $logger->log(RfcLogLevel::INFO, 'Unfiltered message');
  }

}
