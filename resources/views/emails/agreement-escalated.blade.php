@extends('emails.layouts.master')

@section('title', 'Legal Agreement Escalated')

@section('icon', '🚨')

@section('header', 'Legal Agreement Escalated')

@section('subtitle')
This legal agreement has been escalated and requires attention.
@endsection

@section('content')

@include('emails.partials.agreement-detail')

@endsection
