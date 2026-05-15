@extends('emails.layouts.master')

@section('title', 'Ticket Cancelled')

@section('icon', '❌')

@section('header', 'Ticket Cancelled')

@section('subtitle')
This ticket has been cancelled.
@endsection

@section('content')

@include('emails.partials.ticket-detail')

@endsection
