<?php
// Incluye la librería PhpSpreadsheet
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Realiza la conexión a tu base de datos (cambia los valores según tu configuración)
$servername = "localhost";
$username = "root";
$password = "";
$database = "inventario";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para obtener todos los datos de los productos
$sql = "SELECT * FROM producto";
$result = $conn->query($sql);

// Consulta SQL para obtener los nombres de usuario
$userQuery = "SELECT usuario_id, usuario_nombre FROM usuario";
$userResult = $conn->query($userQuery);
$userMap = [];
while ($row = $userResult->fetch_assoc()) {
    $userMap[$row['usuario_id']] = $row['usuario_nombre'];
}

// Crea una nueva instancia de Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Definir estilo de borde para las celdas con información de la base de datos
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Definir estilos para encabezados de columna
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
];

// Aplicar estilo de borde y encabezados de columna
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
$sheet->getStyle('A1:G1')->applyFromArray($borderStyle);

// Agrega encabezados de columna
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Código');
$sheet->setCellValue('C1', 'Nombre');
$sheet->setCellValue('D1', 'Precio');
$sheet->setCellValue('E1', 'Stock');
$sheet->setCellValue('F1', 'Categoría');
$sheet->setCellValue('G1', 'Usuario'); // Cambiado de 'ID de Usuario' a 'Usuario'

// Llena los datos de los productos y aplica estilo de borde
if ($result && $result->num_rows > 0) {
    $rowIndex = 2;
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A'.$rowIndex, $row['producto_id']);
        $sheet->setCellValue('B'.$rowIndex, $row['producto_codigo']);
        $sheet->setCellValue('C'.$rowIndex, $row['producto_nombre']);
        $sheet->setCellValue('D'.$rowIndex, $row['producto_precio']);
        $sheet->setCellValue('E'.$rowIndex, $row['producto_stock']);
        $sheet->setCellValue('F'.$rowIndex, $row['categoria_id']);
        $sheet->setCellValue('G'.$rowIndex, $userMap[$row['usuario_id']]); // Utiliza el nombre del usuario
        // Aplicar estilo de borde a cada celda
        $sheet->getStyle('A'.$rowIndex.':G'.$rowIndex)->applyFromArray($borderStyle);
        
        $rowIndex++;
    }
}

// Autoajustar el ancho de las columnas
foreach(range('A','G') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Crea un objeto Writer para guardar el archivo Excel
$writer = new Xlsx($spreadsheet);

// Nombre del archivo Excel generado
$filename = 'productos.xlsx';

// Guarda el archivo Excel en el servidor
$writer->save($filename);

// Cierra la conexión
$conn->close();

// Envía los encabezados HTTP para forzar la descarga del archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.basename($filename).'"');
header('Cache-Control: max-age=0');

// Leer el archivo y enviarlo al cliente
readfile($filename);

// Elimina el archivo Excel del servidor después de enviarlo
unlink($filename);

// Termina el script
exit;
?>