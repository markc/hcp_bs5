<?php

declare(strict_types=1);
// lib/php/themes/bootstrap/auth.php 20150101 - 20230625
// Copyright (C) 2015-2023 Mark Constable <markc@renta.net> (AGPL-3.0)

class Themes_Bootstrap_Auth extends Themes_Bootstrap_Theme
{
    // forgotpw (create new pw)
    public function create(array $in): string
    {
        return '
        <div class="row">
          <h3><i class="bi bi-key"></i> Forgot password</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
            <div class="input-group mb-2 mr-sm-2">
            <div class="input-group-prepend">
              <div class="input-group-text"><i class="bi bi-envelope"></i></div>
            </div>
              <input type="email" name="login" id="login" class="form-control" placeholder="Your Login Email Address" value="' . $login . '" autofocus required>
            </div>
            <small class="form-text text-muted text-center">
              You will receive an email with further instructions and please note that this only resets the password for this website interface.
            </small>
            <div class="form-group text-right">
              <div class="btn-group">
                <a class="btn btn-outline-primary" href="?o=auth">&laquo; Back</a>
                <button class="btn btn-primary" type="submit" name="m" value="create">Send</button>
              </div>
            </div>
          </form>
        </div>';
    }

    // signin (read current pw)
    public function list(array $in): string
    {
        elog(__METHOD__);
        elog(var_export($in, true));

        return '
        <div class="col-md-4 mx-auto">
          <h3><i class="bi bi-key"></i> Sign in</h3>

          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="auth">

            <div class="mb-3">
                <label for="login" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" name="login" id="login" placeholder="Your Email Address" value="' . $in['login'] . '" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="sr-only" for="webpw">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" name="webpw" id="webpw" class="form-control" placeholder="Your Password" required>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Remember me on this computer
                </label>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
              <div class="btn-group">
                <a class="btn btn-outline-primary" href="?o=auth&m=create">Forgot password</a>
                <button class="btn btn-primary" type="submit" id="m" name="m" value="list">Sign in</button>
              </div>
            </div>

          </form>
        </div>';
    }

    // resetpw (update pw)
    public function update(array $in): string
    {
        elog(__METHOD__);
        elog(var_export($in, true));

        extract($in);

        return '
        <div class="col-10 col-sm-8 col-md-6 col-lg-5 col-xl-4 mr-auto ml-auto">
          <h3><i class="fas fa-key fa-fw"></i> Update Password</h3>
          <form action="' . $this->g->cfg['self'] . '" method="post">
            <input type="hidden" name="c" value="' . $_SESSION['c'] . '">
            <input type="hidden" name="o" value="auth">
            <input type="hidden" name="id" value="' . $id . '">
            <input type="hidden" name="login" value="' . $login . '">
            <p class="text-center"><b>For ' . $login . '</b></p>
            <label class="sr-only" for="passwd1">New Password</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key fa-fw"></i></div>
              </div>
                <input class="form-control" type="password" name="passwd1" id="passwd1" placeholder="New Password" value="" required>
            </div>
            <label class="sr-only" for="passwd2">Confirm Password</label>
            <div class="input-group mb-2 mr-sm-2">
              <div class="input-group-prepend">
                <div class="input-group-text"><i class="fas fa-key fa-fw"></i></div>
              </div>
                <input class="form-control" type="password" name="passwd2" id="passwd2" placeholder="Confirm Password" value="" required>
            </div>
            <div class="form-group text-right">
              <div class="btn-group">
                <button class="btn btn-primary" type="submit" name="m" value="update">Update my password</button>
              </div>
            </div>
          </form>
        </div>';
    }
}
