<?php

/**
 * isWritable
 *
 * @param  mixed $fileOrDirectory
 * @return void
 */
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

/**
 * Source: https://stackoverflow.com/a/2050909
 * Author: Felix Kling
 * 
 * This function copy the entire source directory to a destination directory. PHP's native copy()
 * function doesn't copy folders, much less do it recursively.
 *
 * @param  string $sourceDirectory                  The directory that will be copied.
 * 
 * @param  string $destinationDirectory             The destination folder that will receive the
 *                                                  copy of the source directory.
 * 
 * @param  string $childFolder                      (Optional) Adds a child folder inside the
 *                                                  destination directory and copies the source
 *                                                  directory to this child folder.
 * 
 * @return void
 */
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

/**
 * copyFile
 *
 * @param  mixed $sourceFile
 * @param  mixed $destinationFile
 * @return void
 */
function copyFile($sourceFile, $destinationFile) {
    $path = pathinfo($destinationFile);

    if (!is_dir($path['dirname'])) {
        mkdir($path['dirname'], 0777, true);
    }
    
    copy($sourceFile, $destinationFile);
}

/**
 * Source: https://intecsols.com/delete-files-and-folders-from-a-folder-using-php-by-intecsols/
 * Author: Syed Muhammad Waqas
 * 
 * This function delete the entire directory even if it has files inside it. PHP's native rmdir()
 * function doesn't remove folders with files inside.
 *
 * @param  string $directory                              Directory that will be removed.
 * 
 * @return void
 */
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

/**
 * compressDirectory
 *
 * @param  mixed $directoryPath
 * @param  mixed $destinationPath
 * @return void
 */
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
