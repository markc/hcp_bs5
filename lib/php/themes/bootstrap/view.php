<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// bootstrap.php 20151015 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_View extends View
{
    public function css() : string
    {
error_log(__METHOD__);

        return '
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Roboto:500,400,300,100,100italic" rel="stylesheet" type="text/css">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <style>
html {
  background: url(https://unsplash.it/1366/720?image=990) no-repeat center center fixed;
  background-size: cover;
  font-size: 16px;
}
body {
  background-color: transparent;
  font-family: "Roboto", sans-serif; font-weight: 300;
  font-size: 1.1rem;
}
.content {
  background-color: #FFFFFF;
  margin-bottom: 4em;
  margin-top: 50px;
  opacity: 0.9;
  padding: 0.5em 1em;
}
.content > h2 {
  margin: 0.25em 0;
}
footer {
  background-color: #3F3E3D;
  bottom: 0px;
  color: #9E9E9E;
  height: 50px;
  padding: 0.5em;
  position: fixed;
  text-align: center;
  width: 100%;
  z-index: 99;
}
td {
  white-space: nowrap;
}
.navbar {
  background-color: #3F3E3D;
  border: none;
}
@media(min-width:767px){
  .alert { margin-top: 1em; }
  .content {
    border-radius: 0.25em;
    box-shadow: 0px 4px 5px 0px rgba(0, 0, 0, 0.14), 0px 1px 10px 0px rgba(0, 0, 0, 0.12), 0px 2px 4px -1px rgba(0, 0, 0, 0.2);
    margin-top: 70px;
    padding: 1em 2em;
  }
}
</style>';
    }

    public function log() : string
    {
error_log(__METHOD__);

        list($l, $m) = util::log();
        return $m ? '
      <div class="alert alert-'.$l.'">'.$m.'
      </div>' : '';
    }

    public function head() : string
    {
error_log(__METHOD__);

        return '
    <header class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="?o=home"><strong>'.$this->g->out['head'].'</strong></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">'.$this->g->out['nav1'].'
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Themes <span class="caret"></span></a>
              <ul class="dropdown-menu">'.$this->g->out['nav2'].'
              </ul>
            </li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </header>';
    }

    public function nav1(array $a = []) : string
    {
error_log(__METHOD__);

        $a = isset($a[0]) ? $a : util::nav($this->g->nav1);
//        $p = '?p='.$_SESSION['p'];
//        $t = '?t='.$_SESSION['t'];
        $o = '?o='.$this->g->in['o'];
        $t = '?t='.$this->g->in['t'];
        return join('', array_map(function ($n) use ($o, $t) {
            $c = $o === $n[1] || $t === $n[1] ? ' class="active"' : '';
            return '
            <li'.$c.'><a href="'.$n[1].'">'.$n[0].'</a></li>';
        }, $a));
    }

    public function nav2() : string
    {
error_log(__METHOD__);

        return $this->nav1($this->g->nav2);
    }

    public function main() : string
    {
error_log(__METHOD__);

        return '
    <main class="container">
      <div class="row">
        <div class="col-md-1 col-lg-2"></div>
        <div class="content col-md-10 col-lg-8">'.$this->g->out['log'].$this->g->out['main'].'
        </div>
        <div class="col-md-1 col-lg-2"></div>
      </div>
    </main>';
    }

    public function end() : string
    {
error_log(__METHOD__);

        return '
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>';
    }

    public function veto_a($href, $label, $class, $extra)
    {
error_log(__METHOD__);

        $class = $class ? ' btn-'.$class : '';
        return ['class' => 'btn btn-primary'.$class];
    }

    public function veto_button($label, $type, $class, $name, $value, $extra)
    {
error_log(__METHOD__);

        $class = $class ? ' btn-'.$class : '';
        return ['class' => 'btn btn-primary'.$class];
    }
/*
    public function veto_email_contact_form()
    {
error_log(__METHOD__);

        return '
      <form class="form-horizontal" role="form" method="post" onsubmit="return mailform(this);">
        <div class="form-group">
          <label for="subject" class="col-sm-2 col-md-3 col-lg-4 control-label">Subject</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="subject" placeholder="Your Subject" required>
          </div>
        </div>
        <div class="form-group">
          <label for="message" class="col-sm-2 col-md-3 col-lg-4 control-label">Message</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <textarea class="form-control" id="message" rows="9" placeholder="Your Message" required></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-2 col-sm-offset-2 col-md-offset-3 col-lg-offset-4">
            <input class="btn btn-primary" id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
          </div>
        </div>
      </form>';
    }
*/
    // Notes

    public function veto_notes_item($ary) : string
    {
error_log(__METHOD__);

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
      </table>';
    }

    public function veto_notes_form($ary) : string
    {
error_log(__METHOD__);

        extract($ary);
        return '
      <form class="form-horizontal" role="form" method="post"">
        <div class="form-group">
          <label for="title" class="col-sm-2 col-md-3 col-lg-4 control-label">Title</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="title" name="title" placeholder="Your Title" value="' . $title . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="author" class="col-sm-2 col-md-3 col-lg-4 control-label">Author</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="author" name="author" placeholder="Authors name" value="' . $author . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="content" class="col-sm-2 col-md-3 col-lg-4 control-label">Content</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <textarea class="form-control" id="content" name="content" rows="9" placeholder="Your Message" required>' . $content . '</textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-2 col-sm-offset-2 col-md-offset-3 col-lg-offset-4">'.$this->button('Submit', 'submit', 'primary').'
          </div>
        </div>
        <input type="hidden" name="p" value="' . $this->g->in['p'] . '">
        <input type="hidden" name="a" value="' . $this->g->in['a'] . '">
        <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
      </form>';
    }

    // Users

    public function veto_users_list(string $str) : string
    {
error_log(__METHOD__);

        return '
      <div class="responsive">
        <table class="table">' . $str . '
        </table>
      </div>';
    }

    public function veto_users_item(array $ary) : string
    {
error_log(__METHOD__);

        extract($ary);
        return '
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>ID</label></div>
        <div class="col-md-3 col-xs-8">' . $id . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>UID</label></div>
        <div class="col-md-3 col-xs-8">' . $uid . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>FirstName</label></div>
        <div class="col-md-3 col-xs-8">' . $fname . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>LastName</label></div>
        <div class="col-md-3 col-xs-8">' . $lname . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>Email</label></div>
        <div class="col-md-3 col-xs-8">' . $email . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>Updated</label></div>
        <div class="col-md-3 col-xs-8">' . $updated . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>Created</label></div>
        <div class="col-md-3 col-xs-8">' . $created . '</div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-1 col-xs-4"><label>Note</label></div>
        <div class="col-md-3 col-xs-8"><em>' . nl2br($anote) . '</em></div>
        <div class="col-md-4"></div>
      </div>
      <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4 col-xs-12 text-right">
          <br>
          '.$this->a('?p=users&a=update&i='.$id, 'Edit', 'btn btn-primary').'
          '.$this->a('?p=users&a=delete&i='.$id, 'Delete', 'btn btn-danger', ' onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')"').'
        </div>
        <div class="col-md-4"></div>
      </div>';
    }

    public function veto_users_form(array $ary) : string
    {
error_log(__METHOD__);

        extract($ary);
        return '
      <form class="form-horizontal" role="form" method="post"">
        <div class="form-group">
          <label for="uid" class="col-sm-2 col-md-3 col-lg-4 control-label">UID</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="uid" name="uid" placeholder="Username" value="' . $uid . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="fname" class="col-sm-2 col-md-3 col-lg-4 control-label">FirstName</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="fname" name="fname" placeholder="FirstName" value="' . $fname . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="lname" class="col-sm-2 col-md-3 col-lg-4 control-label">LastName</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="text" class="form-control" id="lname" name="lname" placeholder="LastName" value="' . $lname . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="email" class="col-sm-2 col-md-3 col-lg-4 control-label">Email</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="' . $email . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="anote" class="col-sm-2 col-md-3 col-lg-4 control-label">Note</label>
          <div class="col-sm-9 col-md-7 col-lg-5">
            <textarea class="form-control" id="anote" name="anote" rows="3" placeholder="Admin Note">' . $anote . '</textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-2 col-sm-offset-2 col-md-offset-3 col-lg-offset-4">'
          .$this->button('Save', 'submit', 'primary').'
          </div>
        </div>
        <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
      </form>';
//        <input type="hidden" name="p" value="' . $this->g->in['p'] . '">
//        <input type="hidden" name="a" value="' . $this->g->in['a'] . '">
    }

    // Auth
/*
//    public function auth_signin(string $uid = '') : string
    public function auth_signin(array $ary) : string
    {
error_log(__METHOD__);
        extract($ary);
        return '
        <h2><i class="fa fa-sign-in fa-fw"></i> Sign in</h2>
        <div class="col-md-6 col-md-offset-3">
          <form class="form" role="form" action="" method="post">
            <input type="hidden" name="p" value="auth">
            <div class="row">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-user fa-fw"></span></span>
                  <input type="text" name="uid" id="uid" class="form-control" placeholder="Your Email Address" value="'.$uid.'" required autofocus>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                  <input type="password" name="webpw" id="webpw" class="form-control" placeholder="Your Password">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="remember" id="remember" value="yes"> Remember me on this computer
                  </label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="text-right">
                  <a class="btn btn-md btn-default" href="?p=auth&amp;a=forgotpw">Forgot password</a>
                  <button class="btn btn-md btn-primary" type="submit" name="a" value="signin">Sign in</button>
                </div>
              </div>
            </div>
          </form>
        </div>';
    }
*/
    public function auth_forgotpw(string $uid = '') : string
    {
error_log(__METHOD__);

        return '
        <h2><i class="fa fa-key fa-fw"></i> Reset password</h2>
        <div class="col-md-6 col-md-offset-3">
          <form class="form" role="form" action="?p=auth&amp;a=forgotpw" method="post">
            <input type="hidden" name="p" value="auth">
            <div class="row">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-envelope fa-fw"></span></span>
                  <input type="email" name="uid" id="uid" class="form-control" placeholder="Your Login Email Address" value="'.$uid.'" required autofocus>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="text-right">
                  <a class="btn btn-md btn-default" href="?p=auth&amp;a=signin">&laquo; Back</a>
                  <button class="btn btn-md btn-primary" type="submit">Send</button>
                </div>
              </div>
            </div>
            <div class="row text-center">
              You will receive an email with further instructions and please
              note that this only resets the password for this website interface.
            </div>
          </form>
        </div>';
    }

    public function auth_newpw(int $id, string $uid) : string
    {
error_log(__METHOD__." id=".$id);

        return '
        <h2><i class="fa fa-key fa-fw"></i> Reset Password</h2>
        <div class="col-md-6 col-md-offset-3">
          <form class="form" role="form" action="?p=auth&amp;a=resetpw" method="post">
            <input type="hidden" name="i" value="'.$id.'">
            <div class="row">
              <p class="text-center"><b>'.$uid.'</b></p>
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                  <input type="password" name="passwd1" id="passwd1" class="form-control" placeholder="New Password" value="" required autofocus>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                  <input type="password" name="passwd2" id="passwd2" class="form-control" placeholder="Confirm Password" required value="">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="form-group">
                <div class="text-right">
                  <button class="btn btn-md btn-primary" type="submit">Reset my password</button>
                </div>
              </div>
            </div>
          </form>
        </div>';
    }
}
