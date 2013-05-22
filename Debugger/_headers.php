<style type="text/css" media="screen">
.LemmonDebuggerDump {
    font-family:'Consolas',monospace;
    font-size:12px;
    line-height:17px;
    color:#002b36;
    background:#fdf6e3 !important;
    color:#002b36;
    overflow-x:auto;
    margin-bottom:10px;
}
pre.LemmonDebuggerDump {
    border:1px solid #eee8d5;
    padding:9px 10px 8px;
    white-space:pre-wrap;
}
table.LemmonDebuggerDump {
    width:100%;
    border-spacing:0;
    border-collapse:collapse;
    border-top:1px solid #eee8d5;
    border-left:1px solid #eee8d5;
}
table.LemmonDebuggerDump th,
table.LemmonDebuggerDump td {
    padding:6px 8px 2px;
    border-right:1px solid #eee8d5;
    border-bottom:1px solid #eee8d5;
    background:#fdf6e3 !important;
    white-space:pre-wrap;
    vertical-align:top;
}
table.LemmonDebuggerDump th {
    font-weight:normal;
    text-align:left;
}
table.LemmonDebuggerDump tbody th {
    font-weight:bold;
}
table.LemmonDebuggerDump tr:nth-child(even) th,
table.LemmonDebuggerDump tr:nth-child(even) td {
    -background:#eee8d5 !important;
}
a.LemmonDebugerExpander {
    color:inherit;
    text-decoration:none;
}
.LemmonDebugger .collapse,
.LemmonDebuggerDump .collapse {
    display:none;
}
.LemmonDebugger .collapse.expand,
.LemmonDebuggerDump .collapse.expand {
    display:inline;
}
/*
.LemmonDebuggerDump abbr {
    color:#93a1a1;
}
*/
.LemmonDebuggerDump span.mark {
    color:#dc322f;
    font-weight:bold;
}
.LemmonDebuggerDump span.note {
    color:#839496;
}
.LemmonDebuggerDump span.string {
    color:#268bd2; /* blue */
}
a.LemmonDebugerExpander span.more {
    display:inline-block;
    background:#eee8d5;
    color:#93a1a1;
    font-family:sans-serif;
    line-height:1em;
    padding:0 2px;
    -webkit-border-radius:3px;
            border-radius:3px;
}
a.LemmonDebugerExpander .more.hide {
    display:none;
}
</style>
<script src="<?php echo self::$jQuery ?>"></script>
<script>
$(function(){
    $('a.LemmonDebugerExpander').click(function(){
        $(this).find('.more').toggleClass('hide');
        $(this).next().toggleClass('expand');
        return false;
    });
});
</script>