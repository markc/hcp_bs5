<?php
// lib/php/themes/bootstrap/auth.php 20150101
// Copyright (C) 2015-2017 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Auth extends Themes_Bootstrap_Theme
{
    // forgotpw (create new pw)
    public function create(array $in) : string
    {
error_log(__METHOD__);

        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-key"></i> Forgot password</h2>
          <form action="' . $this->g->self . '" method="post">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="email" name="login" id="login" class="form-control" placeholder="Your Login Email Address" value="' . $login . '" autofocus required>
              </div>
            </div>
            <div class="form-group">
              <a tabindex="0" role="button" data-toggle="popover" data-trigger="hover" title="Please Note" data-content="You will receive an email with further instructions and please note that this only resets the password for this website interface."> <i class="fa fa-question-circle fa-fw"></i></a>
              <div class="btn-group pull-right">
                <a class="btn btn-outline-primary" href="?o=auth">&laquo; Back</a>
                <button class="btn btn-primary" type="submit" name="m" value="create">Send</button>
              </div>
            </div>
          </form>
        </div>
        <script>$(function() { $("[data-toggle=popover]").popover(); });</script>';
    }

    // signin (read current pw)
    public function list(array $in) : string
    {
error_log(__METHOD__);
error_log(var_export($in,true));

        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-sign-in"></i> Sign in</h2>
          <form action="' . $this->g->self . '" method="post">
            <input type="hidden" name="o" value="auth">
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-user fa-fw"></i></span>
                <input type="email" name="login" id="login" class="form-control" placeholder="Your Email Address" value="' . $login . '" required autofocus>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-key fa-fw"></i></span>
                <input type="password" name="webpw" id="webpw" class="form-control" placeholder="Your Password" required>
              </div>
            </div>
            <div class="form-group">
              <label class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="remember" id="remember" value="yes">
                <span class="custom-control-indicator"></span>
                <span class="custom-control-description">Remember me on this computer</span>
              </label>
            </div>
            <div class="btn-group pull-right">
              <a class="btn btn-outline-primary" href="?o=auth&m=create">Forgot password</a>
              <button class="btn btn-primary" type="submit" name="m" value="list">Sign in</button>
            </div>
          </form>
        </div>';
    }

    // resetpw (update pw)
    public function update(array $in) : string
    {
error_log(__METHOD__);
error_log(var_export($in,true));

        extract($in);

        return '
        <div class="col-md-4 offset-md-4">
          <h2><i class="fa fa-key"></i> Update Password</h2>
          <form action="' . $this->g->self . '" method="post">
            <input type="hidden" name="o" value="auth">
            <input type="hidden" name="id" value="' . $id . '">
            <input type="hidden" name="login" value="' . $login . '">
            <p class="text-center"><b>For ' . $login . '</b></p>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                <input type="password" name="passwd1" id="passwd1" class="form-control" placeholder="New Password" value="" required autofocus>
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <span class="input-group-addon"><span class="fa fa-key fa-fw"></span></span>
                <input type="password" name="passwd2" id="passwd2" class="form-control" placeholder="Confirm Password" value="" required>
              </div>
            </div>
            <div class="btn-group pull-right">
              <button class="btn btn-primary" type="submit" name="m" value="update">Update my password</button>
            </div>
          </form>
        </div>';
    }
}

?>
