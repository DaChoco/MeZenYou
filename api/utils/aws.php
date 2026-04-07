<?php
require 'vendor/autoload.php';
class AWSservice
{
    private $s3;
    private $dynamo;
    private $bucketName = "mezenyou";
    private $tableName = "mezenyoutbl";

    public function __construct($s3Client, $dynamoClient)
    {
        $this->s3 = $s3Client;
        $this->dynamo = $dynamoClient;
    }

    #AWS S3-------------------------
    public function uploadUserIcon($userID, $File)
    {
        $fileName = $this->generateS3Key("users", $File['name'], $userID);

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'SourceFile' => $File['image'],
                'ContentType' => mime_content_type($File['image'])

            ]);

            return $fileName; //THIS GETS INSERTED IN THE DB. DO NOT FORGET IT
        } catch (Exception $exception) {
            echo "Failed to upload $fileName with error: " . $exception->getMessage();
            return "";
        }
    }

    public function uploadProductImage($productID, $File)
    {
        $fileName = $this->generateS3Key("products", $File['name'], $productID);

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'SourceFile' => $File['image'],
                'ContentType' => mime_content_type($File['image'])

            ]);

            return $fileName; //THIS GETS INSERTED IN THE DB. DO NOT FORGET IT
        } catch (Aws\S3\Exception\S3Exception $exception) {
            echo "Failed to upload $fileName with error: " . $exception->getMessage();
            return "";
        }
    }

    #AWS S3-------------------------

    private function generateS3Key(string $type, string $filename, string $id): string
    {
        #key structure type/USERID/UNIQUEIDENTIFIER.png/jpeg
        #user/43/9292939949_welt.png

        $safeFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);

        $timestamp = time();
        return "{$type}/{$id}/{$timestamp}_{$safeFilename}";
    }

    #AWS DynamoDB-------------------------
    public function retrieveProductReviews($productID)
    {
        try {

            $result = $this->dynamo->query([
                'TableName' => $this->tableName,
                'ScanIndexForward' => false,
                'KeyConditionExpression' => 'product_id = :pid',
                'ExpressionAttributeValues' => [
                    ':pid' => ['S' => $productID]
                ]
            ]);

            $items = $result['Items'];

            return $items;
        } catch (Exception $e) {
            echo "Failed to upload review with error: " . $e->getMessage();
            return false;
        }
    }



    public function uploadProductReview(string $userID, $productID, $txt, $rating): bool
    {


        try {
            $this->dynamo->putItem(
                [
                    'Item' => [
                        'uID' => [
                            'S' => $userID,
                        ],
                        'timestamp' => [
                            'N' => time(),
                        ],
                        "pID" => [
                            'S' => $productID
                        ],
                        "comment" => [
                            "S" => $txt
                        ],
                        "rating" => [
                            "N" => $rating
                        ]
                    ],
                    'TableName' => $this->tableName,
                ]
            );


            return true;
        } catch (Aws\DynamoDb\Exception\DynamoDbException $exception) {
            echo "Failed to upload review with error: " . $exception->getMessage();
            return false;
        }
    }

    #AWS DYNAMODB-------------------------
}
