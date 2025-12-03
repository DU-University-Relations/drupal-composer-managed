<?php
/**
 * Quicksilver Script: Warm critical cache pages.
 */

// 1. Load paths from site_meta.json (One level up from this script)
$json_path = __DIR__ . '/../site_meta.json';

if (!file_exists($json_path)) {
  echo "Skipping: site_meta.json not found in " . __DIR__ . "\n";
  exit;
}

$site_meta = json_decode(file_get_contents($json_path), true);
$paths = $site_meta['critical_paths'] ?? [];

if (empty($paths)) {
  echo "No paths defined in site_meta.json.\n";
  exit;
}

// 2. Determine Base URL
$env = $_ENV['PANTHEON_ENVIRONMENT'];
$site_name = $_ENV['PANTHEON_SITE_NAME'];

if ($env === 'live' && !empty($_ENV['VANITY_DOMAIN'])) {
  // Must contain the full URL (e.g., https://www.du.edu)
  $base_url = $_ENV['VANITY_DOMAIN'];
} else {
  // Default Pantheon internal URL for dev/test/multidevs
  $base_url = 'https://' . $env . '-' . $site_name . '.pantheonsite.io';
}

echo "Starting cache warm for [$env] at: $base_url \n";

// 3. Parallel Curl Execution
$mh = curl_multi_init();
$curl_handles = [];
$timeout = 10; // 10s timeout per page

foreach ($paths as $path) {
  // Ensure path starts with slash just in case JSON is messy
  $path = '/' . ltrim($path, '/');
  $url = $base_url . $path;

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  curl_multi_add_handle($mh, $ch);
  $curl_handles[$url] = $ch;
}

// Execute handles
$running = 0;
do {
  $mrc = curl_multi_exec($mh, $running);
  if ($mrc === CURLM_OK && $running) {
    // Wait for activity to avoid busy-wait
    curl_multi_select($mh, 1.0);
  }
} while ($running > 0);

// 4. Output Results
foreach ($curl_handles as $url => $ch) {
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
  echo "[$code] Warmed $url ({$time}s)\n";

  curl_multi_remove_handle($mh, $ch);
  curl_close($ch);
}

curl_multi_close($mh);
