<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// m/limits/item.php 20160206 (C) 2016 Mark Constable <markc@renta.net> (AGPL-3.0)

return '
      <hr>
      <table style="table-layout: fixed">
        <tr><td></td><td>ID</td><td>' . $id . '</td><td></td></tr>
        <tr><td></td><td>Domain</td><td>' . $domain . '</td><td></td></tr>
        <tr><td></td><td>MaxAccounts</td><td>' . $maxaccounts . '</td><td></td></tr>
        <tr><td></td><td>MaxAccountSize</td><td>' . $maxaccountsize . '</td><td></td></tr>
        <tr><td></td><td>MaxAccountCount</td><td>' . $maxaccountcount . '</td><td></td></tr>
        <tr><td></td><td>MaxForwards</td><td>' . $maxforwards . '</td><td></td></tr>
        <tr><td></td><td>MaxForwardsRcpt</td><td>' . $maxforwardsrcpt . '</td><td></td></tr>
        <tr><td></td><td>Updated</td><td>' . $updated . '</td><td></td></tr>
        <tr><td></td><td>Created</td><td>' . $created . '</td><td></td></tr>
        <tr><td></td>
          <td colspan="2" style="text-align:right">
            <p>'
            .$this->a('?o=m_limits&m=delete&i=' . $id, 'Delete', 'danger', ' onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')"')
            .$this->a('?o=m_limits&m=update&i=' . $id, 'Edit', 'primary').'
            </p>
          </td>
        </tr>
      </table>';
