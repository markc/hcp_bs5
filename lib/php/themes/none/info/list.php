<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// info/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
error_log(var_export($infos,true));
foreach ($infos as $info) $buf .= info_list_row($info);
//return "we'll be back";
return '
      <table>
        <tr><th>Domain</th><th>Accounts</th><th>Size</th><th>Num</th><th>Forwards</th><th>Rcpt</th><th></th></tr>' . $buf . '
      </table>';

    function info_list_row(array $ary) : string
    {
        extract($ary);
        return '
        <tr>
          <td><a href="?o=info&m=read&i=' . $id . '">' . $domain . '</a></td>
          <td>' . $maxaccounts . '</td>
          <td>' . $maxaccountsize . '</td>
          <td>' . $maxaccountcount . '</td>
          <td>' . $maxforwards . '</td>
          <td>' . $maxforwardsrcpt . '</td>
          <td style="text-align:right">
            <small>
              <a href="?o=info&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=info&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>
            </small>
          </td>
        </tr>';
    }

