<?php

function isWritable($fileOrDirectory): void
{
    if (!is_writable($fileOrDirectory)) {
        echo "
            The following folder doesn't have writing permissions: $fileOrDirectory\n
            Grant write permissions to the folder and run the updater again.
        ";
        exit;
    }
}

function copyDirectory(string $sourceDirectory, string $destinationDirectory, string $childFolder = '', array $ignorePaths = []): void {
    $directory = opendir($sourceDirectory);

    if (is_dir($destinationDirectory) === false) {
        mkdir($destinationDirectory);
    }

    if ($childFolder !== '') {
        if (is_dir("$destinationDirectory/$childFolder") === false) {
            mkdir("$destinationDirectory/$childFolder");
        }

        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("$sourceDirectory/$file") === true) {
                copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file", '', $ignorePaths);
            } else {
                foreach ($ignorePaths as $ignore) {
                    if ($ignore == substr($sourceDirectory, 0, strlen($ignore))) {
                        return;
                    }
                }
                copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            }
        }

        closedir($directory);

        return;
    }

    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        if (is_dir("$sourceDirectory/$file") === true) {
            copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$file", '', $ignorePaths);
        }
        else {
            foreach ($ignorePaths as $ignore) {
                if ($ignore == substr($sourceDirectory, 0, strlen($ignore))) {
                    return;
                }
            }
            copy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
    }

    closedir($directory);
}

function copyFile($sourceFile, $destinationFile) {
    $path = pathinfo($destinationFile);

    if (!is_dir($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }
    
    copy($sourceFile, $destinationFile);
}

function deleteAll(string $directory): void
{
    foreach(glob($directory . '/*') as $file) {
        if(is_dir($file)) {
            deleteAll($file);
        } else {
            unlink($file);
        }
    }

    rmdir($directory);
}

function createDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

function writeFile(string $path, string $value, string $type = 'w'): void
{
    $path = pathinfo($path);
    
    if (!is_dir($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }

    $file = $path['dirname'].'/'.$path['basename'];

    $fopen = fopen($file, $type);
    fwrite($fopen, $value);
    fclose($fopen);
}

function compressDirectory(string $directoryPath, string $destinationPath): void
{
    if (file_exists($destinationPath.'.gz')) {
        unlink($destinationPath.'.gz');
    }

    $phar = new PharData($destinationPath);
    $phar->buildFromDirectory($directoryPath);
    $phar->compress(Phar::GZ);

    unlink($destinationPath);
}

/**
 * Other changes
 */

/**
 * picomChanges
 *
 * @param  mixed $configDir
 * @return void
 */
function picomChanges(): void
{
    $changeDetected = false;
    $configFile = $_SERVER['HOME']."/.config/picom.conf";

    $content = file_get_contents($configFile);

    foreach ([
        "\"class_g = 'thunderbird' && window_type = 'menu'\",",
        "\"class_g = 'thunderbird' && window_type = 'dropdown_menu'\",",
        "\"class_g = 'thunderbird' && window_type = 'popup_menu'\",",
        "\"class_g = 'thunderbird' && window_type = 'tooltip'\",",
    ] as $contentToAdd) {
        if (strpos($content, $contentToAdd) === false) {
            $content = preg_replace("/(^shadow-exclude = \[[\n])(.*?)(];)/ms", "$1  $contentToAdd\n$2$3", $content);
            $changeDetected = true;
        }
    }
    
    if ($changeDetected) {
        copy($configFile, "$configFile.backup");
        writeFile($configFile, $content);
    }
}

/**
 * sddmChanges
 *
 * @return void
 */
function sddmChanges(): void
{
    $changeDetected = false;
    $configFile = "/etc/sddm.conf.d/sddm.conf";

    $content = file_get_contents($configFile);
    
    if (strpos($content, "[General]") === false) {
        $content = "[General]\n\n$content";
        $changeDetected = true;
    }
    
    if (strpos($content, "Numlock = ") === false) {
        $content = preg_replace("/(^\[General]\n)(.*)(\[)|(^\[General]\n)(.*)/ms", "$1# Numlock = on\n$2$3", $content);
        $changeDetected = true;
    }
    
    if ($changeDetected) {
        $content = trim($content);
        exec("cp -r \"$configFile\" \"$configFile\.backup\"");
        exec("echo \"$content\" | sudo tee \"$configFile\"");
    }
}

picomChanges();
sddmChanges();
