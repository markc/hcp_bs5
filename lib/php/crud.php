<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// Crud.php 20160205 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Crud
{
    private $g          = null;
    private $t          = null;
    private $o          = null;

    protected $b        = '';
    protected $in       = [];
    protected $update   = 'Update';
    protected $create   = 'Create';
    protected $acl      = 1;

    public function __construct(View $t)
    {
error_log(__METHOD__);

        util::acl($this->acl);
        $this->t  = $t;
        $this->g  = $t->g;
        $this->o  = $t->g->in['o'];
        $this->in = util::esc($this->in);
        db::$tbl  = $this->o;
        $this->b = $this->{$t->g->in['m']}();
//        $this->b .= $this->{$t->g->in['m']}();
    }

    public function __toString() : string
    {
error_log(__METHOD__);

        return $this->b;
    }

    public function create()
    {
error_log(__METHOD__);

        if (count($_POST)) {
            $this->in['updated'] = date('Y-m-d H:i:s');
            $this->in['created'] = date('Y-m-d H:i:s');
            db::create($this->in);
            header("Location: ?o=".$this->o);
            exit();
        } else {
            $this->in['submit'] = $this->create;
            return $this->t->{$this->o.'_form'}($this->in);
        }
    }

    public function read()
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            return $this->t->{$this->o.'_item'}(
                db::read('*', 'id', $this->g->in['i'], '', 'one')
            );
        } else {
            return $this->t->{$this->o.'_list'}([
                'data' => db::read('*', '', '', 'ORDER BY `updated` DESC')
            ]);
        }
    }

    public function update()
    {
error_log(__METHOD__);

        if (count($_POST)) {
            unset($this->in['id'], $this->in['created']);
            $this->in['updated'] = date('Y-m-d H:i:s');
            db::update($this->in, [['id', '=', $this->g->in['i']]]);
            $this->g->in['i'] = 0;
            return $this->read();
        } elseif ($this->g->in['i']) {
            return $this->t->{$this->o.'_form'}(array_merge(
                db::read('*', 'id', $this->g->in['i'], '', 'one'),
                ['submit' => $this->update]
            ));
        } else return 'Error with Update';
    }

    public function delete()
    {
error_log(__METHOD__);

        if ($this->g->in['i']) {
            $res = db::delete([['id', '=', $this->g->in['i']]]);
            $this->g->in['i'] = 0;
            return $this->read();
        } else return 'Error with Delete';
    }
}
