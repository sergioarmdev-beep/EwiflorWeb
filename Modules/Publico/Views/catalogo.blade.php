@extends('publico::layouts.public')

@section('title', 'Catálogo | Ewiflor Importaciones')

@section('content')

@include('publico::partials.products-grid', ['modo' => 'completo'])

@endsection
