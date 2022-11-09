#!/bin/bash

# ------
# Global definitions.
# ------

# The base URL of the repository where the contents that is needed to be
# download.
#
# @var string $baseUrl
baseUrl="http://ainad.andregalastri.local.com";

source <(curl -s "$baseUrl/ainad.sh");

clear;

# Running order.
CenterTitle;
AinadTitleScreenAnimation;
SectionChooseLanguage;
SectionChooseInstall;
SectionInstall;
