<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Aws\DynamoDb\Marshaler;


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

    public function generateImageURL(string $key){
        if (!$key){
            echo "MISSING KEY";
            return null;
        }
        $url = "{$this->imgbaseurl}/$key";

        return $url ;
    }

    #AWS DynamoDB-------------------------
    public function retrieveProductReviews($productID)
    {
        $marshaler = new Marshaler();
        try {

            $result = $this->dynamo->query([
                'TableName' => $this->tableName,
                'KeyConditionExpression' => 'pID = :pid',
                'ExpressionAttributeValues' => [
                    ':pid' => ['S' => $productID]
                ]
            ]);

            $cleanItems = [];
            foreach ($result['Items'] as $item) {
                $cleanItems[] = $marshaler->unmarshalItem($item);
            }

            usort($cleanItems, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        
            return $cleanItems;
        } catch (Exception $e) {
            echo "Failed to extract review with error: " . $e->getMessage();
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
                        "pID" => [
                            'S' => $productID
                        ],
                        'timestamp' => [
                            'N' => time(),
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
