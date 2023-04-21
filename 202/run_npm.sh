#!/bin/bash

export NVM_DIR="$HOME/.nvm" # set local path to NVM
. ~/.nvm/nvm.sh             # add NVM into the Shell session
nvm use lts/*  # choose current version
npm install
npm run build
