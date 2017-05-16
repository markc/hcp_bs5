#!/bin/bash
# build.sh 20170301
# Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

[ -n $1 ] && cd $1

echo '<?php declare(strict_types = 1);

// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)
' > all.php

(
  find lib/php -name "*.php" -exec cat {} +
  cat index.php
) | sed \
  -e '/^?>/d' \
  -e '/^<?php/d' \
  -e '/^\/\/ Copyright.*/d' \
  -e '/^error_log.*/,+1 d' >> all.php

#  -e 's/^?>//g' \
#  -e 's/^<?php//g' \
#  -e 's/^\/\/ Copyright.*//g' \

chmod 640 all.php
