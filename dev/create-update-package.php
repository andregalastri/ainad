<?php

if (!isset($argv[1])) {
    echo "Inform the number of heads\n";
    exit;
}

$numOfHeads = $argv[1];

define('REPO_DIR', $_SERVER['HOME']."/Documentos/Github/andregalastri/ainad");
define('VERSIONS', require(REPO_DIR.'/VERSIONS.php'));
define('UPDATE_PACKAGES_DIR', REPO_DIR.'/dist/update-packages/'.VERSIONS['ainad']);

require(REPO_DIR.'/dev/libs/fileManager.php');

exec('
    pandoc '.REPO_DIR.'/README.md -o '.REPO_DIR.'/less/en_US.txt;
    pandoc '.REPO_DIR.'/README.pt_BR.md -o '.REPO_DIR.'/less/pt_BR.txt;
');

foreach (["en_US.txt", "pt_BR.txt"] as $lessFile) {
    $output = file_get_contents(REPO_DIR.'/less/'.$lessFile);
    $output = preg_replace("/\*/", "", $output);
    $output = preg_replace("/`/", "", $output);
    $output = preg_replace("/<br \/>{=html}/", "", $output, 5);
    $output = preg_replace("/^!\[AINAD Screenshot].*?(\n\nWHY AINAD IS NOT A DISTRO)/ms", "$1", $output);
    $output = preg_replace("/^!\[AINAD Screenshot].*?(\n\nPOR QUÊ O AINAD NÃO É UMA DISTRO)/ms", "$1", $output);
    $output = preg_replace("/<br \/>{=html}/", "----------------------------------------", $output);
    $output = preg_replace("/^\[🇧🇷 Versão em.*?\n(ABOUT AINAD)/ms", "$1", $output);
    writeFile(REPO_DIR.'/less/'.$lessFile, $output);
}

copy(REPO_DIR.'/VERSIONS.php', REPO_DIR.'/src/usr/share/ainad/VERSIONS.php');

$ainadVersion = file_get_contents(REPO_DIR.'/ainad.bash');
$ainadVersion = preg_replace("/^ainad\[9\]=.*/m", "ainad[9]=\"  ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀ v".VERSIONS['ainad']." ▀▀\";", $ainadVersion);
writeFile(REPO_DIR.'/ainad.bash', $ainadVersion);

compressDirectory(REPO_DIR.'/src', REPO_DIR.'/dist/ainad.tar');

$storedChanges = [
    'added' => [],
    'modified' => [],
    'removed' => [],
];

for ($heads = $numOfHeads; $heads >= 0; $heads--) {
    exec('cd '.REPO_DIR.'; git show --name-status HEAD~'.$heads, $gitChanges);
    
    foreach ($gitChanges as $data) {
        preg_match('/[R]\d.*?\s.*?src(.+?)\s+?src(.+)/', $data, $matches);
        if (!empty($matches[0])) {
            $storedChanges['removed'][] = $matches[1];
            $storedChanges['added'][] = $matches[2];
        }

        preg_match('/[UA]\s.*?src(.+)/', $data, $matches);
        if (!empty($matches[0])) {
            $storedChanges['added'][] = $matches[1];
        }

        preg_match('/[M]\s.*?src(.+)/', $data, $matches);
        if (!empty($matches[0])) {
            $storedChanges['modified'][] = $matches[1];
        }

        preg_match('/[D]\s.*?src(.+)/', $data, $matches);
        if (!empty($matches[0])) {
            $storedChanges['removed'][] = $matches[1];
        }
    }

    $storedChanges['added'] = array_values(array_unique($storedChanges['added']));
    $storedChanges['removed'] = array_values(array_unique($storedChanges['removed']));
    $storedChanges['modified'] = array_values(array_unique($storedChanges['modified']));

    foreach ($storedChanges['removed'] as $removedKey => $file) {
        $modifiedKey = array_search($file, $storedChanges['modified']);
        if($modifiedKey !== false) {
            unset($storedChanges['modified'][$modifiedKey]);
            // unset($storedChanges['removed'][$removedKey]);
        }
    }

    foreach ($storedChanges['removed'] as $removedKey => $file) {
        $addedKey = array_search($file, $storedChanges['added']);
        if($addedKey !== false) {
            unset($storedChanges['added'][$addedKey]);
            unset($storedChanges['removed'][$removedKey]);
        }
    }
    
    foreach ($storedChanges['modified'] as $modifiedKey => $file) {
        $addedKey = array_search($file, $storedChanges['added']);
        if($addedKey !== false) {
            unset($storedChanges['modified'][$modifiedKey]);
        }
    }
}

writeFile(UPDATE_PACKAGES_DIR.'/all-changes.php', "<?php\nreturn ".var_export($storedChanges, true).";\n");

$updateFiles = [
    'added' => [],
    'modified' => [],
    'removed' => [],
];

foreach ($storedChanges['added'] as $addedFile) {
    preg_match('/\/usr.*/', $addedFile, $matches);
    if (!empty($matches[0])) {
        $updateFiles['added'][] = $matches[0];
    }
}

foreach ($storedChanges['modified'] as $modifiedFile) {
    preg_match('/\/usr.*/', $modifiedFile, $matches);
    if (!empty($matches[0])) {
        $updateFiles['modified'][] = $matches[0];
    }
}

foreach ($storedChanges['removed'] as $removedFile) {
    preg_match('/\/usr.*/', $removedFile, $matches);
    if (!empty($matches[0])) {
        $updateFiles['removed'][] = $matches[0];
    }
}

foreach ($updateFiles['added'] as $addedFile) {
    copyFile(REPO_DIR.'/src'.$addedFile, UPDATE_PACKAGES_DIR.'/added'.$addedFile);
}

foreach ($updateFiles['modified'] as $modifiedFile) {
    copyFile(REPO_DIR.'/src'.$modifiedFile, UPDATE_PACKAGES_DIR.'/modified'.$modifiedFile);
}

writeFile(UPDATE_PACKAGES_DIR.'/package.php', "<?php\nreturn ".var_export($updateFiles, true).";\n");



// if (file_exists(UPDATE_PACKAGES_DIR.'.tar.gz')) {
//     unlink(UPDATE_PACKAGES_DIR.'.tar.gz');
// }

// $phar = new PharData(UPDATE_PACKAGES_DIR.'.tar');
// $phar->buildFromDirectory(UPDATE_PACKAGES_DIR);
// $phar->compress(Phar::GZ);

// unlink(UPDATE_PACKAGES_DIR.'.tar');

