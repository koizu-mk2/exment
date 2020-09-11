<div class="box box-info">
    <!-- box-header -->
    <div class="box-header with-border">
        <h3 class="box-title">{{ exmtrans('plugincode.upload_header') }}</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <!-- /.box-header -->
    <!-- box-body -->
    <div class="box-body">
        <div class="fields-group">
            <div class="form-group">
                <span class="help-block">
                    <i class="fa fa-info-circle"></i>&nbsp;{{ exmtrans('plugincode.message.upload_file', $filepath) }}
                </span>
                <span class="help-block">
                    <i class="fa fa-info-circle"></i>&nbsp;{{ exmtrans('plugincode.message.file_edit') }}
                </span>
                <span class="help-block">
                    <i class="fa fa-warning"></i>&nbsp;{{ exmtrans('plugincode.message.force_updated') }}
                </span>
            </div>
        </div>
        @if(session()->has('errorMess'))
            <span class="font-weight-bold" style="color: red"><i class="fa fa-times-circle-o"></i> {!! session('errorMess') !!}</span>
        @endif
            
    </div>
    <!-- /.box-body -->
    <input type="hidden" id="plugin_file_path" name="plugin_file_path" value="{{ $filepath }}">
</div>
