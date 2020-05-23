@if(!empty($error))
    <h1>ИСХОДНЫЙ КОД</h1>
    <pre>
    {{$FileCode}}
</pre>
<pre>
<h1 style="color: RED">ERROR</h1>
    {{print_r($error)}}
    </pre>
@else
<h1 style="color: GREEN;">ИСХОДНЫЙ КОД</h1>
<pre>
    {{$FileCode}}
</pre>

<h1>L-WIQA</h1>
<pre>
    {{print_r($WIQA)}}
</pre>
@endif