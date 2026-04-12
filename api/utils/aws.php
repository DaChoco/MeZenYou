<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\DynamoDb\Marshaler;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AWSservice
{
    private $s3;
    private $dynamo;
    private $bucketName = "mezenyou";
    private $tableName = "mezenyoutbl";
    private $imgbaseurl = "https://ik.imagekit.io/gp2sqgkfsChocoChoco/resources";


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

    public function uploadProductImage($productID, $File, $name)
    {
        $fileName = $this->generateS3Key("products", $name, $productID);

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $fileName,
                'SourceFile' => $File,
                'ContentType' => mime_content_type($File)

            ]);
            $url = $this->generateImageURL($fileName);
            return $url; //THIS GETS INSERTED IN THE DB. DO NOT FORGET IT
        } catch (\Throwable $exception) {
            error_log("Failed to upload $fileName with error: " . $exception->getMessage());
            return "";
        }
    }

    #AWS S3-------------------------


    #HELPER PRIVATE FUNCTIONS
    private function generateS3Key(string $type, string $filename, string $id): string
    {
        #key structure type/USERID/UNIQUEIDENTIFIER.png/jpeg
        #user/43/9292939949_welt.png

        $safeFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);

        $timestamp = time();
        return "{$type}/{$id}/{$timestamp}_{$safeFilename}";
    }

    public function generateImageURL(string $key)
    {
        if (!$key) {
            echo "MISSING KEY";
            return null;
        }
        $url = "{$this->imgbaseurl}/$key";

        return $url;
    }

    private function generateConversationID($userID1, $userID2): string
    {
        $ids = [$userID1, $userID2];
        sort($ids);
        return "{$ids[0]}#{$ids[1]}";
    }

    #AWS DynamoDB -REVIEWS-------------------------
    public function retrieveProductReviews($productID)
    {
        $marshaler = new Marshaler();
        try {

            $result = $this->dynamo->query([
                'TableName' => $this->tableName,
                'KeyConditionExpression' => 'GSIPID = :pid',
                'IndexName' => 'timesorting',

                'ExpressionAttributeValues' => [
                    ':pid' => ['S' => "PRODUCT#$productID"],
                ],
                'ScanIndexForward' => false,
            ]);

            $cleanItems = [];
            foreach ($result['Items'] as $item) {
                $cleanItems[] = $marshaler->unmarshalItem($item);
            }

            $result = $this->dynamo->getItem([
                'TableName' => $this->tableName,
                'Key' => [
                    'pID' => ['S' => "PRODUCT#$productID"],
                    'uID' => ['S' => "METADATA"]
                ]
            ]);

            $item = $result['Item'];

            if (!isset($result['Item'])) {
                $avg = 0;
            } else {
                $item = $result['Item'];
                $sum = (int)($item['ratingSum']['N'] ?? 0);
                $count = (int)($item['ratingCount']['N'] ?? 0);
                $avg = $count > 0 ? $sum / $count : 0;
            }

            return ["reviews" => $cleanItems, "avg" => $avg];
        } catch (Exception $e) {
            echo "Failed to extract review with error: " . $e->getMessage();
            return false;
        }
    }

    public function retrieveAllProductReviews()
    {
        $marshaler = new Marshaler();

        try {
            $result = $this->dynamo->query([
                'TableName' => $this->tableName,
                'IndexName' => 'uid-index',
                'KeyConditionExpression' => 'uID = :uid',
                'ExpressionAttributeValues' => [
                    ':uid' => ['S' => 'METADATA'],
                ],
            ]);
            $average_ratings = [];

            $average_ratings = [];

            if (!isset($result['Items']) || count($result['Items']) === 0) {
                return [];
            }

            foreach ($result['Items'] as $rawItem) {
                $item = $marshaler->unmarshalItem($rawItem);
                $sum = $item['ratingSum'] ?? 0;
                $count = $item['ratingCount'] ?? 0;

                $avg = $count > 0 ? $sum / $count : 0;

                $average_ratings[] = [
                    "pID" => $item['pID'] ?? null,
                    "avg" => $avg
                ];
            }

            return $average_ratings;
        } catch (Throwable $e) {
            error_log("Dynamo error: " . $e->getMessage());
            return false;
        }
    }



    public function uploadProductReview(string $userID, $productID, $txt, $rating, $username): bool
    {
        $current_time = (string) time();
        try {
            $this->dynamo->transactWriteItems([
                'TransactItems' => [
                    [
                        'Put' => [
                            'TableName' => $this->tableName,
                            'Item' => [
                                'pID' => ['S' => "PRODUCT#$productID"],
                                'uID' => ['S' => "REVIEW#USER#$userID"],
                                'rating' => ['N' => (string)$rating],
                                'comment' => ['S' => $txt],
                                'timestamp' => ['N' => $current_time],
                                'username' => ['S' => $username],
                                "GSIPID" => ['S' => "PRODUCT#$productID"],
                                "GSITIME" => ['N' => $current_time],
                            ],
                            'ConditionExpression' => 'attribute_not_exists(pID) AND attribute_not_exists(uID)',
                        ]
                    ],
                    [
                        'Update' => [
                            'TableName' => $this->tableName,
                            'Key' => [
                                'pID' => ['S' => "PRODUCT#$productID"],
                                'uID' => ['S' => "METADATA"]
                            ],
                            'UpdateExpression' => 'SET ratingSum = if_not_exists(ratingSum, :zero) + :r, ratingCount = if_not_exists(ratingCount, :zero) + :one',
                            'ExpressionAttributeValues' => [
                                ':r' => ['N' => (string)$rating],
                                ':one' => ['N' => '1'],
                                ':zero' => ['N' => '0']
                            ]
                        ]
                    ]
                ]
            ]);


            return true;
        } catch (Aws\DynamoDb\Exception\DynamoDbException $exception) {
            echo "Failed to upload review with error: " . $exception->getMessage();
            return false;
        }
    }

    #AWS DYNAMODB-MESSAGING------------------------

    public function sendMessage(string $senderID, string $recipientID, string $messageText): array
    {
        try {
            $conversationID = $this->generateConversationID($senderID, $recipientID);
            $messageID = uniqid('msg_', true);
            $timestamp = (int) (microtime(true) * 1000); // Millisecond tier precision

            $this->dynamo->putItem([
                'Item' => [
                    'conversationID' => ['S' => $conversationID],
                    'timestamp' => ['N' => (string) $timestamp],
                    'messageID' => ['S' => $messageID],
                    'senderID' => ['S' => $senderID],
                    'recipientID' => ['S' => $recipientID],
                    'messageText' => ['S' => $messageText],
                    'isRead' => ['BOOL' => false],
                ],
                'TableName' => 'MeZenYoumessages',
            ]);

            return [
                'success' => true,
                'messageID' => $messageID,
                'timestamp' => $timestamp
            ];
        } catch (Exception $e) {
            echo "Failed to send message: " . $e->getMessage();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
