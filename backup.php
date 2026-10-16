<?php 

// Schedule this script to run once per day via cron.
//
// Retention policy:
// - Daily backups: today + yesterday
// - One backup per day of the month
// - One backup per month of the year
// - One backup per year
//
// After one year, total backup storage is typically around 50–60 times
// the size of a single backup.
//
// Uploaded files are intentionally excluded. If user uploads contain
// important data, they should be backed up separately.

// IMPORTANT: Verify all paths before adding this script to cron.

$today_backup = '../backup_today.zip';
$yesterday_backup = '../backup_yesterday.zip';
$day_backup = '../backup_day_'.date('d').'.zip';
$month_backup = '../backup_month_'.date('m').'.zip';
$year_backup = '../backup_year_'.date('Y').'.zip';
$mysql_backup_prefix = 'backup_mysql_';
$config_file = 'custom/config.php';
$mappings_file = 'custom/mappings.php';

// End of configurable paths.

if (!empty($_GET['get'])) {
    // Clave secreta definida en el servidor (usa una cadena larga y aleatoria)
    $valid_key = md5('jorge');

    // 1. Validar la clave enviada usando hash_equals para prevenir timing attacks
    if (empty($_GET['key']) || !hash_equals($valid_key, $_GET['key'])) {
        http_response_code(403);
        die('Error: Clave de acceso invalida.');
    }

    // 2. Validar existencia del archivo
    if (!isset($today_backup) || !file_exists($today_backup)) {
        http_response_code(404);
        die('Error: El archivo de backup no existe.');
    }

    // 3. Desactivar limite de tiempo para descargas pesadas
    set_time_limit(0);

    // 4. Limpiar cualquier bufer de salida previo para evitar corromper el archivo
    while (ob_get_level()) {
        ob_end_clean();
    }

    // 5. Encabezados HTTP para la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($today_backup) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($today_backup));

    readfile($today_backup);
    exit;
}

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be executed from CLI');
}

$lockFile = fopen(__DIR__ . '/backup.lock', 'c');

if (!$lockFile) {
    die('Cannot create lock file');
}

if (!flock($lockFile, LOCK_EX | LOCK_NB)) {
    die('Another backup is already running');
}

require_once('include/SuppleApplication.php');
require_once('include/ifsnop-mysqldump/Mysqldump.php');

// Export all configured MySQL databases.
$mysql_backups = array();
foreach($db->mappings['MysqlConnection'] as $db_name => $m){
    $db_pass = $m['password'];
    $db_user = $m['user'];
    $host = $m['host'];

	$mysql_backup = $mysql_backup_prefix . $db_name . '.sql';
	if (file_exists($mysql_backup)) unlink($mysql_backup);

	$dump = new Ifsnop\Mysqldump\Mysqldump('mysql:host='.$host.';dbname='.$db_name, $db_user, $db_pass, array('add-drop-table' => true));
	$dump->start($mysql_backup);

	$mysql_backups[] = $mysql_backup;
}

// Build the backup archive.
if (file_exists($yesterday_backup)) unlink($yesterday_backup);
if (file_exists($today_backup)){
    rename($today_backup, $yesterday_backup);
}
$zip = new ZipArchive;
$res = $zip->open($today_backup, ZipArchive::CREATE);
if ($res === TRUE){
    
	// Include configuration files.
	if (file_exists($config_file)) $zip->addFile($config_file);
	if (file_exists($mappings_file)) $zip->addFile($mappings_file);

	// Include phpArrayDB data and metadata.
    zipAddGlob($zip, 'phpArrayDB/default/*.php');
    zipAddGlob($zip, 'phpArrayDB/metadata/*.php');
    zipAddGlob($zip, 'phpArrayDBcore/*.php');

	// Include all files from the custom directory.
	$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('custom'), RecursiveIteratorIterator::SELF_FIRST);
	foreach ($files as $file) {
		$file = str_replace('\\', '/', $file);
		if (is_file($file) === true){
			$zip->addFile($file);
		}
	}

	// Include uploaded files.
	/*$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('uploaded'), RecursiveIteratorIterator::SELF_FIRST);
	foreach ($files as $file) {
		$file = str_replace('\\', '/', $file);
		if (is_file($file) === true){
			$zip->addFile($file);
		}
	}*/

	// Include MySQL dumps.
	foreach ($mysql_backups as $mysql_backup){
		$zip->addFile($mysql_backup);
	}

    $zip->close();

} else {
    echo "Zip ERROR:" . $res ;
    // TODO: Send an alert email.
}

// Update daily, monthly and yearly snapshots.
if (file_exists($day_backup)) unlink($day_backup);
copy($today_backup, $day_backup);
if (file_exists($month_backup)) unlink($month_backup);
copy($today_backup, $month_backup);
if (file_exists($year_backup)) unlink($year_backup);
copy($today_backup, $year_backup);

// Remove temporary SQL dump files.
foreach ($mysql_backups as $mysql_backup){
	if (file_exists($mysql_backup)) unlink($mysql_backup);
}


flock($lockFile, LOCK_UN);
fclose($lockFile);



function zipAddGlob($zip, $pat){
	foreach (glob($pat) as $file) {
		$zip->addFile($file);
	}
}
