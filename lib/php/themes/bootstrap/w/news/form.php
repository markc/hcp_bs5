<?php declare(strict_types = 1);?>
<?php error_log(__FILE__);?>
<?php
// bootstrap/w/news/form.php 20151030 (C) 2015-2016 Mark Constable <markc@renta.net> (AGPL-3.0)

util::acl(1);
return '
      <h2><i class="fa fa-newspaper-o fa-fw"></i> News</h2>
      <form class="form-horizontal" role="form" method="post">
        <div class="form-group">
          <label for="title" class="col-md-2 control-label">Title</label>
          <div class="col-md-8">
            <input type="text" class="form-control" id="title" name="title" placeholder="Your Title" value="' . $title . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="author" class="col-md-2 control-label">Author</label>
          <div class="col-md-8">
            <input type="text" class="form-control" id="author" name="author" placeholder="Authors name" value="' . $author . '" required>
          </div>
        </div>
        <div class="form-group">
          <label for="content" class="col-md-2 control-label">Content</label>
          <div class="col-md-8">
            <textarea class="form-control" id="content" name="content" rows="12" placeholder="Your Message" required>' . $content . '</textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="col-md-10 text-right">'
          . $this->a('?o=w_news&m=read&i=' . $id, '&laquo; Back', 'default')
          . $this->button('Save', 'submit', 'success')
          . $this->a('?o=w_news&m=delete&i=' . $id, 'Delete', 'danger'). '
          </div>
        </div>
        <input type="hidden" name="o" value="' . $this->g->in['o'] . '">
        <input type="hidden" name="m" value="' . $this->g->in['m'] . '">
        <input type="hidden" name="i" value="' . $this->g->in['i'] . '">
      </form>';
