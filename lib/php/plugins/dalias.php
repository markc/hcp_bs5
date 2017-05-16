<?php
// lib/php/plugins/mail/alias_domains.php 20170225
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Plugins_Mail_DomainAlias extends Plugin
{
    protected
    $tbl = 'alias_domain',
    $in = [
        'alias_domain'      => '',
        'target_domain'     => '',
        'active'            => 0,
    ];

    protected function create() : string
    {
error_log(__METHOD__);

        if ($_POST) {
            extract($this->in);

            if (!util::is_valid_domain_name($alias_domain)) {
                util::log('Invalid alias domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if (!util::is_valid_domain_name($target_domain)) {
                util::log('Invalid target domain name');
                $_POST = [];
                return $this->t->create($this->in);
            }

            if ($alias_domain === $target_domain) {
                util::log('Alias domain must not be equal to target domain');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `domain` FROM `domain`
  WHERE `domain`= :target_domain";

            $num_results = db::qry($sql, ['target_domain' => $target_domain], 'one');

            if (!$num_results) {
                util::log('Target domain not found');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 SELECT `alias_domain` FROM `alias_domain`
  WHERE `alias_domain`= :alias_domain";

            $num_results = db::qry($sql, [
                'alias_domain' => $alias_domain,
            ], 'one');

            if ($num_results) {
                util::log('Alias domain already exists');
                $_POST = [];
                return $this->t->create($this->in);
            }

            $sql = "
 INSERT INTO `alias_domain` (
        `alias_domain`,
        `target_domain`,
        `active`,
        `updated`,
        `created`
) VALUES (
        :alias_domain,
        :target_domain,
        :active,
        :updated,
        :created
)";
            $res = db::qry($sql, [
                'alias_domain'  => $alias_domain,
                'target_domain' => $target_domain,
                'active'        => $active ? 1 : 0,
                'updated'       => date('Y-m-d H:i:s'),
                'created'       => date('Y-m-d H:i:s'),
            ]);
//            $lid = db::$dbh->lastInsertId();
            util::log('Created domain alias from ' . $alias_domain . ' to ' . $target_domain, 'success');
            return $this->list();
        } else return $this->t->create($this->in);
    }
}

?>
