#!/bin/bash

pkexec env DISPLAY=$DISPLAY XAUTHORITY=$XAUTHORITY dbus-launch nemo "$1";
