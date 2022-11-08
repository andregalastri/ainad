#!/bin/bash

# ------
# This script runs on startup of the system and on reloading the environment.
# ------

# Importing functions.
source "$ainadBaseDir/scripts/functions.sh";

# Kills programs and loop scripts that will be rerun by this script.
killall -9 polybar parcellite picom ksuperkey;
KillLoopScripts;

# Checks if polkit is running. If not, starts it.
if [[ ! `pidof polkit-mate-authentication-agent-1` ]]; then
    "/usr/lib/mate-polkit/polkit-mate-authentication-agent-1" &
fi;

# Starts the power manager.
xfce4-power-manager &

# Restore the current wallpaper.
nitrogen --restore &

# Set the network interface.
ainad-utilities 'HardwareDefinition' 'setNetworkInterface';

# Starts the Polybar panels. It is important to understand that AINAD uses two
# panels, one as background and another with the modules. 
#
# It is done this way because of the Polybar tray module. The tray module has a
# problem that doesn't allow to have rounded borders, which makes the visuals
# weird. So, to make it work properly, the background panel has rounded corners
# and the foreground panel has its background color transparent. The tray module
# is positioned a little off to the left.
#
# The background panel is launched first so that it is placed behind the front
# panel.
polybar -q 'bg' -c "$ainadBaseDir/polybar/bar-bg.ini" &
sleep 0.5;
polybar -q 'main' -c "$ainadBaseDir/polybar/bar-main.ini" &

# Store the panels PIDs to be used by AINAD Utilities.
ainad-utilities 'Polybar' 'storePolybarPids' &

# Starts the Parcellite clipboard manager.
parcellite &

# Starts the Picom compositor.
picom &

# Loads the Ksuperkey. Sets the super key to run the Ctrl + Alt + Shift + F1
# keybind, because Openbox recognize the super key as a modifier key, not a
# common key.
ksuperkey -e 'Super_L=Control_L|Alt_L|Shift_L|F1' &
ksuperkey -e 'Super_R=Control_L|Alt_L|Shift_L|F1' &

# Starts the loop scripts.
"$ainadBaseDir/scripts/loop-scripts/2-seconds.sh" &
"$ainadBaseDir/scripts/loop-scripts/1-hour.sh" &
