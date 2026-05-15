@extends('emails.layouts.master')

@section('title', 'Ticket Completed')

@section('icon', '✅')

@section('header', 'Ticket Completed')

@section('subtitle')
Your ticket has been completed successfully.
@endsection

@section('content')

@include('emails.partials.ticket-detail')

@endsection
