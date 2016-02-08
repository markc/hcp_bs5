<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// auth/signin.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

        return '
        <h2><i class="fa fa-sign-in fa-fw"></i> Sign in</h2>
        <div class="col-md-6 col-md-offset-3">
          <form class="form" role="form" action="" method="post">
            <input type="hidden" name="o" value="auth">
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
                  <a class="btn btn-md btn-default" href="?o=auth&amp;m=forgotpw">Forgot password</a>
                  <button class="btn btn-md btn-primary" type="submit" name="m" value="signin">Sign in</button>
                </div>
              </div>
            </div>
          </form>
        </div>';
