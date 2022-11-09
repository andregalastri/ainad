#!/bin/bash

# ------
# This script sends commands for the Updater utility. It is a modi for ROFI and
# needs to be launch with it.
# ------

# Changes the status of the update package. If it is selected, then it is set to
# unselected. If it is unselected, then changes it to selected.
#
# It sends the position of the button, going from 0 to 9, to defines which
# package was selected. More information about that in the Updater controller
# class, in the php directory.
#
# @param int $1                         The position of the selected packaged.
#
# @return void
function SetPackageSelected()
{
    ainad-utilities 'Updater' 'setPackageSelected' $1;

    # This pause is needed to avoid ROFI reopen too soon.
    sleep 0.07;
    killall -9 rofi;
    ainad-utilities 'Updater' 'launchUpdater';
};

# Changes the page, when the number of packages exceeds 10.
#
# @param string $1                      Defines to go to the next or the
#                                       previous page.
#
# @return void
function SetPage() {
    ainad-utilities 'Updater' 'setPage' $1;

    # This pause is needed to avoid ROFI reopen too soon.
    sleep 0.07;
    killall -9 rofi;
    ainad-utilities 'Updater' 'launchUpdater';
};

# Open the terminal to install the selected updates.
#
# @return void
function ApplyUpdates() {
    sleep 0.07;
    ainad-utilities 'Updater' 'applyUpdates';
};

# Allows hotkeys, which is needed to make the buttons work as intended.
echo -e "\0use-hot-keys\x1ftrue";

# Detects which button was selected. Each $ROFI_RETV value represents a
# kb-custom-<num> value. The <num> starts with 1, that is represented by 10 in
# the $ROFI_RETV variable, and goes on to 19, that is represented by 28 in the
# $ROFI_RETV variable.
case $ROFI_RETV in
    10) SetPackageSelected 0;;
    11) SetPackageSelected 1;;
    12) SetPackageSelected 2;;
    13) SetPackageSelected 3;;
    14) SetPackageSelected 4;;
    15) SetPackageSelected 5;;
    16) SetPackageSelected 6;;
    17) SetPackageSelected 7;;
    18) SetPackageSelected 8;;
    19) SetPackageSelected 9;;
    25) SetPage "next";;
    26) SetPage "previous";;
    27) ApplyUpdates;;
esac

echo -e "-";
