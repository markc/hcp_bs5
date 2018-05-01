#!/usr/bin/env bash
# ~/.sh/build.sh 20170301 - 20180501
# Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)

[[ $1 =~ '-h' ]] && echo "Usage: [bash] buildall.sh [path(pwd)]

Example:

su - sysadm
cd var/www/adm
bash build.sh .
" && exit 1

[[ $1 ]] && cd $1

echo "<?php declare(strict_types = 1);
// netserva.php $(date -u +'%Y-%m-%d %H:%M:%S') UTC
// Copyright (C) 2015-2018 Mark Constable <markc@renta.net> (AGPL-3.0)
// This is single script concatenation of all PHP files in lib/php at
// https://github.com/netserva/hcp
" > netserva.php

(
  find lib/php -name "*.php" -exec cat {} +
  cat index.php
) | sed \
  -e '/^?>/d' \
  -e '/^<?php/d' \
  -e '/^\/\/ Copyright.*/d' \
  -e '/^error_log.*/,+1 d' >> netserva.php

chmod 640 netserva.php
