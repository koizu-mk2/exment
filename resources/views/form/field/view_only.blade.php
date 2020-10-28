<div class="{{$viewClass['form-group']}}">
    <label class="{{$viewClass['label']}} control-label" style="padding-top:10px;">{{$label}}</label>
    <div class="{{$viewClass['field']}}">
        <div class="no-margin">
            <!-- /.box-header -->
            <div class="box-body {{$displayClass ?? null}}" style="padding-left:0; padding-bottom:0;">
                <span class="{{$class}}" {!! $attributes  !!}>
                @if(isset($displayText))
                    @if(!$escape)
                    {!! $displayText !!}
                    @else
                    {{ $displayText }}
                    @endif
                @else
                    @if(!$escape)
                    {!! $value !!}
                    @else
                    {{ $value }}
                    @endif
                @endif
                </span>&nbsp;
            </div><!-- /.box-body -->
        </div>

        @if($prepareDefault)
        <input type="hidden" name="{{$name}}" />
        @endif

        @include('admin::form.help-block')

    </div>
</div>