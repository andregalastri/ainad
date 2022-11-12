<?php

$home = getenv('HOME');
$ainadBaseDir = getenv('ainadBaseDir');

return [
    $home.'/Images/Backgrounds/*',
    $home.'/Imagens/Backgrounds/*',
    $home.'/Imagens/Wallpapers/*',
    $ainadBaseDir.'/backgrounds/*'
];
