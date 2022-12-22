#!/bin/bash

# ------
# This script runs the update of the selected packages. It needs to be run in a
# Terminal because of Sudo commands.
# ------

# Stores all the selected packages. This comes as arguments when the script is
# called.
#
# @var array
selectedPackages=($@);

echo -e "Para prosseguir na atualização, por favor, informe sua senha";

# Updates the repositories and execute the update. It uses Yay instead of Pacman
# because AUR packages that also have updates.
yay -Syy;
echo "y" | LANG=C yay --noprovides --answerdiff None --answerclean All --mflags --noconfirm -S ${selectedPackages[*]};

# Detects if everything worked fine or not.
if [[ "$?" = "0" ]]; then
    dunstify -i "package-installed-updated" -t 10000 "Concluído!" "Os pacotes foram atualizados com sucesso!";
    ainad-utilities 'Updater' 'writeUpToDateData';
else
    dunstify -i "dialog-error" -t 10000 "Atenção! Erro #$?" "Houve uma falha durante a atualização. Os pacotes podem não ter sido atualizados.";
    echo -e "Houve uma falha durante a atualização. Os pacotes podem não ter sido atualizados. Veja acima o que houve.";
    echo -e "Pressione qualquer tecla para fechar esta janela.";
    read
fi;
