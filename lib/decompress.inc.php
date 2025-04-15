<?
function DecompressFile($path, $mime = 'text/plain') {
    switch ($mime) {
        case 'text/plain':
            $file = fopen($path, 'rb');
            $content = fread($file, filesize($path));
            fclose($file);
            break;
        case 'application/x-gzip':
            if (@function_exists('gzopen')) {
                $file = gzopen($path, 'rb');
                $content = '';
                while (!gzeof($file)) {
                    $content .= gzread($file,10000);
                }
                gzclose($file);
            } else {
                return FALSE;
            }
           break;
        case 'application/x-bzip':
            if (@function_exists('bzdecompress')) {
                $file = fopen($path, 'rb');
                $content = fread($file, filesize($path));
                fclose($file);
                $content = bzdecompress($content);
            } else {
                return FALSE;
            }
           break;
        default:
           return FALSE;
    }
    if (!file_exists($path)) {
        return FALSE;
    }
    return $content;
}
?>
