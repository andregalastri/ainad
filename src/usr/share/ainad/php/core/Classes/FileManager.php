<?php

namespace Core\Classes;

/**
 * Contains methods related to file management.
 */
class FileManager 
{
    /**
     * Source: https://stackoverflow.com/a/2050909
     * - Author: Felix Kling
     *
     * This method copy the entire source directory to a destination directory.
     * PHP's native copy() function doesn't copy folders.
     *
     * @param  string $sourceDirectory  The directory that will be copied.
     *
     * @param  string $destinationDirectory     The destination folder that will
     *                                          receive the copy of the source
     *                                          directory.
     *
     * @param  string $childFolder      (Optional) Adds a child folder inside
     *                                  the destination directory and copies the
     *                                  source directory to this child folder.
     *
     * @return void
     */
    public static function copyDirectory(string $sourceDirectory, string $destinationDirectory, string $childFolder = '', array $ignorePaths = []): void
    {
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
                    self::copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file", '', $ignorePaths);
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
                self::copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$file", '', $ignorePaths);
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
     * Copy a file to a destination folder. If the folder doesn't exist, it will
     * be created automatically. 
     *
     * @param  mixed $sourceFile           The file that will be copied.
     *
     * @param  mixed $destinationFolder    The folder where the copy of the file
     *                                     will be placed.
     *
     * @return void
     */
    public static function copyFile(string $sourceFile, string $destinationFolder): void
    {
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder, 0777, true);
        }
        
        copy($sourceFile, $destinationFile.'/'.$sourceFile);
    }

    /**
     * Source: https://intecsols.com/delete-files-and-folders-from-a-folder-using-php-by-intecsols/
     * - Author: Syed Muhammad Waqas
     *
     * This method deletes the entire directory even if it has files inside it.
     * PHP's native rmdir() function doesn't remove folders with files inside.
     *
     * @param  string $directory        Directory that will be removed.
     *
     * @return void
     */
    public static function deleteAll(string $directory): void
    {
        foreach(glob($directory . '/*') as $file) {
            if(is_dir($file)) {
                self::deleteAll($file);
            } else {
                unlink($file);
            }
        }

        rmdir($directory);
    }
    
    /**
     * Abstraction of mkdir() function. Checks if the directory exists before
     * trying to create it.
     *
     * @param  string $path             The path of the directory.
     *
     * @return void
     */
    public static function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
    
    /**
     * Writes a content to a file, creating the directory path or file if they
     * don't exist.
     *
     * @param  mixed $path              The file path.
     *
     * @param  mixed $content           The content that will be write in the
     *                                  file.
     *
     * @param  mixed $mode              (Optional) Specifies the mode of access.
     *                                  Default is 'w', which means that the
     *                                  current content of the file will be
     *                                  erased and the new content will replace
     *                                  it.
     * 
     * @return void
     */
    public static function writeFile(string $path, string $content, string $mode = 'w'): void
    {
        $path = pathinfo($path);
        
        if (!is_dir($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }

        $file = $path['dirname'].'/'.$path['basename'];

        $fopen = fopen($file, $mode);
        fwrite($fopen, $content);
        fclose($fopen);
    }

    /**
     * Uses the PharData class to compress an entire directory into a tar.gz
     * file. Note that if the
     *
     * @param string $sourceDirectory   The directory that will be compressed.
     *
     * @param string $destinationFile   The destination file name. Must be .tar
     *                                  type.
     *
     * @param bool $overwrite           When true, overwrites any previous
     *                                  compressed file.
     *
     * @return void
     */    
    public static function compressDirectory(string $sourceDirectory, string $destinationFile, bool $overwrite = false): void
    {
        if (file_exists($destinationFile.'.gz')) {
            if ($overwrite) {
                unlink($destinationFile.'.gz');
            } else {
                throw new \Exception("A file with the same name already exists in the specified directory.", 1);
            }
        }

        $phar = new \PharData($destinationFile);
        $phar->buildFromDirectory($sourceDirectory);
        $phar->compress(Phar::GZ);

        unlink($destinationFile);
    }
    
    /**
     * writePhpVar
     *
     * @param  mixed $location
     * @param  mixed $value
     * @return void
     */
    public static function writePhpVar(string $location, mixed $value) {
        self::writeFile($location, "<?php\n\nreturn ".var_export($value, true).";\n");
    }
}
