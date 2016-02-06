<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// s/usergrouplist/list.php 20160206 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= s_usergrouplist_row($d);
return '
      <table>
        <tr><th>GID</th><th>Username</th><th></th></tr>' . $buf . '
      </table>';

    function s_usergrouplist_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=s_usergrouplist&m=read&i=' . $id . '">' . $gid . '</a></td>
          <td>' . $username . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=s_usergrouplist&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=s_usergrouplist&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

