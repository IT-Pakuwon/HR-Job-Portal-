@extends('emails.layouts.master')

@section('title', 'Ticket Assigned')

@section('icon', '👨‍💻')

@section('header', 'Ticket Assigned')

@section('subtitle')
A ticket has been assigned to you for handling.
@endsection

@section('content')

@include('emails.partials.ticket-detail')

@endsection
