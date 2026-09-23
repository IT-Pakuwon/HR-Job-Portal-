@extends('emails.layouts.master')

@section('title', 'Legal Agreement Active')

@section('icon', '✅')

@section('header', 'Legal Agreement Active')

@section('subtitle')
This legal agreement is now active.
@endsection

@section('content')

@include('emails.partials.agreement-detail')

@endsection
