#!/usr/bin/env bash
# ~/.sh/build.sh 20170301 - 20240904
# Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

[[ $1 =~ '-h' ]] && echo "Usage: [bash] build.sh [path(pwd)]

Example:

su - sysadm
cd var/www/html/hcp
bash build.sh .
" && exit 1

[[ $1 ]] && cd $1

echo "<?php

declare(strict_types=1);

// netserva.php $(date -u +'%Y-%m-%d %H:%M:%S') UTC
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)
// This is single script concatenation of all PHP files in lib/php at
// https://github.com/markc/hcp
" >netserva.php

(
    cat lib/php/db.php
    cat lib/php/init.php
    cat lib/php/plugin.php
    cat lib/php/plugins/accounts.php
    cat lib/php/plugins/auth.php
    cat lib/php/plugins/dkim.php
    cat lib/php/plugins/domains.php
    cat lib/php/plugins/home.php
    cat lib/php/plugins/infomail.php
    cat lib/php/plugins/infosys.php
    cat lib/php/plugins/processes.php
    cat lib/php/plugins/records.php
    cat lib/php/plugins/valias.php
    cat lib/php/plugins/vhosts.php
    cat lib/php/plugins/vmails.php
    cat lib/php/theme.php
    cat lib/php/themes/bootstrap5/theme.php
    cat lib/php/themes/bootstrap5/accounts.php
    cat lib/php/themes/bootstrap5/auth.php
    cat lib/php/themes/bootstrap5/dkim.php
    cat lib/php/themes/bootstrap5/domains.php
    cat lib/php/themes/bootstrap5/home.php
    cat lib/php/themes/bootstrap5/infomail.php
    cat lib/php/themes/bootstrap5/infosys.php
    cat lib/php/themes/bootstrap5/processes.php
    cat lib/php/themes/bootstrap5/records.php
    cat lib/php/themes/bootstrap5/valias.php
    cat lib/php/themes/bootstrap5/vhosts.php
    cat lib/php/themes/bootstrap5/vmails.php
    cat lib/php/util.php
    cat index.php
) | sed \
    -e '/^?>/d' \
    -e '/^<?php/d' \
    -e '/^\/\/ Copyright.*/d' \
    -e '/^declare(strict_types=1);/d' \
    -e '/^error_log.*/,+1 d' >>netserva.php

chmod 640 netserva.php
