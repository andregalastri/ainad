#!/bin/bash

directoryMain=$1

extensions=("\.zip" "\.7z" "\.ar" "\.cbz" "\.cpio" "\.exe" "\.iso" "\.jar" "\.tgz" "\.tar\.Z" "\.tar\.bz2" "\.tar\.gz" "\.tar\.lzma" "\.tar\.lz" "\.tar\.xz" "\.tar" "\.rar")

for i in ${extensions[*]}
do
    directoryNew=$(echo "$directoryMain" | sed "s/\(.*\)$i/\1/")
    if [ "$directoryMain" != "$directoryNew" ];
    then
        directoryMain=$directoryNew
        break
    fi
done

increment=0
directoryExists=1

directoryTest=$directoryMain
while
    if [ -d "$directoryTest" ]; then
        increment=$(($increment+1))
        directoryTest="$directoryMain ($increment)"
    else
        directoryExists=0
    fi

    [ $directoryExists != 0 ]
do :;  done

mkdir -p "$directoryTest"
engrampa -e "$directoryTest" "$1"
