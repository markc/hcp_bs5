<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= s_users_row($d);
return '
      <table>
        <tr><th>Username</th><th>Gecos</th><th>Homedir</th><th></th></tr>' . $buf . '
      </table>';

    function s_users_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=s_users&m=read&i=' . $id . '">' . $username . '</a></td>
          <td>' . $gecos . '</td>
          <td>' . $homedir . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=s_users&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=s_users&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

