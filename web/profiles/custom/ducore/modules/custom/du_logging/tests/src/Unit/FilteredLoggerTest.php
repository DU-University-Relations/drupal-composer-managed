<?php

namespace Drupal\Tests\du_logging\Unit;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;
use Drupal\du_logging\Logger\FilteredLogger;
use Drupal\Core\Config\ConfigFactory;

/**
 * @coversDefaultClass \Drupal\du_logging\Logger\FilteredLogger
 */
class FilteredLoggerTest extends UnitTestCase {

  private TranslationInterface $translation;
  protected function setUp(): void {
    parent::setUp();

    $this->translation = $this->createStringTranslation();
  }

  /**
   * Helper to build a mock TranslationInterface.
   */
  private function createStringTranslation(): TranslationInterface {
    $translation = $this->createMock(TranslationInterface::class);
    // Naive token replacement to simulate rendered messages.
    $translation->method('translate')
      ->willReturnCallback(static function ($message, array $context = []) {
        foreach ($context as $key => $value) {
          // Replace both @key and %key placeholders.
          $message = str_replace('@' . $key, $value, $message);
          $message = str_replace('%' . $key, $value, $message);
        }
        return $message;
      });

    return $translation;
  }

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
   * When filtering is disabled, a call to FilteredLogger::log() must call the inner
   * logger’s log() once.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::DEBUG, 'Any message', ['channel' => 'cron']);
  }

  /**
   * With filtering enabled and only ERROR level allowed, an INFO log must not
   * call the inner logger at all.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::INFO, 'Informational message');
  }

  /**
   * With certain channels configured, logging to cron must not reach the inner logger.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::WARNING, 'Cron warning', ['channel' => 'cron']);
  }

  /**
   * If the message matches a configured pattern (e.g. “deprecated”), it must not reach the inner logger.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::NOTICE, 'Deprecated function call in module x');
  }

  /**
   * When the level, channel, and message do not match any filter, the inner logger must be called once.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::WARNING, 'Clean message', ['channel' => 'allowed_channel']);
  }

  /**
   * If filtering is enabled but no criteria are configured, the message should pass through and log()
   * should be called once.
   *
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

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::INFO, 'Unfiltered message');
  }

  /**
   * If the rendered (token-replaced) message matches a configured pattern,
   * it must not reach the inner logger.
   *
   * This simulates the typical dblog message template:
   *   '@type: @message in %function (line %line of %file).'
   * and a pattern the admin copied from the dblog UI, e.g.
   *   '/CacheableAccessDeniedHttpException/i'
   *
   * @covers ::log
   */
  public function testRenderedMessagePatternFiltered(): void {
    $inner = $this->createMock(LoggerInterface::class);
    $inner->expects($this->never())->method('log');

    $configFactory = $this->createConfigFactory([
      'enabled' => TRUE,
      'log_levels' => [],
      'message_types' => [],
      'patterns' => ['/CacheableAccessDeniedHttpException/i'],
    ]);

    // This is roughly what core uses for dblog message templates.
    $template = '@type: @message in %function (line %line of %file).';

    // IMPORTANT: keys are bare (type, message, function, line, file),
    // the template uses @type, @message, %function, %line, %file.
    $context = [
      'type' => 'Path',
      'message' => "Drupal\\Core\\Http\\Exception\\CacheableAccessDeniedHttpException: The 'access site reports' permission is required.",
      'function' => 'Drupal\\Core\\Routing\\AccessAwareRouter->checkAccess()',
      'line' => 117,
      'file' => '/var/www/html/web/core/lib/Drupal/Core/Routing/AccessAwareRouter.php',
    ];

    $logger = new FilteredLogger($inner, $configFactory, $this->translation);
    $logger->log(RfcLogLevel::ERROR, $template, $context);
  }

}
