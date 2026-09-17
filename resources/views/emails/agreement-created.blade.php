@extends('emails.layouts.master')

@section('title', 'New Legal Agreement')

@section('icon', '📝')

@section('header', 'New Legal Agreement Created')

@section('subtitle')
A new legal agreement has been submitted and requires your attention.
@endsection

@section('content')

@include('emails.partials.agreement-detail')

@endsection
