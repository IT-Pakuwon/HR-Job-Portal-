@extends('emails.layouts.master')

@section('title', 'Ticket Created')

@section('icon', '🎫')

@section('header', 'New Ticket Created')

@section('subtitle')
A new support ticket has been submitted into the {{ $systemLabel }}.
@endsection

@section('content')
@include('emails.partials.ticket-detail')
@endsection
