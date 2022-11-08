#!/bin/bash

case $1 in
    'en_US')
        sudo cp -n "/usr/bin/networkmanager_dmenu" "/usr/bin/networkmanager_dmenu_en_US";
        ;;

    'pt_BR')
        sudo cp -n "/usr/bin/networkmanager_dmenu" "/usr/bin/networkmanager_dmenu_pt_BR";

        sudo sed -i "
        s/\"Networks\"/\"Redes\"/g; 
        s/\"Networking\"/\"Rede\"/g; 
        s/\"Disable\"/\"Desativar\"/g; 
        s/\"Enable\"/\"Ativar\"/g; 
        s/ Networking\"/ Rede\"/g; 
        s/\"Launch Connection Manager\"/\"Abrir Conexões de Rede\"/g; 
        s/\"Delete a Connection\"/\"Remover uma conexão\"/g; 
        s/\"Rescan Wifi Networks\"/\"Buscar Redes Wifi\"/g; 
        s/\"Wifi rescan failed\"/\"Falha ao buscar wifi\"/g; 
        s/\"Wifi scan complete\"/\"Busca por wifi concluída\"/g; 
        s/\"There are multiple connections possible\"/\"Existem múltiplas conexões possíveis\"/g; 
        s/\"Activated {conn.get_id()}\"/\"Ativado {conn.get_id()}\"/g; 
        s/\"Problem activating {data.get_id()}\"/\"Problema ao ativar {data.get_id()}\"/g; 
        s/\"Deactivated {data.get_id()}\"/\"Desativado {data.get_id()}\"/g; 
        s/\"Problem deactivating {data.get_id()}\"/\"Problema ao desativar {data.get_id()}\"/g; 
        s/\"SAVED\"/\"SALVO\"/g; 
        s/\"Multiple active connections match {con.get_id()}\"/\"Múltiplas conexões ativas correspondem a {con.get_id()}\"/g; 
        s/\"Selection was ambiguous: /\"Seleção ambígua: /g; 
        s/\"Lacking permission to write to \/dev\/rfkill.\"/\"Falta permissão de escrita em \/dev\/rfkill.\"/g; 
        s/\"Check README for configuration options.\"/\"Verifica o README para ver as opções de configuração.\"/g; 
        s/\"No network connection editor installed\"/\"Nenhum gerenciador de redes instalado\"/g; 
        s/dmenu_cmd(0, \"Passphrase\"/dmenu_cmd(0, \"Senha\"/g; 
        s/\"CHOOSE CONNECTION TO DELETE:\"/\"ESCOLHA A CONEXÃO PARA REMOVER:\"/g; 
        s/\"Deleted {dev.get_id()}\"/\"Removido {dev.get_id()}\"/g; 
        s/\"Problem deleting {dev.get_id()}\"/\"Problema ao remover {dev.get_id()}\"/g; 
        s/\'infrastructure\'/\'infraestrutura\'/g; 
        s/\"Added {conn.get_id()}\"/\"Adicionado {conn.get_id()}\"/g; 
        s/\"Multiple connection profiles match\"/\"Múltiplos perfis de conexão correspondem\"/g; 
        s/\" the wireless AP\"/\" a rede sem fio AP\"/g; 
        s/\"Saved connections\"/\"Conexões salvas\"/g;" "/usr/bin/networkmanager_dmenu_pt_BR";
        ;;
esac;
