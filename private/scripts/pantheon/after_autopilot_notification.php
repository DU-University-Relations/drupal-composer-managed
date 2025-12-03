<?php

/**
 * @file
 * Sends Autopilot VRT Quicksilver hook data to MS Teams.
 */

$webhook_url = $_ENV['AUTOPILOT_WEBHOOK_URL'] ?? getenv('AUTOPILOT_WEBHOOK_URL');

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

$is_pass = ($status === 'pass');
$emoji = $is_pass ? '✅' : '❌';
$color = $is_pass ? 'Good' : 'Attention';

// Build update list as plain text for Teams.
$update_lines = array_map(
  fn($ext) => "• {$ext['title']}: {$ext['version']} → {$ext['update_version']}",
  $updates_info['extension_list'] ?? []
);
$update_list_text = implode("\n", $update_lines);

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
            'text' => $update_list_text,
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
