# SOBRE O AINAD

AINAD é uma sigla para **AINAD Is Not A Distro** (AINAD não é uma distro). Trata-se de um simples script que instala uma série de pacotes e programas que criam um ambiente gráfico mínimo pronto para ser usado no Arch Linux.

---

**IMPORTANTE: Este projeto está em suas fases iniciais de desenvolvimento e não está pronto para uso em produção.**

---

![AINAD Screenshot](images/screenshot1.png "AINAD Screenshot")

<br />

# INSTALAÇÃO

1. Instale o **Arch Linux** na modalidade **Minimal Install** através do comando `archinstall` disponibilizado pelo próprio Arch Linux;
1. Após a instalação, reinicie o computador e logue com seu usuário e senha;
1. Execute o comando abaixo:
    ```
    (source <(curl -L https://andregalastri.com.br/ainadinstall))
    ```
    > **ATENÇÃO:<br /> É necessário conexão com a internet.**
1. Escolha o seu idioma;
1. Escolha a opção 1, informe sua senha SUDO/ROOT e aguarde o fim da instalação.

<br />

# POR QUÊ O AINAD NÃO É UMA DISTRO

Porque ele é simplesmente um script que automatiza a instalação e configuração de um ambiente gráfico mínimo para Arch Linux, e **é só isto**. Ele foi criado para uso pessoal, mas foi aberto para que qualquer um que queira testá-lo.

Ao final da instalação, o usuário estará apenas usando Arch Linux e não uma distro aleatória mantida por um aletório.

* O AINAD não é, e nem pretende ser, uma distro Linux;
* Ele é apenas um instalador de pacotes automatizado que cria um ambiente gráfico mínimo e usável para o usuário. Ele não faz nada além do que o usuário poderia fazer rodando comandos manualmente;
* Ele não garante que tudo irá funcionar após a instalação. Seu hardware é diferente do meu. Reporte de bugs são bem vindos. Ele é testado para usos básicos, com hardware comum;
* Ele também não garante suporte ao usuário.

<br />

# QUAIS PACOTES SÃO INSTALADOS

O AINAD se utiliza do gerenciador de pacotes oficial do Arch Linux, o **Pacman**, e do gerenciador de pacotes da comunidade, o **Yay**, para instalação dos programas. O AINAD utiliza os repositórios oficiais do Arch Linux e o repositório da comunidade do Arch Linux, o **AUR**.

<br />

* **REFLECTOR**

    *Pacotes* : `reflector`

    Utilitário que mantém a lista de mirrors atualizada.
  
<br />

* **XORG**

    *Pacotes* : `xorg-server xorg-xev xorg-xinput`

    O bom e velho servidor **Xorg** é o backend responsável por desenhar o ambiente gráfico na tela do usuário.
  
    **- Por que não o Waylad?**
 
    O Wayland ainda possui incompatibilidades com drivers proprietários de placas de vídeo NVidia. O AINAD não pretende ser o mais atualizado possível, seu objetivo é ser estável, funcional, e o Xorg, neste caso, é a melhor escolha por enquanto. Além disso, o AINAD instala o **Openbox** como gerenciador de janelas, que não possui versão para Wayland.

<br />

