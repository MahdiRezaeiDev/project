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

// -----------------------------
// BRANDS CATEGORY
// -----------------------------
$brands = [
    'KOREA' => [
        'YONG',
        'YONG HOO',
        'OEM',
        'ONNURI',
        'GY',
        'MIDO',
        'MIRE',
        'CARDEX',
        'MANDO',
        'OSUNG',
        'DONGNAM',
        'HYUNDAI BRAKE',
        'SAM YUNG',
        'BRC',
        'GEO SUNG',
        'YULIM',
        'CARTECH',
        'HSC',
        'KOREA STAR',
        'DONI TEC',
        'ATC',
        'VALEO',
        'MB KOREA',
        'FAKE MOB',
        'FAKE GEN',
        'IACE',
        'MB',
        'PH',
        'CAP',
        'BRG',
        'GMB',
        'KGC',
        'GATES',
        'KOART',
        'SAEHAN',
        'FORCEONE',
        'DAEWHA',
        'AUTOFIX',
        'BOSUNG'
    ],
    'CHINA' => [
        'OEMAX',
        'JYR',
        'RB2',
        'Rb2',
        'IRAN',
        'FAKE MOB',
        'FAKE GEN',
        'OE MAX',
        'MAXFIT',
        'ICBRI',
        'HOH'
    ]
];

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
    global $brands;
    $brand = strtoupper(trim($brand));

    if (in_array($brand, ['MOB', 'GEN'])) {
        return 'اصلی';
    }

    // 🔹 handle direct or short forms of KOREA and CHINA
    if (in_array($brand, ['KOREA', 'KOR', 'KR'])) return 'کره';
    if (in_array($brand, ['CHINA', 'CHN', 'CN'])) return 'چینی';

    // 🔹 detect substring matches like "MB KOREA" or "OEM CHINA"
    if (str_contains($brand, 'KOREA')) return 'کره';
    if (str_contains($brand, 'CHINA')) return 'چینی';

    foreach ($brands['KOREA'] as $kBrand) {
        if (strcasecmp($brand, $kBrand) == 0) return 'کره';
    }

    foreach ($brands['CHINA'] as $cBrand) {
        if (strcasecmp($brand, $cBrand) == 0) return 'چینی';
    }

    return $brand; // اگر برند ناشناخته بود
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
                // حفظ note (مثل LR)
                if (preg_match('/(\d+)\s+([A-Z]+)(?:\s*\(([^)]+)\))?/', $part, $pmatch)) {
                    $price = (int)$pmatch[1];
                    $brand = $pmatch[2];
                    $note = $pmatch[3] ?? null;

                    $finalBrand = getBrandOrigin($brand);
                    if ($note) {
                        $finalBrand .= " ($note)";
                    }

                    $prices[] = [
                        'price' => roundUpToHundred($price),
                        'brand' => $finalBrand
                    ];
                }
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
