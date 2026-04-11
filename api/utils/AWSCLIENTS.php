<?php
use Aws\DynamoDb\DynamoDbClient;
use AWS\S3\S3Client;


function createDynamoClient($key) {
    $credentials = ['key' => $key['aws']['AWSUSERKEY'], 'secret' => $key['aws']["AWSUSERACCESS"]];
    return new DynamoDbClient([
        'region' => $key['aws']['AWSREGION'],
        'version' => 'latest',
        'credentials' => $credentials
    ]);
}

function createS3Client($key){
    $credentials = ['key' => $key['aws']['AWSUSERKEY'], 'secret' => $key['aws']["AWSUSERACCESS"]];
    return new S3Client([
        'region' => $key['aws']['AWSREGION'],
        'version' => 'latest',
        'credentials' => $credentials
    ]);

};