#!/bin/bash

# ------
# This script has common functions to be used in the scripts.
# ------

# Kills the loop scripts.
#
# return void
function KillLoopScripts()
{
    # Gets all loop scripts PIDs and stores it in an array.
    readarray -t pidList < "$ainadBaseDir/scripts/loop-scripts/loop.pid";

    # For each PID, kills its sleep command and the loop script itself.
    for id in ${pidList[@]}; do
        pkill -P "$id" sleep;
        kill "$id";
    done;
    
    # Clears the loop.pid file.
    echo "" > "$ainadBaseDir/scripts/loop-scripts/loop.pid";
}
