<?php

namespace Drupal\lab_migration\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * One place to send all lab_migration emails.
 *
 * Before this, every form was doing the same thing: read From/Cc/Bcc from
 * config, build the headers array, call plugin.manager.mail, check the result,
 * show an error if it failed. That's ~15 lines copy-pasted everywhere.
 *
 * This service wraps all of that so call sites just do:
 *   \Drupal::service('lab_migration.mail_service')->send('proposal_received', $to, $params);
 *
 * The hook_mail() in lab_migration.module still handles subject/body — nothing
 * changes there, this only cleans up the dispatch side.
 */
class LabMigrationMailService {

  use StringTranslationTrait;

  protected MailManagerInterface $mailManager;
  protected ConfigFactoryInterface $configFactory;
  protected MessengerInterface $messenger;

  public function __construct(
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory,
    MessengerInterface $messenger
  ) {
    $this->mailManager   = $mail_manager;
    $this->configFactory = $config_factory;
    $this->messenger     = $messenger;
  }

  /**
   * Builds the standard MIME headers using values from lab_migration.settings.
   *
   * Cc and Bcc are read from config and normalised to strings here so callers
   * don't have to worry about it. Pass $overrides to swap out individual headers
   * if you need something different (e.g. a different Content-Type for HTML mail).
   */
  public function buildHeaders(array $overrides = []): array {
    $config = $this->configFactory->get('lab_migration.settings');

    $from = $config->get('lab_migration_from_email') ?: '';
    $cc   = $config->get('lab_migration_cc_emails')  ?: '';
    $bcc  = $config->get('lab_migration_emails')      ?: '';

    // PhpMail expects comma-delimited strings, not arrays.
    if (is_array($cc)) {
      $cc = implode(',', $cc);
    }
    if (is_array($bcc)) {
      $bcc = implode(',', $bcc);
    }

    $headers = [
      'From'                     => $from,
      'MIME-Version'             => '1.0',
      'Content-Type'             => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
      'Content-Transfer-Encoding' => '8Bit',
      'X-Mailer'                 => 'Drupal',
      'Cc'                       => $cc,
      'Bcc'                      => $bcc,
    ];

    return array_merge($headers, $overrides);
  }

  /**
   * Sends a lab_migration email and shows an error message if it fails.
   *
   * $key must match a case in hook_mail() in lab_migration.module
   * (e.g. 'proposal_received', 'proposal_approved', 'solution_uploaded').
   *
   * $params should follow the same structure the form was already building,
   * just without the headers sub-key — that gets added here automatically
   * unless you've already set it yourself.
   *
   * Returns true/false so callers can act on the result if they need to,
   * though most of the time you can just fire-and-forget.
   */
  public function send(
    string $key,
    string $to,
    array $params,
    ?string $langcode = NULL,
    array $header_overrides = []
  ): bool {
    if (empty($langcode)) {
      $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();
    }

    // Only inject headers if the caller hasn't already set them.
    if (!isset($params[$key]['headers'])) {
      $params[$key]['headers'] = $this->buildHeaders($header_overrides);
    }

    $from = $this->configFactory->get('lab_migration.settings')
      ->get('lab_migration_from_email') ?: NULL;

    $result = $this->mailManager->mail(
      'lab_migration',
      $key,
      $to,
      $langcode,
      $params,
      $from,
      TRUE
    );

    if (empty($result['result'])) {
      $this->messenger->addError($this->t('Error sending email notification (@key).', ['@key' => $key]));
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Shortcut for sending a plain admin error notification.
   *
   * Used in deletion functions when a file or folder can't be removed —
   * just pass a subject and body and it handles everything else.
   * This hits the 'standard' case in hook_mail() which accepts free-form content.
   */
  public function sendStandard(
    string $to,
    string $subject,
    string $body,
    array $header_overrides = []
  ): bool {
    $params = [
      'standard' => [
        'subject' => $subject,
        'body'    => $body,
        'headers' => $this->buildHeaders($header_overrides),
      ],
    ];

    return $this->send('standard', $to, $params);
  }

}
