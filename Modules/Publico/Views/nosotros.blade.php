@extends('publico::layouts.public')

@section('title', 'Nosotros | Ewiflor Importaciones')

@section('content')

@include('publico::partials.about', ['pagina' => true])

@include('publico::partials.advantages')

@include('publico::partials.cta')

@endsection
