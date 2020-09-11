<div class="form-group ">
    <span>{{$filepath}}</span>
</div>
<div class="form-group">
    <textarea id="logs" class="form-control" rows="15">{{$filedata}}</textarea>
</div>

<div class="form-group pull-right log_links">
    {!! $paginator->links() !!}
</div>
