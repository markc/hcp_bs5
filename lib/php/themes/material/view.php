<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// material.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Material_View extends View
{
    public function css() : string
    {
        return '
    <link href="//fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Roboto:400,300,300italic,500,400italic,700,700italic" rel="stylesheet" type="text/css">
    <link href="//storage.googleapis.com/code.getmdl.io/1.0.1/material.blue_grey-pink.min.css" rel="stylesheet">
<style>
* { transition: 0.2s linear; }
.demo-main {
  margin-top: -35vh;
  -webkit-flex-shrink: 0;
    -ms-flex-negative: 0;
          flex-shrink: 0;
}
.demo-container {
  max-width: 1600px;
  width: calc(100% - 16px);
  margin: 0 auto;
}
.demo-ribbon {
  width: 100%;
  height: 40vh;
  background: url("/simple-php7-examples/lib/img/20130317_Albert_Park_800x144.jpg") fixed top no-repeat;
  background-size: 100%;
  -webkit-flex-shrink: 0;
    -ms-flex-negative: 0;
          flex-shrink: 0;
}
.demo-content {
  border-radius: 0.2em;
  padding: 0 2em 1em;
  margin-bottom: 4em;
}
.demo-alert {
  border-radius: 0.1em;
  color: #FFF;
  margin-top: 1em;
  padding: 1em;
}
.mdl-navigation .mdl-navigation__link {
  border-bottom: 4px solid transparent;
  border-top: 4px solid transparent;
  line-height: 56px;
  font-size: 14px;
  text-transform: uppercase;
}
a.mdl-navigation__link.is-active,
a.mdl-navigation__link.is-active:hover {
  color: rgb(0,0,0);
  border-bottom: 4px solid transparent;
  border-top: 4px solid transparent;
}
a.mdl-navigation__link:hover {
  color: #DFDFDF;
  border-bottom: 4px solid rgb(255,64,129);
  border-top: 4px solid transparent;
}
table {
  border-top: 1px solid #EFEFEF;
  width: 100%;
}



.drop {
  position: relative;
}
.drop ul {
  position: absolute;
  left: 0;
  top: 3em;
  -webkit-transition: all 0.3s ease;
  transition: all 0.3s ease;
  -webkit-transform: scale(0);
          transform: scale(0);
  -webkit-transform-origin: 0 0;
          transform-origin: 0 0;
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.16), 0 2px 8px 0 rgba(0, 0, 0, 0.12);
}
.drop ul li {
  display: block;
  width: 100%;
}
.drop ul li a {
  width: 100%;
  padding: 1em 18px;
  display: inline-block;
  white-space: pre;
  box-sizing: border-box;
}
.drop ul li a:hover {
  background: #ebebeb;
}
.drop:hover ul {
  -webkit-transform: scale(1);
          transform: scale(1);
}

</style>';
    }

    public function top() : string
    {
        return '
    <div class="mdl-layout mdl-layout--fixed-header mdl-js-layout mdl-color--grey-100">';
    }

    public function msg() : string
    {
        list($l, $m) = $this->g->in['m']
            ? explode(':', $this->g->in['m']) : util::msg();
        return $m ? '
            <p class="demo-alert' . $this->mdl_color($l) . '">' . $m . '
            </p>' : '';
    }

    public function head() : string
    {
       return '
      <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
          <span class="mdl-layout-title">'.$this->g->out['head'].'</span>
          <div class="mdl-layout-spacer"></div>
          <nav class="mdl-navigation">'.$this->g->out['nav1'].'
          </nav>
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable">
            <label class="mdl-button mdl-js-button mdl-button--icon" for="search">
              <i class="material-icons">search</i>
            </label>
            <div class="mdl-textfield__expandable-holder">
              <input class="mdl-textfield__input" type="text" id="search" />
              <label class="mdl-textfield__label" for="search">Enter your query...</label>
            </div>
          </div>
          <button class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" id="more-button">
            <i class="material-icons">more_vert</i>
          </button>
          <ul class="mdl-menu mdl-js-menu mdl-menu--bottom-right mdl-js-ripple-effect" for="more-button">'.$this->g->out['nav2'].'
          </ul>
        </div>
      </header>
      <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">'.$this->g->out['head'].'</span>
        <nav class="mdl-navigation">'.$this->g->out['nav1'].'
        </nav>
      </div>';
    }

    public function nav1() : string
    {
        $o = '?o='.$this->g->in['o'];
        return join('', array_map(function ($n) use ($o) {
            if (is_array($n[1])) {
                error_log(var_export($n[1],true));
                return '
        <ul>
          <li class="drop">'.$n[0].'
            <ul>'.join('', array_map(function ($x) use ($o) {
            $c = $o === $x[1] ? ' class="active"' : '';
            return '
              <li><a'.$c.' href="'.$x[1].'">'.$x[0].'</a></li>';
        }, $n[1])).'
            </ul>
          </li>
        </ul>';
            } else {
            return '
            <a class="mdl-navigation__link'.($o===$n[1]?' is-active':'').'" href="'.$n[1].'">'.$n[0].'</a>';
            }
        }, util::which_usr($this->g->nav1)));
    }

    public function nav2() : string
    {
        $t = '?t='.$this->g->in['t'];
        return join('', array_map(function ($n) use ($t) {
            return '
            <li class="mdl-menu__item'.($t===$n[1]?' is-active':'').'"><a href="'.$n[1].'">'.$n[0].'</a></li>';
        }, $this->g->nav2));
    }

    public function main() : string
    {
        return '
      <div class="demo-ribbon"></div>
      <main class="demo-main mdl-layout__content">
        <div class="demo-container mdl-grid">
          <div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
          <div class="demo-content mdl-color--white mdl-shadow--4dp content mdl-color-text--grey-800 mdl-cell mdl-cell--8-col">'.$this->g->out['log'].$this->g->out['main'].'
          </div>
        </div>
      </main>';
    }

    public function foot() : string
    {
        return '
    <footer class="mdl-mini-footer">
      <p><em><small>Copyright (C) 2015 Mark Constable (AGPL-3.0)</small></em></p>
    </footer>';
    }

    public function end() : string
    {
        return '
    </div>
    <script src="//storage.googleapis.com/code.getmdl.io/1.0.1/material.min.js"></script>';
    }

    public function veto_a($href, $label, $class, $extra)
    {
        $class = $this->mdl_color($class);
        return ['class' => 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent'.$class];
    }

    public function veto_button($label, $type, $class, $name, $value, $extra)
    {
        $class = $this->mdl_color($class);
        return ['class' => 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent'.$class];
    }

    public function veto_email_contact_form() : string
    {
        return '
      <form action="#" method="post" onsubmit="return mailform(this);">
        <div class="mdl-cell--12-col mdl-grid">
          <div class="mdl-cell mdl-cell--3-col mdl-cell--1-col-tablet">&nbsp;</div>
          <div class="mdl-grid mdl-cell--6-col">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell--12-col">
              <input class="mdl-textfield__input" type="text" id="subject" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,40}$">
              <label class="mdl-textfield__label" for="subject">Your Subject</label>
              <span class="mdl-textfield__error">Requires from 2 to 40 chars</span>
            </div>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell--12-col">
              <textarea class="mdl-textfield__input" type="text" rows= "5" id="message" maxlength="10" minlength="5"></textarea>
              <label class="mdl-textfield__label" for="message">Your Message</label>
              <span class="mdl-textfield__error">Requires from 2 to 1024 chars</span>
            </div>
            <button class="mdl-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Send</button>
          </div>
          <div class="mdl-cell mdl-cell--3-col mdl-cell--1-col-tablet">&nbsp;</div>
        </div>
      </form>';
    }

    public function veto_notes_item($ary) : string
    {
        extract($ary);
        return '
      <table class="table">
        <tr>
          <td><a href="?p=notes&a=read&i=' . $id . '">' . $title . '</a></td>
          <td style="text-align:right">
            <small>
              by <b>' . $author . '</b> - <i>' . util::now($updated) . '</i> -
              <a href="?p=notes&a=update&i=' . $id . '" title="Update">E</a>
              <a href="?p=notes&a=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>
        <tr>
          <td colspan="2">' . nl2br($content) . '</td>
        </tr>
      </table>
      <br>';
    }

    public function veto_notes_form($ary) : string
    {
        extract($ary);
        return '
      <form action="#" method="post"">
        <div class="mdl-cell--12-col mdl-grid">
          <div class="mdl-cell mdl-cell--3-col mdl-cell--1-col-tablet">&nbsp;</div>
          <div class="mdl-grid mdl-cell--6-col">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell--12-col">
              <input class="mdl-textfield__input" type="text" id="title" name="title" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,40}$">
              <label class="mdl-textfield__label" for="title">Title</label>
              <span class="mdl-textfield__error">Requires from 2 to 40 chars</span>
            </div>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell--12-col">
              <input class="mdl-textfield__input" type="text" id="author" name="author" pattern="^[a-zA-Z][a-zA-Z0-9-_\.]{1,40}$">
              <label class="mdl-textfield__label" for="author">Author</label>
              <span class="mdl-textfield__error">Requires from 2 to 40 chars</span>
            </div>
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-cell--12-col">
              <textarea class="mdl-textfield__input" type="text" rows= "7" id="content" name="content" maxlength="10" minlength="5"></textarea>
              <label class="mdl-textfield__label" for="content">Your Message</label>
              <span class="mdl-textfield__error">Requires from 2 to 1024 chars</span>
            </div>
            <button class="mdl-button mdl-button--raised mdl-button--accent mdl-js-ripple-effect">Submit</button>
          </div>
          <div class="mdl-cell mdl-cell--3-col mdl-cell--1-col-tablet">&nbsp;</div>
        </div>
        <input type="hidden" name="p" value="' . $this->g->in['p'] . '">
        <input type="hidden" name="a" value="' . $this->g->in['a'] . '">
        <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
      </form>';
    }

    private function mdl_color($class)
    {
        switch($class) {
            case 'success'  : return ' mdl-color--green';
            case 'danger'   : return ' mdl-color--red';
            case 'primary'  : return ' mdl-color--blue';
            default         : return '';
        }
    }
}
