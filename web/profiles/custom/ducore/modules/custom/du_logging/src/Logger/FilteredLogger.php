<?php

namespace Drupal\du_logging\Logger;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\RfcLoggerTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Psr\Log\LoggerInterface;

/**
 * Decorates the dblog logger to filter out noisy messages.
 */
class FilteredLogger implements LoggerInterface {
  use RfcLoggerTrait;

  /**
   * The decorated logger service.
   */
  protected LoggerInterface $innerLogger;

  private ConfigFactory $configFactory;

  private TranslationInterface $stringTranslation;

  /**
   * Constructs a FilteredLogger object.
   *
   * @param \Psr\Log\LoggerInterface $inner_logger
   *   The decorated logger service.
   */
  public function __construct(
    LoggerInterface $inner_logger,
    ConfigFactory $config_factory,
    TranslationInterface $string_translation) {
    $this->innerLogger = $inner_logger;
    $this->configFactory = $config_factory;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = []): void {
    // Skip if message should be filtered.
    if ($this->shouldFilter($level, $message, $context)) {
      return;
    }

    // Pass through to the original dblog logger.
    $this->innerLogger->log($level, $message, $context);
  }

  /**
   * Determines if a log message should be filtered out.
   *
   * @param mixed $level
   *   The log level.
   * @param string $message
   *   The log message.
   * @param array $context
   *   The log context.
   *
   * @return bool
   *   TRUE if the message should be filtered (not logged).
   */
  protected function shouldFilter($level, string $message, array $context): bool {
    $config = $this->configFactory->get('du_logging.settings');

    // If filtering is disabled, don't filter anything.
    if (!$config->get('enabled')) {
      return FALSE;
    }

    // Filter by log level first (most efficient check).
    $allowed_levels = $config->get('log_levels') ?? [];
    if (!empty($allowed_levels) && !in_array($level, $allowed_levels, TRUE)) {
      return TRUE;
    }

    // Check message type filtering
    $message_types = $config->get('message_types') ?: [];
    if (!empty($context['channel']) && in_array($context['channel'], $message_types)) {
      return TRUE;
    }

    // Check pattern filtering
    $patterns = $config->get('patterns') ?: [];
    $rendered_message = (string) $this->stringTranslation->translate($message, $context);
    foreach ($patterns as $pattern) {
      if (preg_match($pattern, $rendered_message)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}