#!/bin/bash

# ------
# This script runs the commands every hour.
# ------

# Stores the PID of this script to allows it to be killed when needed.
echo $$ >> "$ainadBaseDir/bash/loops/loop.pid";

# Forever loop that runs every hour.
while true; do

    # Runs the Polybar hook that checks for updates. It waits for 20 seconds to
    # prevent errors on startup.
    sleep 120;
    ainad-utilities 'Updater' 'checkUpdates';

    # Waits 1 hour.
    sleep 3480;
done;
