#!/bin/bash

# ------
# This script runs on shutting down, rebooting or logging off the system.
# ------

# Importing functions.
source "$ainadBaseDir/bash/functions.bash";

# Kills programs and loop scripts.
killall -9 polybar parcellite ksuperkey;
KillLoopScripts;

# Runs the logoff script of the current user, if it exists.
if [ -f "$HOME/.config/openbox/logoff" ]; then
    "$HOME/.config/openbox/logoff";
fi;

# Runs the logoff script of the current user, if it exists.
if [ -d "$HOME/.config/logoff" ]; then
    # Gets all files from the ~/.config/logoff directory of the current user and
    # stores it in an array.
    readarray -d " " -t logoffList<<<"$(ls "$HOME/.config/logoff/"*)";

    # For each file of the folder, checks:
    for logoffApp in ${logoffList[@]}; do

        # If it is a .desktop file, runs it via gio launch.
        if [[ "$logoffApp" == *".desktop" ]]; then
            gio launch "$logoffApp" &
        fi;

        # If it is a .sh or .bash file, then it just runs the file.
        if [[ "$logoffApp" == *".sh" || "$logoffApp" == *".bash" ]]; then
            $logoffApp &
        fi;
    done;
fi;
