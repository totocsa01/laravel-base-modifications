<?php
$appEnv = getAppEnv();

function syncAll(string $vendorPart): void
{
    $ds = DIRECTORY_SEPARATOR;

    $vendorDir = realpath(__DIR__ . "{$ds}..{$ds}$vendorPart");
    $publishDir = realpath(__DIR__ . "{$ds}$vendorPart");

    if (is_dir($vendorDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $vendorDir,
                RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $pattern = '/^' . preg_quote($vendorDir, '/') . '' . '\/[^\/]+' . preg_quote('/resources/assets/', '/') . '.*/';
        foreach ($iterator as $item) {
            /**@var SplFileInfo $item */

            $pathName = $item->getPathname();
            if ($item->isFile() && preg_match($pattern, $pathName)) {
                $itemDir = $item->getPath();
                $destDir = substr($itemDir, strlen($vendorDir));
                $destDir = $publishDir . str_replace("{$ds}resources{$ds}assets", '', $destDir);

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0775, true);
                    //echo "mkdir\n$destDir\n";
                }

                $from = $pathName;
                $to = $destDir . $ds . $item->getFilename();

                if (!file_exists($to) || filemtime($from) > filemtime($to)) {
                    copy($from, $to);
                    //echo "copy\n$from\n$to\n\n";
                }
            }
        }
    }
}

function getAppEnv(): string
{
    $ds = DIRECTORY_SEPARATOR;
    $appEnv = getenv('APP_ENV');

    if ($appEnv === false) {
        $envContent = file_get_contents(__DIR__ . "{$ds}..{$ds}.env");
        $envContentArray = array_reverse(explode(PHP_EOL, $envContent));
        $appEnvLine = '';
        $i = 0;
        while ($appEnvLine === '' && $i < count($envContentArray)) {
            $line = $envContentArray[$i];
            $matching = preg_match('/^APP_ENV\s{0,}=/', $line);
            if ($matching != 0) {
                $appEnvLine = $line;
            }

            $i++;
        }

        if ($appEnvLine !== '') {
            $appEnvLineArray = explode('=', $appEnvLine);
            if (count($appEnvLineArray) === 2) {
                $appEnv = $appEnvLineArray[1];
            }
        }
    }

    return strval($appEnv);
}
