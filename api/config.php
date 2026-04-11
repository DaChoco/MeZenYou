
<?php
require __DIR__ . '/../vendor/autoload.php';
$envPath = dirname(__DIR__);
if (file_exists($envPath .'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
}
return [
    "db"=> [
        "DB_HOST" => $_ENV['DB_HOST'],
        "DB_NAME" => $_ENV['DB_NAME'],
        "DB_USER" => $_ENV['DB_USER'],
        "DB_PASS" => $_ENV['DB_PASS']
        ],
    "aws" => [
        "AWSUSERKEY" => $_ENV['AWS_ACCESS_KEY_ID'],
        "AWSUSERACCESS" => $_ENV["AWS_SECRET_ACCESS_KEY"],
        "AWSREGION" => $_ENV["AWS_REGION"]
    ],
    "util" => [
        "frontendURL" => "http://localhost:3000"
    ]
    
];

