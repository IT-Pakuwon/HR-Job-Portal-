@extends('emails.layouts.master')

@section('title', 'Legal Agreement Completed')

@section('icon', '✅')

@section('header', 'Legal Agreement Completed')

@section('subtitle')
This legal agreement has been completed successfully.
@endsection

@section('content')

@include('emails.partials.agreement-detail')

@endsection
