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
        return $this->uploadToS3("users", $File, $userID, "avatar");
    }

    public function uploadProductImage($productID, $File)
    {
        return $this->uploadToS3("products", $File, $productID, "main");
    }

    public function deleteS3Image($url)
    {
        if (str_contains($url, $this->imgbaseurl) === false) {
            //NOT STORED ON AWS S3 BUCKET
            return false;
        }
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $this->extractKey($url)
            ]);

            return true;
        } catch (\Throwable $exception) {
            error_log("Failed to Delete $url with error: " . $exception->getMessage());
            return false;
        }
    }

    #AWS S3-------------------------


    #HELPER PRIVATE FUNCTIONS
    private function uploadToS3($folder, $filePath, $id, $new_name)
    //This is the main upload function, user icon and product image are wrappers to help them feel seperate
    {
        $key = $this->generateS3Key($folder, $filePath, $id, $new_name);

        try {
            $this->s3->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'SourceFile' => $filePath,
                'ContentType' => mime_content_type($filePath)
            ]);

            return $this->generateImageURL($key);
        } catch (\Throwable $exception) {
            error_log("Failed to upload $key with error: " . $exception->getMessage());
            return "";
        }
    }

    private function generateS3Key(string $type, $tmpPath, string $id, string $new_name): string
    {   #filename is that original filename the image had before
        #key structure type/USERID/avatar.png/jpeg
        #user/43/avatar.png

        $mime = mime_content_type($tmpPath);

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMime[$mime])) {
            throw new Exception("Invalid file type");
        }

        return "{$type}/{$id}/{$new_name}.{$allowedMime[$mime]}";
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

    private function extractKey($url)
    {
        return str_replace("{$this->imgbaseurl}/", "", $url);
    }

    private function generateConversationID($userID1, $userID2): string
    {
        $ids = [$userID1, $userID2];
        sort($ids);
        return "{$ids[0]}#{$ids[1]}";
    }

    private function censorMessage($txt){
        #this is just to censor messages
        $PROFANITY_WORDS = ["shit", "crap", "asshole", "bastard", "fuck", "bitch"];
        $pattern = '/\b(' . implode('|', $PROFANITY_WORDS) . ')\w*\b/i';

        return preg_replace_callback($pattern, function($matches) {
            return str_repeat('*', strlen($matches[0]));
        }, $txt);

        

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
                $sum = (int) ($item['ratingSum']['N'] ?? 0);
                $count = (int) ($item['ratingCount']['N'] ?? 0);
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

        $txt = $this->censorMessage($txt);
        try {
            $this->dynamo->transactWriteItems([
                'TransactItems' => [
                    [
                        'Put' => [
                            'TableName' => $this->tableName,
                            'Item' => [
                                'pID' => ['S' => "PRODUCT#$productID"],
                                'uID' => ['S' => "REVIEW#USER#$userID"],
                                'rating' => ['N' => (string) $rating],
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
                                ':r' => ['N' => (string) $rating],
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
    public function updateConvIcon(string $userID, string $icon)
    {
        //When someone updates messages in the sql, call this as well to line up their icons on dynamo

        try {
            $result = $this->dynamo->updateItem([
                'TableName' => 'mezenyoumsg',
                'Key' => [
                    'cID' => ['S' => $userID]
                ],
                'UpdateExpression' => 'SET avatar = :icon',
                'ExpressionAttributeValues' => [
                    ':icon' => ['S' => $icon]
                ],
                'ReturnValues' => 'UPDATED_NEW'
            ]);
            return ["result" => $result['Attributes'], "success" => true];

        } catch (\Aws\DynamoDb\Exception\DynamoDbException $e) {
            if ($e->getAwsErrorCode() === 'ConditionalCheckFailedException') {
            return ["success" => true, "message" => "No conversations to update"];
        }
        }
        
        catch (Exception $e) {
            echo "Failed to send message: " . $e->getMessage();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    public function sendMessage(string $senderID, string $recipientID, string $messageText, string $myIcon, string $theirIcon, string $role): array
    {
        try {
            $conversationID = $this->generateConversationID($senderID, $recipientID);
            $messageID = uniqid('msg_', true);
            $timestamp = (int) (microtime(true) * 1000); // Millisecond tier precision

            $messageSK = "MSG#{$timestamp}";
            $convSK = "CONV#{$conversationID}";

            $messageText = $this->censorMessage($messageText);



            $this->dynamo->transactWriteItems([
                'TransactItems' => [

                    // 1. Individual message - Eg. "I like water"
                    [
                        'Put' => [
                            'TableName' => 'mezenyoumsg',
                            'Item' => [
                                'cID' => ['S' => $conversationID],
                                'SK' => ['S' => $messageSK],
                                'messageID' => ['S' => $messageID],
                                'sID' => ['S' => $senderID],
                                'rID' => ['S' => $recipientID],
                                'role' => ['S' => $role],
                                'avatar' => ['S' => $myIcon],
                                'messageText' => ['S' => $messageText],
                                'isRead' => ['BOOL' => false],
                            ],
                        ]
                    ],

                    // 2. My message summary
                    [
                        'Put' => [
                            'TableName' => 'mezenyoumsg',
                            'Item' => [
                                'cID' => ['S' => $senderID],
                                'SK' => ['S' => $convSK],
                                'convID' => ['S' => $conversationID],
                                'otherID' => ['S' => $recipientID],
                                'lastMessage' => ['S' => $messageText],
                                'lastMessageAt' => ['N' => (string) $timestamp],
                                'avatar' => ['S' => $theirIcon],
                            ],
                        ]
                    ],

                    // 3. Their message summary
                    [
                        'Put' => [
                            'TableName' => 'mezenyoumsg',
                            'Item' => [
                                'cID' => ['S' => $recipientID],
                                'SK' => ['S' => $convSK],
                                'convID' => ['S' => $conversationID],
                                'otherID' => ['S' => $senderID],
                                'lastMessage' => ['S' => $messageText],
                                'lastMessageAt' => ['N' => (string) $timestamp],
                                'avatar' => ['S' => $myIcon],
                            ],
                        ]
                    ],

                ]
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

    public function getChatMessages(string $userID1, string $userID2, int $limit = 50): array
    {
        $marshaller = new Marshaler();
        $conversationID = $this->generateConversationID($userID1, $userID2);

        $result = $this->dynamo->query([
            'TableName' => 'mezenyoumsg',
            'KeyConditionExpression' => 'cID = :cid AND begins_with(SK, :prefix)',
            'ExpressionAttributeValues' => [
                ':cid' => ['S' => $conversationID],
                ':prefix' => ['S' => "MSG#"]
            ],
            'ScanIndexForward' => true,
            'Limit' => $limit,
        ]);

        $messageList = [];
        foreach ($result['Items'] as $rawItem) {
            $item = $marshaller->unmarshalItem($rawItem);
            $messageList[] = $item;

        }
        return $messageList;
    }

    public function getConversations(string $userID)
    {
        $marshaller = new Marshaler();

        try {
            $result = $this->dynamo->query([
                'TableName' => "mezenyoumsg",
                'KeyConditionExpression' => 'cID = :uid AND begins_with(SK, :prefix)',
                'ExpressionAttributeValues' => [
                    ':uid' => ['S' => $userID],
                    ':prefix' => ['S' => 'CONV#']
                ],
            ]);
            $messageList = [];
            foreach ($result['Items'] as $rawItem) {
                $item = $marshaller->unmarshalItem($rawItem);
                $messageList[] = $item;
            }
            return $messageList;
        } catch (Exception $e) {
            echo $e->getMessage();
            return [];

        }


    }
}
