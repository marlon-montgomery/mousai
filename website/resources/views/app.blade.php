@extends('common::framework')

@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.3805884f36e7b2989495.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script>setTimeout(function() {
        var spinner = document.querySelector('.global-spinner');
        if (spinner) spinner.style.display = 'flex';
    }, 100);</script>
		<script src="client/runtime-es2015.41187377ae76dd365a00.js" type="module"></script>
		<script src="client/runtime-es5.41187377ae76dd365a00.js" nomodule defer></script>
		<script src="client/polyfills-es5.bf031884b5c82939466d.js" nomodule defer></script>
		<script src="client/polyfills-es2015.028f897ea75b3f8e938b.js" type="module"></script>
		<script src="client/main-es2015.60980e7015f4161fca0d.js" type="module"></script>
		<script src="client/main-es5.60980e7015f4161fca0d.js" nomodule defer></script>
	{{--angular scripts end--}}
@endsection
