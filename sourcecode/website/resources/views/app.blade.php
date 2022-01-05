@extends('common::framework')

@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.41f9cd8f18e85618bcff.css" media="print" onload="this.media=&apos;all&apos;">
		<link rel="stylesheet" href="client/styles.41f9cd8f18e85618bcff.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script>setTimeout(function() {
        var spinner = document.querySelector('.global-spinner');
        if (spinner) spinner.style.display = 'flex';
    }, 100);</script>
		<script src="client/runtime-es2015.4ec88a97768e8a190cb3.js" type="module"></script>
		<script src="client/runtime-es5.4ec88a97768e8a190cb3.js" nomodule defer></script>
		<script src="client/polyfills-es5.7dec1fefa52cfcc5108b.js" nomodule defer></script>
		<script src="client/polyfills-es2015.f93fa6be99734e20273f.js" type="module"></script>
		<script src="client/main-es2015.d89b74fa071930a4d73f.js" type="module"></script>
		<script src="client/main-es5.d89b74fa071930a4d73f.js" nomodule defer></script>
	{{--angular scripts end--}}
@endsection
