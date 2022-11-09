#!/bin/bash

# ------
# Global definitions.
# ------

# The base URL of the repository where the contents that is needed to be
# download.
#
# @var string $baseUrl
baseUrl="https://raw.githubusercontent.com/andregalastri/ainad/testing";

source <(curl -s "$baseUrl/ainad.sh");

clear;

# Running order.
CenterTitle;
AinadTitleScreenAnimation;
SectionChooseLanguage;
SectionChooseInstall;
SectionInstall;
