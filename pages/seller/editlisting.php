<?php
require_once __DIR__ . "/../../api/utils/cors.php";
require '../../api/session.php';
require __DIR__ . "/../../api/utils/aws.php";
require __DIR__ . "/../../api/utils/AWSCLIENTS.php";
require __DIR__ . "/../../api/session.php";
$conn = require '../../api/conn.php';

$ACCESS = require __DIR__ . "/../../api/config.php";

$product = isset($_GET['id']) && $_GET['id'] !== '' ? (string) $_GET['id'] : null;
$user_id = $_SESSION['user_id'];

$product_name = $_POST['product_name'] ?? null;
$price = $_POST['price'] ?? null;
$category = $_POST['category'] ?? null;
$location = $_POST['location'] ?? null;
$author = $_POST['author'] ?? null;
$stock = $_POST['stock'] ?? null;
$description = $_POST['description'] ?? null;

$message = null;

try {
    //initial load
    $statement = $conn->prepare("SELECT id, seller_id, product_name, price, category, location, author, stock, descriptiontxt FROM Products WHERE id = :id");
    $statement->execute([":id" => $product]);
    
    $productData = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$productData) {
    http_response_code(404);
    exit("Product not found");
}

    if ($productData['seller_id'] != $_SESSION['user_id']) {
    http_response_code(403);
    exit("You are not allowed to edit this listing");
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("PDO ERROR: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['image'] ?? null;

    if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {

        // Actual upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {

            http_response_code(400);
            $message = "FILE ERROR";
            exit;
        }

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        $realType = mime_content_type($file['tmp_name']);

        if (!in_array($realType, $allowedTypes)) {

            http_response_code(400);
            $message = "Invalid File Type";
            exit;
        }

        $s3 = createS3Client($ACCESS);
        $aws = new AWSservice($s3, null);

        $image_url = $aws->uploadProductImage($product_id, $file['tmp_name']);
        if ($image_url === "" || !$image_url) {
            $message = "INTERNAL SERVER ERROR ON UPLOAD";
            exit;
        }

    } else {
        $message = "No Image Uploaded.";
    }

    // {Form field: Database column}
    $fieldMap = [
        'product_name' => 'product_name',
        'price' => 'price',
        'category' => 'category',
        'location' => 'location',
        'author' => 'author',
        'stock' => 'stock',
        'description' => 'descriptiontxt',
    ];

    $updates = [];
    $params = [];

    foreach ($fieldMap as $formField => $dbColumn) {
        $submittedValue = $_POST[$formField] ?? null;
        $databaseValue = $productData[$dbColumn] ?? null;
        if (is_string($submittedValue)) {
            $submittedValue = trim($submittedValue);
        }

        if (is_string($databaseValue)) {
            $databaseValue = trim($databaseValue);
        }
        if ((string) $submittedValue !== (string) $databaseValue) {

            $updates[] = "{$dbColumn} = :{$formField}";
            $params[":{$formField}"] = $submittedValue;
        }
    }

    //Did something change or not?
    if (!empty($updates)) {
        $sql = "
            UPDATE Products
            SET " . implode(", ", $updates) . "
            WHERE id = :id
        ";

        $params[':id'] = $product;

        $statement = $conn->prepare($sql);
        $statement->execute($params);
        $message = "Listing updated successfully.";
    } else {
        $message = "No Changes Detected.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/dist/output.css" />
    <script src="/variables.js"></script>
    <script src="/javascript/includefooter.js"></script>
    <script src="/javascript/includetopnav.js"></script>
    <title>Edit Listing</title>
</head>

<body>
    <!--Reusable component-->
    <div id="topnav"></div>


<?php if (!empty($message)): ?>
    <div id="flashMessage" class="bg-white text-black border-4 border-normalred p-4 rounded-md w-2/5 mx-auto mt-4">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

 
    <form method="post" enctype="multipart/form-data" id="listingsubmitid"
        class="flex flex-col w-full lg:w-2/5 my-5 mx-auto p-10 space-y-4 bg-white rounded-md">
        <h2 class="text-2xl font-extrabold text-center">Product Editing</h2>
        <input id="name" type="text" name="product_name" placeholder="Product Name"
            class="border-b-4 border-gray-700 px-2 py-3"
            value="<?= htmlspecialchars($productData['product_name'] ?? '') ?>">
        <input id="price" type="number" name="price" class="border-b-4 border-gray-700 px-2 py-3"
            placeholder="Product Price" value="<?= htmlspecialchars($productData['price'] ?? '') ?>">
        <select title="Select Category" name="category" id="categoryselect"
            class="border-b-4 border-gray-700 px-2 py-3">
            <option value="Novels" <?= ($productData['category'] ?? '') === 'Novels' ? 'selected' : '' ?>>Novels</option>
            <option value="Comics/Manga" <?= ($productData['category'] ?? '') === 'Comics/Manga' ? 'selected' : '' ?>>
                Comics/Manga</option>
            <option value="Video Games" <?= ($productData['category'] ?? '') === 'Video Games' ? 'selected' : '' ?>>Video
                Games</option>
            <option value="Electronics" <?= ($productData['category'] ?? '') === 'Electronics' ? 'selected' : '' ?>>
                Electronics</option>
            <option value="Furniture" <?= ($productData['category'] ?? '') === 'Furniture' ? 'selected' : '' ?>>Furniture
            </option>
            <option value="Office" <?= ($productData['category'] ?? '') === 'Office' ? 'selected' : '' ?>>Office</option>
            <option value="Beauty" <?= ($productData['category'] ?? '') === 'Beauty' ? 'selected' : '' ?>>Beauty</option>
            <option value="Collectibles" <?= ($productData['category'] ?? '') === 'Collectibles' ? 'selected' : '' ?>>
                Collectibles</option>
            <option value="Other" <?= ($productData['category'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
        <input id="location" type="text" name="location" class="border-b-4 border-gray-700 px-2 py-3"
            placeholder="Location" value="<?= htmlspecialchars($productData['location'] ?? "") ?>">
        <input id="author" type="text" name="author" class="border-b-4 border-gray-700 px-2 py-3"
            placeholder="Creator Eg.If reselling an Iphone, list Apple"
            value="<?= htmlspecialchars($productData['author'] ?? "") ?>">
        <input min="1" max="1000" id="stock" type="number" name="stock" class="border-b-4 border-gray-700 px-2 py-3"
            placeholder="Stock" value="<?= htmlspecialchars($productData['stock'] ?? "") ?>">
        <input id="image" type="file" name="image" class="border-b-4 border-gray-700 px-2 py-3" placeholder="File">
        <div class="border-2 border-current p-5 bg-white">
            <textarea id="desc" title="DESCTYPE" name="description" class="w-full h-full outline-none"
                placeholder="Write your item and sales description here, the more specific the better"><?= htmlspecialchars($productData['descriptiontxt'] ?? "") ?></textarea>
        </div>

        <button type="submit" class="bg-red-600 p-3 rounded-md text-white">Submit</button>

    </form>

    <!--Reusable component-->
    <div id="footer"></div>

    <style>
    .fade-out {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>

<script>
    setTimeout(() => {
        const msg = document.getElementById("flashMessage");
        if (msg) {
            msg.classList.add("fade-out");

            setTimeout(() => {
                msg.remove();
            }, 500); 
        }
    }, 3000);
</script>

</body>

</html>