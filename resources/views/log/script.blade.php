

<script type="text/javascript">
    function selected_jstree_node() {
        setCodeMirror();
    }

    function setCodeMirror(){
        $('#logs').each(function(index, elem){
            CodeMirror.fromTextArea(elem, {
                mode: 'log',
                lineNumbers: true,
                indentUnit: 4,
                readOnly: "nocursor",
            });
        });
    }

    $(document).on('click.exment_server_log', '.log_links a', {}, function(ev){
        ev.preventDefault();

        $.ajax({
            type: "GET",
            url: $(ev.target).closest('a').attr('href'),
            cache: false,
            success: function(data){
                if(data.editor){
                    $('section.content > div > div.col-sm-9').html(data.editor);
                }
                if ('function' == typeof selected_jstree_node) {
                    selected_jstree_node();
                }
            },
            error: function(msg){
                alert(msg);
            }
        });
    });

    $(function(){
        setCodeMirror();
    });
</script>
