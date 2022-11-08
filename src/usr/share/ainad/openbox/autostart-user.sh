#!/bin/bash

# ------
# This script gets all files from autostart directory of the current user and
# runs on startup of the system. It isn't run on reloading the environment.
# ------

# Gets all files from the ~/.config/autostart directory (if it exists) of the
# current user and stores it in an array.
if [ -d "$HOME/.config/autostart" ]; then
    readarray -d " " -t autostartList<<<"$(ls "$HOME/.config/autostart/"*)";

    # For each file of the folder, checks:
    for autostartApp in ${autostartList[@]}; do

        # If it is a .desktop file, runs it via gio launch.
        if [[ "$autostartApp" == *".desktop" ]]; then
            gio launch "$autostartApp" &
        fi;

        # If it is a .sh or .bash file, then it just runs the file.
        if [[ "$autostartApp" == *".sh" || "$autostartApp" == *".bash" ]]; then
            $autostartApp &
        fi;
    done;
fi