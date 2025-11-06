<?php
// -----------------------------
// CONFIGURATION & DB CONNECTION
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    exit('Only POST requests allowed.');
}

header('Content-Type: text/plain; charset=utf-8');

require_once '../../../config/constants.php';
require_once '../../../database/db_connect.php';
require_once '../../../utilities/inventory/InventoryHelpers.php';

// -----------------------------
// BRANDS CATEGORY (Dynamic from DB)
// -----------------------------
$AvailableBrands = getBrands();
$brandsEnglishName = array_column($AvailableBrands, 'name');
$brandsPersianName = array_column($AvailableBrands, 'persian_name');
$customBrands = array_combine(array_map('strtoupper', $brandsEnglishName), $brandsPersianName);

// -----------------------------
// FUNCTIONS
// -----------------------------
function getPersianName($partNumber)
{
    $stmt = PDO_CONNECTION->prepare("SELECT partName FROM yadakshop.nisha WHERE partnumber LIKE :partnumber LIMIT 1");
    $stmt->execute(['partnumber' => "%$partNumber%"]);
    $row = $stmt->fetch();
    return $row['partName'] ?? null;
}

function roundUpToHundred($num)
{
    return $num;
}

function getBrandOrigin($brand)
{
    global $customBrands;

    $brand = strtoupper(trim($brand));

    // 🔹 Known short forms for main brands
    if (in_array($brand, ['MOB', 'GEN'])) {
        return 'اصلی';
    }

    // 🔹 Direct match from DB mapping
    if (isset($customBrands[$brand])) {
        return $customBrands[$brand];
    }

    // 🔹 Try partial match (e.g., "MB KOREA" or "OEM CHINA")
    foreach ($customBrands as $eng => $per) {
        if (str_contains($brand, $eng)) {
            return $per;
        }
    }

    // 🔹 Fallback
    return 'اصلی';
}

function parsePriceText($text)
{
    $result = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($text));

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        if (preg_match('/^(\S+)\s*:\s*(.+)$/', $line, $matches)) {
            $code = $matches[1];
            $values = $matches[2];

            $priceParts = array_map('trim', explode('/', $values));
            $prices = [];

            foreach ($priceParts as $part) {
                // 1️⃣ اگر عدد + برند دارد
                if (preg_match('/(\d+)\s+([A-Z]+)(?:\s*\(([^)]+)\))?/', $part, $pmatch)) {
                    $price = (int)$pmatch[1];
                    $brand = $pmatch[2];
                    $note = $pmatch[3] ?? null;

                    $finalBrand = getBrandOrigin($brand);
                    if ($note) {
                        $finalBrand .= " ($note)";
                    }
                }
                // 2️⃣ اگر فقط عدد دارد
                elseif (preg_match('/^\d+$/', $part)) {
                    $price = (int)$part;
                    $finalBrand = 'اصلی';
                } else {
                    continue; // خطا یا مقدار نامعتبر
                }

                $prices[] = [
                    'price' => roundUpToHundred($price),
                    'brand' => $finalBrand
                ];
            }

            $result[] = [
                'code' => $code,
                'persian_name' => getPersianName($code) ?? $code,
                'prices' => $prices
            ];
        }
    }

    return $result;
}

function generateFormattedText($parsedResults)
{
    $lines = [];

    foreach ($parsedResults as $item) {
        $line = $item['persian_name'] ?? $item['code'];

        if (!empty($item['prices'])) {
            $priceParts = [];
            foreach ($item['prices'] as $p) {
                $priceParts[] = number_format($p['price'], 0, '', ',') . '  ' . $p['brand'];
            }
            $line .= ' : ' . implode(' / ', $priceParts);
        } else {
            $line .= ' : -';
        }

        $lines[] = $line;
    }

    $lines[] = "\nقیمت ها در واحد هزار تومان می باشد.";
    return implode("\n", $lines);
}

// -----------------------------
// MAIN PROCESS
// -----------------------------
$input = $_POST['codes'] ?? '';

if (empty($input)) {
    exit("هیچ کدی ارسال نشده است.");
}

$parsedResults = parsePriceText($input);
echo generateFormattedText($parsedResults);
