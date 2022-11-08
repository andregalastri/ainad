#!/bin/bash

##
# Global definitions.
##

##
# @var string $baseUrl                  The base URL of the repository where the
#                                       contents that is needed to be download.
##
baseUrl="http://ainad.andregalastri.local.com";

source <(curl -s "$baseUrl/ainad.sh");


#---
# Running order.

clear;

CenterTitle;
AinadTitleScreenAnimation;
SectionChooseLanguage;
SectionChooseInstall;
SectionInstall;
