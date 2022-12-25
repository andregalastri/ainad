#!/bin/bash

# ------
# This script updates the mirrorlist to prevent errors because of out of date
# mirrors.
# ------

sleep 10;
curl -s "https://archlinux.org/mirrorlist/?country=BR&protocol=https&protocol=http&use_mirror_status=on" | sed -e 's/^#Server/Server/' -e '/^#/d' | rankmirrors -n 5 - | sudo tee '/etc/pacman.d/mirrorlist-updated';
sudo cat '/etc/pacman.d/mirrorlist-updated' | sudo tee '/etc/pacman.d/mirrorlist';
sudo rm '/etc/pacman.d/mirrorlist-updated';
