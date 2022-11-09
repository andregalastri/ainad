#!/bin/bash

# ------
# Global definitions.
# ------

# Stores the Ascii art of AINAD name and version.
#
# @var array $ainad
ainad[0]="       ░█████╗░██╗███╗░░██╗░█████╗░██████╗░     ";
ainad[1]="       ██╔══██╗╚═╝████╗░██║██╔══██╗██╔══██╗     ";
ainad[2]="       ███████║██║██╔██╗██║███████║██║░░██║     ";
ainad[3]="       ██╔══██║██║██║╚████║██╔══██║██║░░██║     ";
ainad[4]="       ██║░░██║██║██║░╚███║██║░░██║██████╔╝     ";
ainad[5]="       ╚═╝░░╚═╝╚═╝╚═╝░░╚══╝╚═╝░░╚═╝╚═════╝░     ";
ainad[6]="╔═════════════════════════════════════════════╗ ";
ainad[7]="║              IS  NOT  A  DISTRO             ║█";
ainad[8]="╚═════════════════════════════════════════════╝█";
ainad[9]="  ▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀▀ v0.2.1-alpha ▀▀";

# Stores the number of lines used by the $ainad array.
#
# @var int $titleLines
titleLines=${#ainad[*]};

# Stores the number of characters used by the $ainad array.
#
# @var int $titleColumns
titleColumns=${#ainad[0]};

# Speed of the animation of the intro (in seconds per line).
#
# @var float $titleScreenSpeed
titleScreenSpeed=0.02;

# Speed of text writing when calling the Write function (in seconds per
# character).
#
# @var float $textSpeed
textSpeed=0.02;

# The available languages. The first value is empty because the options given to
# the user always starts in 1, not zero.
#
# @var array $availableLanguages
availableLanguages=("" "en_US" "pt_BR");

# The current locale.
#
# @var string $locale
locale=$(locale | grep 'LANG=' | sed 's/LANG=//');

# The current language.
#
# @var string $language.
language=$(echo $locale | cut -d '.' -f 1);

# ------
# Functions
# ------

# Centers the AINAD logo by subtracting the number of available columns and the
# number of columns used by the logo itself. The result is divided by 2 and the
# result represents how many spaces are needed to be added on the left of each
# line of AINAD logo to center it in the terminal.
#
# @return void
function CenterTitle()
{
    local usedColumns=$titleColumns;
    local availableColumns=$(tput cols);
    local numberOfSpaces=$(( ( availableColumns-usedColumns )/2 ));
    local spaces=$(printf %$(echo $numberOfSpaces)s);

    for (( i=0; i<$titleLines; i++ )); do
        ainad[$i]="$spaces${ainad[$i]}";
    done;

    titleColumns=${#ainad[0]};
};

# Prints the AINAD logo doing an animation. The animation makes the logo comes
# from left to the right and stops at the center of the screen.
#
# @return void
function AinadTitleScreenAnimation()
{
    # Gets the how many character the logo has. It defines where the last
    # position the animation needs to start printing.
    local previousChar=$titleColumns;

    sleep 1;

    # Loops for each character, getting the number of columns used by the logo.
    for (( i=0; i<$titleColumns; i++ )); do

        # Each run of the loop needs to get the last character of each line,
        # starting from the last char position and going on towards to the first
        # character of each line. That is why it decrements the previous char on
        # each loop run.
        (( previousChar-- ));

        clear;
        echo -e "\n\n";

        # This loop prints all lines of the logo based on the number of indexes
        # of the $ainad array.
        for (( j=0; j<$titleLines; j++ )); do

            # Store the current previousChar and append what have been already
            # printed, to make the ilusion of moving.
            printLine[$j]="${ainad[$j]:$previousChar:1}${printLine[$j]}";

            echo -e "${printLine[$j]}";
        done;

        sleep $titleScreenSpeed;
    done;
    echo -e "\n\n";
};

# Prints the AINAD logo, but without doing the animation.
#
# @return void
function AinadTitle()
{
    echo -e "\n\n";

    # This loop prints all lines of the logo based on the number of indexes
    # of the $ainad array.
    for (( i=0; i<$titleLines; i++ )); do
        echo -e "${ainad[$i]}";
    done;

    echo -e "\n\n";
};

# Write the text doing an animation like it is been writing character by
# character. This function do not detect a max number of character per-line to
# line break so, because of that, it will write down until it reach the end of
# the screen.
#
# @param string $1                      The text that will be written.
#
# @param int|float $2                   The delay to proceed after the animation
#                                       ends (in seconds). Default is 0.
#
# @return void
function Write()
{
    local text="$1";
    local delay=0;
    local escapeChar='';

    # Loops on each character to print one by one to create the illusion of
    # typing.
    for (( i=0; i<${#text}; i++ )); do

        # Detects escaped characters (that starts with \). In this case, it
        # stores the \ temporarily and does not print it for now. It proceed
        # with the loop waiting to be printed along the next character.
        #
        # This is because escaped characters uses 2 characters, but the result
        # in the output is one character.
        if [[ "${text:$i:1}" = '\' && "$escapeChar" = '' ]]; then
            escapeChar='\';
            continue;
        fi;

        # Stores the current character that will be printed and prepends a
        # escaped character if there is any.
        local char="$escapeChar${text:$i:1}";

        echo -en "$char";

        # Clears the escaped character if there is any to keep it empty.
        if [[ "$escapeChar" = '\' ]]; then
            escapeChar='';
        fi;

        # Always increment the current char to scan each character of the text
        # that will be printed, starting from the first character towards to the
        # end.
        (( currentCharQty++ ));

        sleep $textSpeed;
    done;

    # If parameter 2 is informed, then, it replaces the default value of $delay
    # variable.
    if [[ "$2" != "" ]]; then
        delay=$2;
    fi;

    sleep $delay;
};

# Do the same as Write() function, but it automatically line breaks after a
# defined number of max character per line.
#
# @param string $1                      The text that will be written.
#
# @param int|float $2                   The delay to proceed after the animation
#                                       ends (in seconds). Default is 0.
#
# @return void
function Describe()
{
    local text="$1";
    local numCharPerLine=68;
    local currentCharQty=0;
    local lineComposing='';
    local phrase='';
    local delay=1;
    readarray -d " " -t wordsArray <<<"$text";

    # Loops on each word of the given text, concatenating them to compose lines.
    for word in ${wordsArray[*]}; do

        composingTest="$lineComposing$word ";

        # If the number of the characters of line exceeds the $numCharPerLine
        # limit, then the line that is being composed has an \n at the and
        # before continuing the process.
        if (( ${#composingTest} <= numCharPerLine )); then
            lineComposing="$composingTest";
        else
            phrase+="$lineComposing\n";
            lineComposing="$word ";
        fi;

    done;

    # If parameter 2 is informed, then, it replaces the default value of $delay
    # variable.
    if [[ "$2" != "" ]]; then
        delay=$2;
    fi;

    Write "$phrase$lineComposing\n" $delay;
};

# Just write the text informing the conclusion of a stage of the installation.
#
# @return void
function DoneStage()
{
    Write "$textDone" 3;
};

# The first part of the script. Asks the user to inform the language of
# installation.
#
# @return void
function SectionChooseLanguage()
{
    while true; do
        clear;
        AinadTitle;
        sleep 1;

        Describe "Select the language to proceed:" 0;
        Write "   1) en_US\n";
        Write "   2) pt_BR\n";
        Write "\nChoose: ";
        read language;

        Write "\n\n";

        if [[ "${availableLanguages[$language]}" = "" ]]; then
            Describe "Invalid option." 1;
            Describe "Please, choose one of the available options." 2;
        else
            Describe "Please wait...";
            source <(curl -s "$baseUrl/languages/${availableLanguages[$language]}.sh");
            return;
        fi;
    done;
};

# The second part of the script. Asks the user to inform the what he/she wants
# to do.
#
# @return void
function SectionChooseInstall()
{
    while true; do
        clear;
        AinadTitle;

        Describe "$textWelcome";
        Describe "$textInstallOptionList" 0;
        Write "$textInstallOption1";
        Write "$textInstallOption2";
        Write "$textInstallOption3";
        Write "$textChoose";
        read installOption;

        Write "\n\n";

        case $installOption in
            1)
                Describe "$textAskSudo" 0;

                sudo echo "";

                if [[ "$?" != "0" ]]; then
                    Write "\n\n";
                    Describe "$textFailedSudo" 3;
                else
                    Describe "$textPleaseWait" 2;
                    return;
                fi;
                ;;
            2)
                echo -e "$(
                    AinadTitle;
                    curl -s "$baseUrl/less/${availableLanguages[$language]}.txt";
                )" | less;
                ;;
            3)
                Describe "$textInstallChoice3" 1;
                exit;
                ;;
            *)
                Describe "$textInvalidOption1" 1;
                Describe "$textInvalidOption2" 2;
                ;;
        esac;
    done;
};

# The last part of the script. Installs AINAD.
#
# @return void
function SectionInstall()
{
    # Keeps SUDO alive while the script is running.
    while true; do sudo -n true; sleep 60; kill -0 "$$" || exit; done 2>/dev/null &

    clear;
    AinadTitle;

    Describe "$textStartingInstallation";

    # Stage: Enable sudo password feedback.
    Describe "$textConfiguringSudo" 2;

    echo "Defaults pwfeedback" | sudo tee -a "/etc/sudoers" 1>/dev/null;

    DoneStage;

    # Stage: Enable parallel downloads of Pacman.
    Describe "$textParallelDownloads" 2;

    sudo sed -i "s/#ParallelDownloads = /ParallelDownloads = /" "/etc/pacman.conf";

    DoneStage;

    # Stage: Set a default mirror list based on Worldwide. It is needed to
    # prevent error, if the mirror list is too old or not defined. Later, it
    # will be updated with Reflector.
    Describe "$textSettingDefaultMirrorlist";

    curl -Ls https://archlinux.org/mirrorlist/all/ | awk "/^## Worldwide/,/^## A/" | sed -e 's/#Server/Server/' -e 's/^## A.*//' | sudo tee /etc/pacman.d/mirrorlist;

    DoneStage;

    # Stage: Installs and configures Pacman keys to prevent problems regarding
    # PGP keys.
    Describe "$textPacmanKey";

    sudo pacman-key --init;
    sudo pacman-key --populate;
    (echo "y") | LANG=C sudo pacman -Syy --needed archlinux-keyring;

    DoneStage;


    # Stage: Updates Arch Linux.
    Describe "$textUpdatingCurrentSystem";

    (echo "y") | LANG=C sudo pacman -Syyu;

    DoneStage;


    # Stage: Installs Reflector and gets the latest 5 mirrors that has the best
    # speed.
    Describe "$textUpdatingMirrorlist";

    (echo "y") | LANG=C sudo pacman -S reflector;
    sudo reflector --verbose --latest 5 --sort rate --save /etc/pacman.d/mirrorlist

    DoneStage;


    # Stage: Installs applications from official repository.
    Describe "$textInstallingNewApps1";
    Describe "$textInstallingNewApps2" 4;

    # Xorg
    packages+=("xorg-server xorg-xev");

    # SDDM
    packages=("sddm noto-fonts qt5-graphicaleffects qt5-quickcontrols2");

    # Kernel headers
    packages+=("linux-headers");

    # Openbox
    packages+=("openbox");

    # Xdotool
    packages+=("xdotool");

    # PHP
    packages+=("php php-intl");

    # Man - Manual interface
    packages+=("man-db");

    # WMCtrl
    packages+=("wmctrl");

    # Reflector
    packages+=("reflector");

    # XFCE Terminal
    packages+=("xfce4-terminal");

    # Nemo File Manager
    packages+=("nemo cinnamon-translations");

    # Engrampa
    packages+=("engrampa");

    # Mate Polkit
    packages+=("mate-polkit");

    # File system formats and integration
    packages+=("gvfs gvfs-nfs gvfs-mtp gvfs-gphoto2 gvfs-google gvfs-goa gvfs-afc ntfs-3g");

    # Samba
    packages+=("samba gvfs-smb cifs-utils");

    # Mousepad
    packages+=("mousepad");

    # Nano
    packages+=("nano");

    # Qalculate
    packages+=("qalculate-gtk");

    # Detecting the GPU driver.
    gpuString="$(lspci -nn | grep '\[03')";

    if [[ "$(echo "$gpuString" | grep -i 'nvidia')" != "" ]]; then
        # Nvidia drivers
        packages+=("nvidia nvidia-lts nvidia-utils nvidia-settings");
    fi;

    if [[ "$(echo "$gpuString" | grep -i 'vmware')" != "" ]]; then
        # VMWare drivers
        packages+=("virtualbox-guest-iso virtualbox-guest-utils xf86-video-vmware");
    fi;

    if [[ "$(echo "$gpuString" | grep -i 'intel')" != "" ]]; then
        # Intel drivers
        packages+=("vulkan-intel xf86-video-intel");
    fi;

    # AMD/ATI drivers These are the only GPU packages that are installed no
    # matter which GPU is installed.
    packages+=("vulkan-radeon xf86-video-amdgpu xf86-video-ati");

    # Nitrogen
    packages+=("nitrogen");

    # GIT
    packages+=("git");

    # Pacman scripts
    packages+=("pacman-contrib");

    # Gnome Keyring
    packages+=("gnome-keyring");

    # GTK2FontSel
    packages+=("gtk2fontsel");

    # Dunst
    packages+=("dunst");

    # Polybar
    packages+=("polybar dbus-python playerctl");

    # Rofi
    packages+=("rofi dmenu");

    # Flameshot
    packages+=("flameshot");

    # Viewnior
    packages+=("viewnior");

    # Xreader
    packages+=("xreader");

    # Arandr
    packages+=("arandr");

    # Lxrandr
    packages+=("lxrandr");

    # LXTask
    packages+=("lxtask");

    # LXInput-GTK3
    packages+=("lxinput-gtk3");

    # Pavucontrol
    packages+=("pavucontrol");

    # XFCE4 Power Manager
    packages+=("xfce4-power-manager");

    # LXAppearance
    packages+=("lxappearance lxappearance-obconf");

    # Kvantum
    packages+=("kvantum");

    # QT Settings
    packages+=("qt5ct");

    # Network Settings
    packages+=("networkmanager nm-connection-editor");

    # Picom
    packages+=("picom");

    # Fonts
    packages+=("noto-fonts-cjk noto-fonts-emoji");

    (echo "y") | LANG=C sudo pacman -S --needed ${packages[*]};

    DoneStage;

    # Stage: Installs Yay AUR helper.
    Describe "$textInstallingYay";

    (echo "1"; echo "y") | LANG=C sudo pacman -S --needed base-devel;
    git clone https://aur.archlinux.org/yay.git;
    cd yay;
    (echo "y") | LANG=C makepkg -s --clean;
    (echo "y") | LANG=C makepkg -i;

    yay -Syy;

    # When installing Yay, it installs Go package to compile it, but it is
    # useless to keep it installed, so, it is removed to save disk space (about
    # 400mb).
    (echo "y") | LANG=C sudo pacman -R go;

    cd "$HOME";

    DoneStage;

    # Stage: Installs applications from AUR.
    Describe "$textInstallingAdditionalApps";

    # Rar
    LANG=C yay --answerdiff None --answerclean All --removemake --needed -S rar;

    # Google Chrome
    (echo "y") | LANG=C yay --answerdiff None --answerclean All --removemake --needed -S google-chrome;

    # Warsaw
    LANG=C yay --answerdiff None --answerclean All --removemake --needed -S warsaw-bin;

    # Parcellite
    (echo "y") | LANG=C yay --answerdiff None --answerclean All --removemake --needed -S parcellite;

    # Dmenu for Network Manager
    LANG=C yay --answerdiff None --answerclean All --removemake --needed -S networkmanager-dmenu-git;

    # KSuperKey
    LANG=C yay --answerdiff None --answerclean All --removemake --needed -S ksuperkey;

    # Fonts
    (echo "y") | LANG=C yay --answerdiff None --answerclean All --removemake --needed -S ttf-roboto-mono ttf-roboto ttf-century-gothic;
    fc-cache -f -v;

    DoneStage;

    # Stage: Downloads configuration files and utilities of AINAD.
    Describe "$textSettingUpConfigs";


    Describe "$textDownloadingConfig";

    mkdir -p "$HOME/ainad";
    curl "$baseUrl/dist/ainad.tar.gz" -o "$HOME/ainad.tar.gz";
    curl "$baseUrl/dist/flat-remix-yellow.tar.xz" -o "$HOME/flat-remix-yellow.tar.xz";
    curl "$baseUrl/dist/fluent-cursors.tar.gz" -o "$HOME/fluent-cursors.tar.gz";


    Describe "$textCopyingConfigFiles";

    tar -xzf "ainad.tar.gz" -C "$HOME/ainad/";

    cp -r "$HOME/ainad/home/user/." "$HOME/";
    sudo cp -r "$HOME/ainad/home/user/." "/root/";

    sudo cp -r "$HOME/ainad/etc/." "/etc/";
    sudo cp -r "$HOME/ainad/usr/." "/usr/";
    sudo cp -r "$HOME/ainad/nemo-mount-allow.rules" "/etc/polkit-1/rules.d/nemo-mount-allow.rules";

    sudo tar -xf "flat-remix-yellow.tar.xz" -C "/usr/share/icons/";
    sudo tar -xzf "fluent-cursors.tar.gz" -C "/usr/share/icons/";


    Describe "$textConfiguringUserPermissions";

    sudo groupadd ainad;
    sudo gpasswd -a root ainad;
    sudo gpasswd -a $USER ainad;
    sudo chgrp -R ainad /usr/share/ainad;
    sudo chmod g+rwx -R /usr/share/ainad;
    sudo chgrp -R ainad /usr/share/sddm/themes/sugar-candy;
    sudo chmod g+rwx -R /usr/share/sddm/themes/sugar-candy;
    sudo chmod +x /usr/bin/ainad-utilities;

    Describe "$textSettingEnvironmentVariables";

    environmentFile="/etc/environment";

    if [ ! -f "$environmentFile" ]; then
        sudo touch "$environmentFile";
        echo -e "#
# This file is parsed by pam_env module
#
# Syntax: simple "KEY=VAL" pairs on separate lines
#
" | sudo tee -a "$environmentFile" 1>/dev/null;
    fi;

    echo -e "
QT_QPA_PLATFORMTHEME=qt5ct
GTK_THEME=Arc-Lighter
ainadBaseDir=/usr/share/ainad
" | sudo tee -a "$environmentFile" 1>/dev/null;


    Describe "$textConfiguringNetworkManager";

    sudo "$HOME/ainad/networkmanager_dmenu_languages.sh";
    sudo ln -s "/usr/share/ainad/rofi/widgets/networkmanager-dmenu" "$HOME/.config/networkmanager-dmenu";


    Describe "$textConfiguringSamba";

    sudo sed -i "s/netbios name = <user-name>/netbios name = $HOSTNAME/" "/etc/samba/smb.conf";


    Describe "$textConfiguringPhp";

    sudo sed -i -e "s/^;extension=intl/extension=intl/" "/etc/php/php.ini";


    Describe "$textConfiguringCinnamonTerminal";

    gsettings set org.cinnamon.desktop.default-applications.terminal exec xfce4-terminal;
    sudo dbus-launch gsettings set org.cinnamon.desktop.default-applications.terminal exec xfce4-terminal;


    Describe "$textConfiguringNemo";

    gsettings set org.nemo.preferences enable-delete false;
    sudo dbus-launch gsettings set org.nemo.preferences enable-delete false;
    gsettings set org.nemo.preferences confirm-move-to-trash true;
    sudo dbus-launch gsettings set org.nemo.preferences confirm-move-to-trash true;
    gsettings set org.nemo.preferences default-folder-viewer list-view;
    sudo dbus-launch gsettings set org.nemo.preferences default-folder-viewer list-view;
    gsettings set org.nemo.preferences inherit-folder-viewer false;
    sudo dbus-launch gsettings set org.nemo.preferences inherit-folder-viewer false;
    gsettings set org.nemo.preferences size-prefixes 'base-10';
    sudo dbus-launch gsettings set org.nemo.preferences size-prefixes 'base-10';
    gsettings set org.nemo.preferences show-location-entry true;
    sudo dbus-launch gsettings set org.nemo.preferences show-location-entry true;
    gsettings set org.nemo.preferences show-show-thumbnails-toolbar false;
    sudo dbus-launch gsettings set org.nemo.preferences show-show-thumbnails-toolbar false;
    gsettings set org.nemo.preferences thumbnail-limit 15728640;
    sudo dbus-launch gsettings set org.nemo.preferences thumbnail-limit 15728640;
    gsettings set org.nemo.preferences show-compact-view-icon-toolbar false;
    sudo dbus-launch gsettings set org.nemo.preferences show-compact-view-icon-toolbar false;
    gsettings set org.nemo.preferences show-edit-icon-toolbar true;
    sudo dbus-launch gsettings set org.nemo.preferences show-edit-icon-toolbar true;
    gsettings set org.nemo.preferences.menu-config selection-menu-open-as-root false;
    sudo dbus-launch gsettings set org.nemo.preferences.menu-config selection-menu-open-as-root false;
    gsettings set org.nemo.preferences.menu-config background-menu-open-as-root false;
    sudo dbus-launch gsettings set org.nemo.preferences.menu-config background-menu-open-as-root false;

    # Allow customizing Nemo keyboard shortcuts
    gsettings set org.cinnamon.desktop.interface can-change-accels true;
    sudo dbus-launch gsettings set org.cinnamon.desktop.interface can-change-accels true;

    # Remove default Nemo Actions because they only work in Cinnamon.
    sudo rm -rf /usr/share/nemo/actions/90_new-workspace.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/91_delete-workspace.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/92_show-expo.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/add-desklets.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/change-background.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/mount-archive.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/myaction.py;
    sudo rm -rf /usr/share/nemo/actions/new-launcher.nemo_action;
    sudo rm -rf /usr/share/nemo/actions/sample.nemo_action;

    Describe "$textConfiguringMousepad";

    gsettings set org.xfce.mousepad.preferences.view color-scheme 'classic';
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view color-scheme 'classic';
    gsettings set org.xfce.mousepad.preferences.view font-name 'Roboto Mono 10';
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view font-name 'Roboto Mono 10';
    gsettings set org.xfce.mousepad.preferences.view insert-spaces true;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view insert-spaces true;
    gsettings set org.xfce.mousepad.preferences.view show-line-numbers true;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view show-line-numbers true;
    gsettings set org.xfce.mousepad.preferences.view tab-width 4;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view tab-width 4;
    gsettings set org.xfce.mousepad.preferences.view use-default-monospace-font false;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view use-default-monospace-font false;
    gsettings set org.xfce.mousepad.preferences.view highlight-current-line true;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.view highlight-current-line true;
    gsettings set org.xfce.mousepad.preferences.window always-show-tabs true;
    sudo dbus-launch gsettings set org.xfce.mousepad.preferences.window always-show-tabs true;
    gsettings set org.xfce.mousepad.state.search highlight-all true;
    sudo dbus-launch gsettings set org.xfce.mousepad.state.search highlight-all true;


    DoneStage;

    # Stage: Enabling services that will run from startup.
    Describe "$textEnablingServices";

    sudo systemctl enable sddm smb nmb avahi-daemon NetworkManager systemd-homed reflector;

    DoneStage;

    # Stage: Removing installation remnants.
    Describe "$textFinishing";

    rm -rf "$HOME/.cache";
    rm -rf "$HOME/.git";
    rm -rf "$HOME/yay";
    rm -rf "$HOME/ainad";
    rm -rf "$HOME/flat-remix-yellow.tar.xz";
    rm -rf "$HOME/fluent-cursors.tar.gz";
    sudo mv "$HOME/ainad.tar.gz" "/usr/share/ainad/ainad.tar.gz";

    # Stage: Final messages.
    Describe "$textAllDone" 0;
    Describe "$textAllDoneFinalMessage" 2;
};
