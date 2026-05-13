@extends('pdf.layout')

@section('styles')
    @include('sales.partials.invoice-style')
@endsection

@section('content')
    @include('sales.partials.invoice-document', ['isPdf' => true])
@endsection
