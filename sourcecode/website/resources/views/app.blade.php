@extends('common::framework')

@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.b29cfa4658be177d6ca0.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script>setTimeout(function() {
        var spinner = document.querySelector('.global-spinner');
        if (spinner) spinner.style.display = 'flex';
    }, 100);</script>
		<script src="client/runtime-es2015.5b4eff44a04776dfe432.js" type="module"></script>
		<script src="client/runtime-es5.5b4eff44a04776dfe432.js" nomodule defer></script>
		<script src="client/polyfills-es5.85a146d3b567a2ddbb57.js" nomodule defer></script>
		<script src="client/polyfills-es2015.394385f3043280af1d7f.js" type="module"></script>
		<script src="client/main-es2015.b61305a94665a17bbe0a.js" type="module"></script>
		<script src="client/main-es5.b61305a94665a17bbe0a.js" nomodule defer></script>
	{{--angular scripts end--}}
@endsection
