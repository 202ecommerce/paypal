#!/bin/bash

export NVM_DIR="$HOME/.nvm" # set local path to NVM
. ~/.nvm/nvm.sh             # add NVM into the Shell session
nvm install 14.17.3
nvm use 14.17.3  # choose current version
npm install
npm run build
