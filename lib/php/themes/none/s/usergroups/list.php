<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= s_usergroups_row($d);
return '
      <table>
        <tr><th>Name</th><th>Password</th><th>GID</th><th></th></tr>' . $buf . '
      </table>';

    function s_usergroups_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=s_usergroups&m=read&i=' . $id . '">' . $name . '</a></td>
          <td>' . $password . '</td>
          <td>' . $gid . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=s_usergroups&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=s_usergroups&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

