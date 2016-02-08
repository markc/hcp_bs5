<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// w/news/list.php 20151030 (C) 2015 Mark Constable <markc@renta.net> (AGPL-3.0)

$buf = '';
$hdr = util::is_adm() ? '
    <h2>News</h2>
    <p>
This is a simple news system, you can
<a href="?o=w_news&m=create" title="Create">create</a> a new item or
<a href="?o=w_news&m=read" title="Read">read</a> them at your leisure.
    </p>': '';
foreach ($data as $d) $buf .= w_news_row($d);
return $hdr . '
      <table>' . $buf . '
      </table>';

    function w_news_row($ary) : string
    {
        extract($ary);

        $ext = util::is_adm() ? ' -
              <a href="?o=w_news&m=update&i=' . $id . '" title="Update">E</a>
              <a href="?o=w_news&m=delete&i=' . $id . '" title="Delete" onClick="javascript: return confirm(\'Are you sure you want to remove '.$id.'?\')">X</a>' : '';

        return '
        <tr><td colspan="2"><hr></td></tr>
        <tr>
          <td><a href="?o=w_news&m=read&i=' . $id . '">' . $title . '</a></td>
          <td style="text-align:right">
            <small>
              by <b>' . $author . '</b> - <i>' . util::now($updated) . '</i>' . $ext . '
            </small>
          </td>
        </tr>
        <tr>
          <td colspan="2"><p>' . nl2br($content) . '</p></td>
        </tr>';
    }

