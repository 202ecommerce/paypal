#!/bin/bash

export NVM_DIR="$HOME/.nvm" # set local path to NVM
. ~/.nvm/nvm.sh             # add NVM into the Shell session
nvm install 14.17.3         # install version (done only one time)
nvm use 14.17.3             # use choosed version
npm install
npm rebuild node-sass       # could be needed once if version changed
npm run build
rm views/js/*.map
rm views/js/diagnostic/*.map
rm views/css/*.map
rm views/css/diagnostic/*.map
