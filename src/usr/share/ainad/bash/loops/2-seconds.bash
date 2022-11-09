#!/bin/bash

# ------
# This script runs the commands every 2 seconds.
# ------

# Stores the PID of this script to allows it to be killed when needed.
echo $$ >> "$ainadBaseDir/bash/loops/loop.pid";

# Forever loop that runs every 2 seconds.
while true; do
    # Runs the Polybar commnad that checks if the current window is in
    # fullscreen (to hide it and prevent the issue of the tray icon remaining on
    # top of fullscreen content).
    ainad-utilities 'Polybar' 'hideOnFullscreen';

    # Runs the Polybar command that refreshes the taskbar.
    ainad-utilities 'TaskBar' 'refreshTasks';

    # Waits 2 seconds.
    sleep 2;
done;