* **SDDM**

    *Pacotes* : `sddm noto-fonts qt5-graphicaleffects qt5-quickcontrols2`

    O **SDDM** é um login manager bonito e leve. Ele permite o usuário fazer logon no computador através de um visual agradável e de fácil utilização.

    Junto com o SDDM são instaladas as fontes Noto e alguns pacotes QT5 necessários para o funcionamento do tema [Sugar Candy de Marian Arlt](https://framagit.org/MarianArlt/sddm-sugar-candy).

<br />

* **KERNEL HEADERS**

    *Pacotes* : `linux-headers`
  
    Os cabeçalhos Linux agem como uma interface entre os componentes internos do Kernel e o espaço de usuário. Algumas bibliotecas como *sys-libs* e *glibc* dependem dos Kernel headers. Muitos programas também usam este pacote, mas alguns acabam não o colocando como dependência. Portanto, é bom tê-lo instalado por padrão. Você pode ler mais sobre isso em [https://wiki.gentoo.org/wiki/Linux-headers](https://wiki.gentoo.org/wiki/Linux-headers).

<br />

* **OPENBOX**

    *Pacotes* : `openbox`

    O **Openbox** é um gerenciador de janelas extremamente estável e muito leve. Basicamente faz a comunicação com o Xorg para desenhar as janelas e seus conteúdos na tela.

<br />

* **XDOTOOL**

    *Pacotes* : `xdotool`
  
    Utilitário que permite a automação de teclas de atalho e do mouse através de comandos de terminal.

<br />


* **THERMALD**

    *Pacotes* : `thermald`

    Serviço para controlar a temperatura da CPU.

<br />

* **MAN - MANUAL INTERFACE**

    *Pacotes* : `man-db`

    Uma interface para terminal que permite o usuário ler manuais de referência de diversas aplicações. É bastante comum em diversas instalações, por isso foi colocada para instalação.
    
<br />

* **PHP**

    *Pacotes* : `php php-intl php-sqlite`

    Eu sei, eu sei, você não gosta de PHP, mas eu gosto, e eu o utilizo em todos os utilitários do AINAD.

    **- Por que não usar a _linguagem X Y Z_? Ou apenas simples Bash scripts?**
 
    Porque eu provavelmente não sei a _linguagem X Y Z_, ou possuo pouco conhecimento sobre ela. Eu sei muito sobre PHP, portanto, é simples assim.
    
    Eu também uso diversos scripts Bash, mas pra algumas coisas ele pode ser um saco de lidar, por tanto, sim, uso primariamente PHP...
    
<br />

* **WMCTRL**

    *Pacotes* : `wmctrl`
    
    Permite controlar janelas, bem como capturar dados relacionados, através de comandos de terminal.

<br />

* **XFCE4 TERMINAL**

    *Pacotes* : `xfce4-terminal`
    
    Um bom emulador gráfico de terminal. É fácil de configurar, leve e possui uma boas opções de personalização.

<br />

* **NEMO**

    *Pacotes* : `nemo cinnamon-translations`

    **Nemo** foi escolhido por ser um dos únicos que possui um buscador próprio de arquivos e diretórios.

    Necessita do pacote de traduções do Cinnamon para dar suporte a outros idiomas.

<br />

* **FORMATOS DE SISTEMA DE ARQUIVOS E OUTRAS INTEGRAÇÕES COM GERENCIADORES DE ARQUIVO**

    *Pacotes* : `gvfs gvfs-nfs gvfs-mtp gvfs-gphoto2 gvfs-google gvfs-goa gvfs-afc ntfs-3g`

    Adiciona recursos ao gestor de arquivos, como lixeira, montagem de drives e navegação de arquivos da rede, além de oferecerem suporte para sistemas de arquivos diversos.

<br />

* **SAMBA**

    *Pacotes* : `samba gvfs-smb cifs-utils`

    Permite o compartilhamento de arquivos e diretórios na rede, além de permitir o acesso aos compartilhamentos de computadores com Windows.

<br />

* **ENGRAMPA**

    *Pacotes* : `engrampa`

    Gerenciador de arquivos comprimidos, como *tar*, *tar.gz*, etc.

<br />

* **MATE POLKIT**

    *Pacotes* : `mate-polkit`

    Um polkit simples que permite que alguma aplicação execute comandos de ROOT quando necessário.

<br />

* **MOUSEPAD**

    *Pacotes* : `mousepad`

    Um editor visual de textos básico.

<br />

* **NANO**

    *Pacotes* : `nano`

    Um editor de textos básico para terminal.

<br />

* **GALCULATOR**

    *Pacotes* : `galculator`

    Uma simples calculadora.

<br />

* **DRIVERS NVIDIA**

    *Pacotes* : `nvidia nvidia-utils`

    Instala os drivers e utilitários proprietários da Nvidia.

    **- Porque não o driver open source?**
 
    Em meus testes, o driver proprietário da Nvidia funciona melhor.

    Caso o usuário prefira o driver open source, basta desinstalar estes pacotes e instalar o pacote `xf86-video-nouveau`.
    
<br />

* **DRIVERS VMWARE**

    *Pacotes* : `virtualbox-guest-iso virtualbox-guest-utils xf86-video-vmware`

    Instala drivers e utilitários de máquina virtual.

<br />

* **DRIVERS INTEL**

    *Pacotes* : `vulkan-intel xf86-video-intel`

    Instala os drivers Intel.

<br />

* **DRIVERS AMD/ATI**

    *Pacotes* : `vulkan-radeon xf86-video-amdgpu xf86-video-ati`

    Instala os drivers AMD e ATI. Estes pacotes são instalados mesmo que o usuário não possua placas da AMD, pois o tamanho delas é pequeno.

<br />

* **NITROGEN**

    *Pacotes* : `nitrogen`

    O AINAD configura utilitários que usam o Nitrogen para definir papeis de parede.

<br />

* **GIT**

    *Pacotes* : `git`

    Necessário para instalar e usar o Yay.

<br />

* **PACMAN SCRIPTS**

    *Pacotes* : `pacman-contrib`

    Um pacote de scripts adicionais contendo comandos úteis. É necessário pois o AINAD instala um utilitário que usa o comando *checkupdates* para verificar atualizações disponíveis.

<br />

* **GNOME KEYRING**

    *Pacotes* : `gnome-keyring`

    Programas como o **VS Code** necessitam deste pacote para usar algumas funções.

<br />

* **GTK2FONTSEL**

    *Pacotes* : `gtk2fontsel`

    Um simples programa que lista as fontes instaladas.

<br />

* **DUNST**

    *Pacotes* : `dunst`

    Um daemon para customizar as mensagens de notificação.

<br />

* **POLYBAR**

    *Pacotes* : `polybar dbus-python playerctl`

    Trata-se de um programa que permite a criação de barras no desktop baseadas em texto. É bastante leve e customizável. O AINAD usa esta barra por padrão.

    Os pacotes `dbus-python` e `playerctl` são dependências de um dos módulos usados na Polybar, o `polybar-now-playing`.
<br />

* **ROFI**

    *Pacotes* : `rofi dmenu`

    O **Rofi** permite a criação de launchers e applets. O AINAD configura diversos launchers por padrão, como Menus de Aplicativos, Atualizador, Calendário, etc. O pacote `dmenu` é usado pelo Rofi para alguns tipos de comandos. 

<br />

* **FLAMESHOT**

    *Pacotes* : `flameshot`

    Um dos melhores programas de screenshot. Permite tirar screenshots de parte da tela, permite desenhar setas e outras figuras antes de tirar a screenshot.

<br />

* **VIEWNIOR**

    *Pacotes* : `viewnior`

    Um simples visualizador de imagens. Foi escolhido para fazer parte da instalação do AINAD por possuir integração com o Nitrogen, o que permite que o usuário possa aplicar a imagem visualizadas como papel de parede.

<br />

* **XREADER**

    *Pacotes* : `xreader`

    Um bom leitor de documentos PDF. Foi escolhido por conter a opção de criar anotações e grifos.

<br />

* **ARANDR**

    *Pacotes* : `arandr`
    
    Um simples programa para gerenciamento de telas e monitores. Seu único problema é que ele não possui uma opção para alterar a taxa de atualização (refresh rate) do monitor. É por este motivo que o AINAD também instala o **Lxrandr**.

<br />

* **LXRANDR**

    *Pacotes* : `lxrandr`

    O único motivo de instalá-lo é porque o **Xrandr** não possui a opção de alterar a taxa de atualização (refresh rate) dos monitores.

<br />

* **LXTASK**

    *Pacotes* : `lxtask`

    Um simples programa de monitoramento de processos.

<br />

* **LXINPUT-GTK3**

    *Pacotes* : `lxinput-gtk3`

    Um simples programa de gerenciamento de mouse e teclado.

<br />

* **PAVUCONTROL**

    *Pacotes* : `pavucontrol`

    Um avançado programa de gerenciamento de audio. Funciona tanto com pulseaudio quanto com pipewire.

<br />

* **XFCE4 POWER MANAGER**

    *Packages* : `xfce4-power-manager`

    Um simples programa de gerenciamento de energia.

<br />

* **LXAPPEARANCE**

    *Pacotes* : `lxappearance lxappearance-obconf`

    De momento, este é o programa para gerenciar os temas, os temas de ícones e de cursores, mas em breve será criado um utilitário que irá substituir este programa.

<br />

* **KVANTUM**

    *Pacotes* : `kvantum`

    Gerencia os temas para aplicações QT.

<br />

* **QT SETTINGS**

    *Pacotes* : `qt5ct`

    Aplica os temas para aplicações QT.

<br />

* **NETWORK SETTINGS**

    *Pacotes* : `connman wpa_supplicant bluez openvpn`

    ConnMan é um gerenciador de conexões de rede via terminal. Ele possui algumas interfaces gráficas, mas ainda estou testando.

    O pacote `wpa_supplicant` permite conexão via Wifi. O pacote `bluez` permite conexão por bluetooth. O pacote `openvpn` permite conexão via VPN.

<br />

* **PICOM**

    *Pacotes* : `picom`

    Trata-se de um compositor de janelas, ou seja, aplica sombreamentos, transparência, desfoque, arredondamento de bordas de janelas e paineis. É um dos vários forks do Compton, mas o mais estável que foi testado.

<br />

* **FONTES DO REPOSITÓRIO OFICIAL**

    *Pacotes* : `noto-fonts-cjk noto-fonts-emoji`

    Instala algumas fontes padrão.

<br />

* **YAY**

    *Pacotes* : `yay`

    O **Yay** é um helper que permite a instalação de programas que estão no repositório AUR, o repositório dos usuários do Arch Linux. Seu funcionamento é igual ao do Pacman, mas os programas disponíveis são mantidos por usuários do Arch.

    **- IMPORTANTE**

    O Yay não está disponível no repositório oficial do Arch Linux. Por isso, seu pacote é instalado por meio de compilação do código fonte do programa.

<br />

* **RAR**

    *Pacotes (AUR)* : `rar`

    Adiciona suporte a arquivos **.rar** nos gerenciadores de arquivos compactados.

<br />

* **GOOGLE CHROME**

    *Pacotes (AUR)* : `google-chrome`

    Navegador de internet. Foi escolhido por ser o mais popular. O AINAD não tem a intenção de escolher apenas programas de código aberto.

<br />

* **WARSAW**

    *Pacotes (AUR)* : `warsaw-bin`

    Utilitário muito usado em sites de internet banking. É instalado por padrão pois a maioria dos sites de internet banking não explica como instalá-lo em sistemas Arch de maneira apropriada.

<br />

* **PARCELLITE CLIPBOARD MANAGER**

    *Pacotes (AUR)* : `parcellite`

    Um gerenciador de áreas de transferência. Sem ele, a área de transferência não é persistente.

<br />

* **FONTES DO AUR**

    *Pacotes (AUR)* : `ttf-roboto-mono ttf-roboto ttf-century-gothic`

    Instala mais algumas fontes.
