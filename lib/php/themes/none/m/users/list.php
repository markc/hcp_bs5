<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// users/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
foreach ($data as $d) $buf .= m_users_row($d);
return '
      <table>
        <tr><th>UID</th><th>Mail Path</th><th></th></tr>' . $buf . '
      </table>';

    function m_users_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=m_users&m=read&i=' . $id . '">' . $uid . '</a></td>
          <td>' . $mpath . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=m_users&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=m_users&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

