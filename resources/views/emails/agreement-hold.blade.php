@extends('emails.layouts.master')

@section('title', 'Legal Agreement On Hold')

@section('icon', '⏸️')

@section('header', 'Legal Agreement On Hold')

@section('subtitle')
This legal agreement has been put on hold.
@endsection

@section('content')

@include('emails.partials.agreement-detail')

@endsection
