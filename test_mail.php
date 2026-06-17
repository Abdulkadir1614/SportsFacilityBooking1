<?php

echo "<pre>";

echo "SMTP_HOST = " . ($_ENV['SMTP_HOST'] ?? 'MISSING') . PHP_EOL;
echo "SMTP_USER = " . ($_ENV['SMTP_USER'] ?? 'MISSING') . PHP_EOL;
echo "SMTP_PASS LENGTH = " . strlen($_ENV['SMTP_PASS'] ?? '') . PHP_EOL;
echo "SMTP_PORT = " . ($_ENV['SMTP_PORT'] ?? 'MISSING') . PHP_EOL;
echo "SMTP_FROM = " . ($_ENV['SMTP_FROM'] ?? 'MISSING') . PHP_EOL;
echo "APP_URL = " . ($_ENV['APP_URL'] ?? 'MISSING') . PHP_EOL;