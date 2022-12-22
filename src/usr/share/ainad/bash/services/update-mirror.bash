#!/bin/bash

# ------
# This script updates the mirrorlist to prevent errors because of out of date
# mirrors.
# ------

curl -s "https://archlinux.org/mirrorlist/?country=BR&protocol=https&protocol=http&use_mirror_status=on" | sed -e 's/^#Server/Server/' -e '/^#/d' | rankmirrors -n 5 - | sudo tee '/etc/pacman.d/mirrorlist';
