<?php

/**
 * @file
 * Sends Autopilot VRT Quicksilver hook data to MS Teams.
 */

$webhook_url = pantheon_get_secret('AUTOPILOT_NOTIFICATION_URL');

if (empty($webhook_url)) {
  die('Missing AUTOPILOT_WEBHOOK_URL environment variable. Aborting!');
}

if (empty($_POST['updates_info']) || empty($_POST['vrt_status']) || empty($_POST['vrt_result_url'])) {
  die('Missing required POST data. Aborting!');
}

$updates_info = json_decode($_POST['updates_info'], true);
$status = $_POST['vrt_status'];
$vrt_result_url = $_POST['vrt_result_url'];

$site_name = $_ENV['PANTHEON_SITE_NAME'];
$environment = $_ENV['PANTHEON_ENVIRONMENT'];
$full_vrt_url = "https://dashboard.pantheon.io/" . $vrt_result_url;

$is_pass = ($status == 'tolerable' || $status == 'pass' );
$emoji = $is_pass ? '✅' : '❌';
$color = $is_pass ? 'Good' : 'Attention';

$message_data = [
  'type' => 'message',
  'attachments' => [
    [
      'contentType' => 'application/vnd.microsoft.card.adaptive',
      'content' => [
        '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
        'type' => 'AdaptiveCard',
        'version' => '1.4',
        'body' => [
          [
            'type' => 'TextBlock',
            'text' => "$emoji Autopilot VRT: $status",
            'weight' => 'Bolder',
            'size' => 'Large',
            'color' => $color,
          ],
          [
            'type' => 'FactSet',
            'facts' => [
              ['title' => 'Site:', 'value' => $site_name],
              ['title' => 'Environment:', 'value' => $environment],
              ['title' => 'Status:', 'value' => ucfirst($status)],
            ],
          ],
          [
            'type' => 'TextBlock',
            'text' => 'Updates Performed:',
            'weight' => 'Bolder',
            'spacing' => 'Medium',
          ],
          [
            'type' => 'TextBlock',
            'text' => print_r($updates_info, true),
            'wrap' => true,
          ],
        ],
        'actions' => [
          [
            'type' => 'Action.OpenUrl',
            'title' => 'Review VRT Results',
            'url' => $full_vrt_url,
          ],
        ],
      ],
    ],
  ],
];

$context = stream_context_create([
  'http' => [
    'method' => 'POST',
    'header' => 'Content-Type: application/json',
    'content' => json_encode($message_data),
    'ignore_errors' => true,
  ],
]);

$response = file_get_contents($webhook_url, false, $context);

// Teams returns 200 with empty body on success.
$status_code = $http_response_header[0] ?? '';
if (str_contains($status_code, '200')) {
  echo "MS Teams notification sent successfully\n";
} else {
  echo "Failed to send notification: $status_code\n";
  echo $response;
}
