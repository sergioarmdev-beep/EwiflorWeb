@extends('publico::layouts.public')

@section('content')

@include('publico::partials.hero')

@include('publico::partials.categories')

@include('publico::partials.products-grid', ['modo' => 'inicio'])

@include('publico::partials.cta')

@endsection
