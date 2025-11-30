<?php
// config.php -- store local-only settings
return [
  'smtp' => [
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'your-smtp-username@example.com',
    'password' => 'your-smtp-password',
    'secure' => 'tls', // 'ssl' or 'tls'
    'from_email' => 'no-reply@flowerngo.com',
    'from_name' => 'Flower n GO'
  ],
  'base_url' => 'http://localhost/FlowernGo' // adjust to your local/production base url
];
