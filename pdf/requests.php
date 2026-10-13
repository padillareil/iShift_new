<?php

// ob_start();
// error_reporting(0);
// @ini_set('display_errors', 0);
error_reporting(E_ALL);
@ini_set('display_errors', 1);
// require_once "../config/connection.php";
require_once "../assets/plugins/fpdf/fpdf.php";

$Hostname   = '';
$servername = "192.168.101.68";
$database = "ISHIFT_PICKLIST";
$username = "sa";
$password = "SB1Admin";
try {
    $conn = new PDO("sqlsrv:server=$servername;database=$database;TrustServerCertificate=true", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
    echo "Connection failed: " . $e->getMessage();
}

session_start();


// $PkList_Num = $_GET['PkList_Num'];
$PkList_Num = $_POST['PkList_Num'];
$Uid = $_SESSION['Uid'];

try {

    $picklistData = $conn->prepare("EXEC dbo.Picklist_PDF ?, ?");
    $picklistData->execute([$Uid, $PkList_Num]);

    // 1. Picklist Header
    $picklistHeader = $picklistData->fetch(PDO::FETCH_ASSOC);

    // 2. Picklist Items
    $picklistData->nextRowset();
    $picklistItems = $picklistData->fetchAll(PDO::FETCH_ASSOC);

    // 3. Destination and Stock Request Numbers
    $picklistData->nextRowset();
    $branchData = $picklistData->fetchAll(PDO::FETCH_ASSOC);

    $picklistData->closeCursor();

    $docDate = isset($picklistHeader['PickedDate'])
        ? date("m/d/Y", strtotime($picklistHeader['PickedDate']))
        : "N/A";

    $textColor = [50, 50, 50];

    // $executedby = $picklistHeader['Executedby'] ?? '';

    class PDF extends FPDF
    {
        public $categoryTitle;
        function Header()
        {
            $this->Image('../assets/image/logo/iap_icon.png', 10, 10, 20);
            // $this->SetFont('Arial', 'B', 35);
            $this->SetFont('Arial', 'B', 32);
            $this->SetTextColor(64, 64, 64);
            $pageWidth = $this->GetPageWidth();
            $this->SetX(12);
            $this->Cell($pageWidth - 15, 20, 'PICK LIST', 0, 1, 'C');
            $this->Ln(8);
        }
        function Footer()
        {
            // $this->Image('../assets/image/footer/footer.jpg', 10, 270, 190);
            $this->SetTextColor($GLOBALS['textColor'][0], $GLOBALS['textColor'][1], $GLOBALS['textColor'][2]);
            $this->SetY(-15);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(120, 120, 120);

            $this->Cell(
                0,
                10,
                'Page ' . $this->PageNo() . ' of {nb}',
                0,
                0,
                'C'
            );
        }
        function NbLines($w, $txt)
        {
            $cw = &$this->CurrentFont['cw'];

            if ($w == 0)
                $w = $this->w - $this->rMargin - $this->x;

            $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;

            $s = str_replace("\r", '', $txt);
            $nb = strlen($s);

            if ($nb > 0 && $s[$nb - 1] == "\n")
                $nb--;

            $sep = -1;
            $i = 0;
            $j = 0;
            $l = 0;
            $nl = 1;

            while ($i < $nb) {
                $c = $s[$i];

                if ($c == "\n") {
                    $i++;
                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                    continue;
                }

                if ($c == ' ')
                    $sep = $i;

                $l += $cw[$c];

                if ($l > $wmax) {
                    if ($sep == -1) {
                        if ($i == $j)
                            $i++;
                    } else
                        $i = $sep + 1;

                    $sep = -1;
                    $j = $i;
                    $l = 0;
                    $nl++;
                } else
                    $i++;
            }

            return $nl;
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();

    function headerDetails($pdf, $PkList_Num, $docDate, $category = '')
    {
        global $textColor;
        $labelWidth = 15;
        $colonWidth = 3;
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($labelWidth, 5, 'Picklist ', 0, 0);
        $pdf->Cell($colonWidth, 5, ':', 0, 0, 'C');
        $pdf->Cell(0, 5, $PkList_Num, 0, 1);
        /* ---------- ROW 3 (CATEGORY) ---------- */
        if (!empty($category)) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($labelWidth, 5, 'Category', 0, 0);
            $pdf->Cell($colonWidth, 5, ':', 0, 0, 'C');
            $pdf->Cell(0, 5, $category, 0, 1);
        }
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell($labelWidth, 5, 'Date ', 0, 0);
        $pdf->Cell($colonWidth, 5, ':', 0, 0, 'C');
        $pdf->Cell(0, 5, $docDate, 0, 1);
        $pdf->Ln(1);
    }

    function renderItemsTable($pdf, $PkList_Num, $picklistItems, $textColor)
    {
        /* ---------- TITLE ---------- */

        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 9);

        /* ---------- FIXED HEADERS ---------- */
        $headers = [
            '#' => 10,
            'Category' => 30,
            'Model' => 80,
            'Brand' => 30,
            'Quantity' => 15,
            'Actual Qty' => 20
        ];

        /* ---------- TABLE HEADER ---------- */

        foreach ($headers as $text => $width) {
            $pdf->Cell($width, 6, $text, 1, 0, 'C');
        }

        $pdf->Ln();
        $pdf->SetFont('Arial', '', 9);

        $lineHeight = 5;
        $counter = 1;

        $groupedItems = [];

        foreach ($picklistItems as $row) {
            $modelRaw = $row['ItemModel'] ?? '';
            $brandRaw = $row['ItemBrand'] ?? '';
            $categoryRaw = $row['ItemCategory'] ?? '';
            $quantity = (int)($row['BrnchCollectionRequest'] ?? 0);
            // $actual = isset($row['BrnchCollectionRequest'])
            //     && $row['BrnchCollectionRequest'] !== ''
            //     ? $row['BrnchCollectionRequest']
            //     : 0;
            $actual = '';

            $key = $modelRaw . '|' . $brandRaw . '|' . $categoryRaw;

            if (!isset($groupedItems[$key])) {
                $groupedItems[$key] = [
                    'Model' => $modelRaw,
                    'Brand' => $brandRaw,
                    'Category' => $categoryRaw,
                    'Quantity' => 0,
                    'Actual' => null
                ];
            }

            $groupedItems[$key]['Quantity'] += $quantity;
            $groupedItems[$key]['Actual'] = (int)$actual;
        }

        uasort($groupedItems, function ($a, $b) {

            $categoryCompare = strcasecmp($a['Category'], $b['Category']);

            if ($categoryCompare !== 0) {
                return $categoryCompare;
            }

            $modelCompare = strcasecmp($a['Model'], $b['Model']);

            if ($modelCompare !== 0) {
                return $modelCompare;
            }

            return strcasecmp($a['Brand'], $b['Brand']);
        });

        /* ---------- TABLE ROWS ---------- */
        foreach ($groupedItems as $row) {

            // Step 1: calculate max lines
            $categoryLines = $pdf->NbLines($headers['Category'], $row['Category']);
            $modelLines = $pdf->NbLines($headers['Model'], $row['Model']);
            $brandLines = $pdf->NbLines($headers['Brand'], $row['Brand']);
            $qtyLines = $pdf->NbLines($headers['Quantity'], number_format($row['Quantity'] ?? 0, 0));
            $actualLines = $pdf->NbLines($headers['Actual Qty'], $row['Actual'] !== null ? $row['Actual'] : '');

            $maxLines = max($brandLines, $modelLines, $categoryLines, $qtyLines, $actualLines, 1);
            $rowHeight = $lineHeight * $maxLines;

            $x = $pdf->GetX();
            $y = $pdf->GetY();

            // Step 2: draw cells with border first
            $pdf->Rect($x, $y, $headers['#'], $rowHeight); // Column #
            $pdf->Rect($x + $headers['#'], $y, $headers['Category'], $rowHeight);
            $pdf->Rect($x + $headers['#'] + $headers['Category'], $y, $headers['Model'], $rowHeight);
            $pdf->Rect($x + $headers['#'] + $headers['Category'] + $headers['Model'], $y, $headers['Brand'], $rowHeight);
            $pdf->Rect($x + $headers['#'] + $headers['Brand'] + $headers['Model'] + $headers['Category'], $y, $headers['Quantity'], $rowHeight);
            $pdf->Rect($x + $headers['#'] + $headers['Brand'] + $headers['Model'] + $headers['Category'] + $headers['Quantity'], $y, $headers['Actual Qty'], $rowHeight);

            // Column #
            $pdf->SetXY($x, $y);
            $pdf->MultiCell($headers['#'], $lineHeight, $counter, 0, 'C');

            // Category
            $pdf->SetXY($x + $headers['#'], $y);
            $pdf->MultiCell($headers['Category'], $lineHeight, $row['Category'], 0);

            // Model
            $pdf->SetXY($x + $headers['#'] + $headers['Category'], $y);
            $pdf->MultiCell($headers['Model'], $lineHeight, $row['Model'], 0);

            // Brand
            $pdf->SetXY($x + $headers['#'] + $headers['Category'] + $headers['Model'], $y);
            $pdf->MultiCell($headers['Brand'], $lineHeight, $row['Brand'], 0);

            // Quantity
            $pdf->SetXY($x + $headers['#'] + $headers['Category'] + $headers['Model'] + $headers['Brand'], $y);
            $pdf->MultiCell($headers['Quantity'], $lineHeight, number_format($row['Quantity'] ?? 0, 0), 0, 'C');

            // Actual Qty
            $pdf->SetXY($x + $headers['#'] + $headers['Category'] + $headers['Model'] + $headers['Brand'] + $headers['Quantity'], $y);
            $pdf->MultiCell(
                $headers['Actual Qty'],
                $lineHeight,
                ($row['Actual'] > 0) ? $row['Actual'] : 0,
                0,
                'C'
            );
            // Step 4: move Y by full row height
            $pdf->SetY($y + $rowHeight);

            $counter++;
        }
    }

    // function bottomLeftDetails($pdf, $picklistItems, $textColor)
    function bottomLeftDetails($pdf, $picklistHeader, $branchData, $textColor)
    {
        $pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $pdf->Ln(5); // gap after table

        $leftX = 10;
        $rightX = 160; // adjust depending on your page width

        $startY = $pdf->GetY();

        /** ---------------- LEFT COLUMN ---------------- **/
        $pdf->SetXY($leftX, $startY);

        // Requesting Branch
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, 'Requesting Branch', 0, 1);

        $pdf->SetX($leftX);
        $pdf->SetFont('Arial', '', 9);
        $lines = [];

        foreach ($branchData as $item) {
            $branch = $item['Destination_Loc'] ?? '';
            $srn = $item['SR_Num'] ?? '';

            if ($branch || $srn) {
                $lines[] = "{$branch} - {$srn}";
            }
        }
        $lines = array_unique($lines);

        $pdf->SetFont('Arial', '', 9);
        foreach ($lines as $line) {
            $pdf->SetX($leftX);
            $pdf->Cell(80, 5, $line, 0, 1);
        }

        $pdf->Ln(2);

        // Printed By
        $pdf->SetX($leftX);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, 'Printed by', 0, 1);

        $pdf->SetX($leftX);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, $picklistHeader['Configby'], 0, 1);

        $pdf->SetX($leftX);
        $pdf->Cell(0, 5, '10/13/2026', 0, 1);


        /** ---------------- RIGHT COLUMN ---------------- **/
        $pdf->SetXY($rightX, $startY);

        if ($picklistHeader['Executedby'] && !empty($picklistHeader['Executedby'])) {
            // Executed By
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(60, 5, 'Executed by', 0, 1);

            $pdf->SetX($rightX);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(60, 5, $picklistHeader['Executedby'], 0, 1);
        }

        $pdf->Ln(3);
    }

    $categorizedItems = [];

    foreach ($picklistItems as $item) {
        $category = $item['ItemCategory'] ?? 'Uncategorized';

        if (!isset($categorizedItems[$category])) {
            $categorizedItems[$category] = [];
        }

        $categorizedItems[$category][] = $item;
    }

    $pdf->categoryTitle = '';
    $pdf->AddPage();
    headerDetails($pdf, $PkList_Num, $docDate, '');
    renderItemsTable($pdf, $PkList_Num, $picklistItems, $textColor);
    // bottomLeftDetails($pdf, $executedby, $picklistHeader, $branchData, $textColor);
    bottomLeftDetails($pdf, $picklistHeader, $branchData, $textColor);
    // }
    ob_end_clean();
    $pdf->Output('I', $PkList_Num . '.pdf');
} catch (PDOException $e) {
    // $conn->rollBack();
    errorHandler(E_WARNING, $e->getMessage(), $e->getFile(), $e->getLine());
}
